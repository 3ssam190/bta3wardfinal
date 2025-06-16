<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

// Make sure this is the VERY FIRST LINE with no whitespace before it
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Check if user is logged in - BEFORE ANY OUTPUT
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit;
// }

// // Check if role is set in session, if not set a default or redirect
// if (!isset($_SESSION['role'])) {
//     // Either set a default role (if appropriate for your application)
//     $_SESSION['role'] = 'Super Admin';
    
//     // Or redirect to login if role is required
//     header('Location: login.php');
//     exit;
// }

// Define allowed roles (fixed variable name)
$allowedRoles = ['Super Admin', 'Product Manager', 'Order Manager']; // Added all possible roles

// Check if user has allowed role (using correct variable name)
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: unauthorized.php');
    exit;
}

// Initialize database connection
try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Validate status
        $allowed_statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        if (!in_array($new_status, $allowed_statuses)) {
            throw new Exception('Invalid order status');
        }
        
        // Get current status
        $stmt = $pdo->prepare("SELECT status FROM Orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $current_status = $stmt->fetchColumn();
        
        if (!$current_status) {
            throw new Exception('Order not found');
        }
        
        // Prevent invalid status transitions
        $valid_transitions = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['Processing', 'Cancelled'],
            'Processing' => ['Shipped', 'Cancelled'],
            'Shipped' => ['Delivered'],
            'Delivered' => [],
            'Cancelled' => []
        ];
        
        // Add validation for status transitions
        if (!array_key_exists($current_status, $valid_transitions) || 
            !in_array($new_status, $valid_transitions[$current_status])) {
            throw new Exception("Invalid status transition from $current_status to $new_status");
        }
        
        // Additional restrictions for order managers
        if ($_SESSION['role'] === 'order_manager' && in_array($new_status, ['Cancelled'])) {
            throw new Exception("Order managers cannot cancel orders");
        }
        
        $verified_by = $_SESSION['admin_id'];
        $stmt = $pdo->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$new_status, $order_id]);
        $stmt = $pdo->prepare("UPDATE Payments SET payment_status = 'Completed', verified_at = NOW(), verified_by = ? WHERE payment_id = ?");
        $stmt->execute([$verified_by, $payment_id]);
        
        // Add to order history
        // $stmt = $pdo->prepare("INSERT INTO OrderHistory (order_id, status, changed_by) VALUES (?, ?, ?)");
        // $stmt->execute([$order_id, $new_status, $_SESSION['admin_id']]);
        
        // If changing to Delivered, update payment status if not already completed
        if ($new_status === 'Delivered') {
            $stmt = $pdo->prepare("
                UPDATE Payments 
                SET payment_status = 'Completed'
                WHERE order_id = ? 
                AND payment_status = 'Pending'
            ");
            $stmt->execute([$order_id]);
        }
        
        $_SESSION['message'] = "Order status updated successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: orders.php');
    exit;
}

// Handle Vodafone Cash verification (for admins only)
if (isset($_POST['verify_vodafone']) && in_array($_SESSION['role'], ['super_admin', 'admin'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Get payment details
        $stmt = $pdo->prepare("SELECT * FROM Payments WHERE order_id = ? AND payment_method = 'Vodafone Cash'");
        $stmt->execute([$order_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new Exception('Vodafone Cash payment not found');
        }
        
        if ($action === 'approve') {
            $stmt = $pdo->prepare("
                UPDATE Payments 
                SET payment_status = 'Completed',
                    verified_at = NOW(),
                    verified_by = ?
                WHERE order_id = ?
                AND payment_method = 'Vodafone Cash'
            ");
            $stmt->execute([$_SESSION['admin_id'], $order_id]);
            
            // Only update order status if not already processing
            $stmt = $pdo->prepare("
                UPDATE Orders 
                SET status = 'Processing'
                WHERE order_id = ?
                AND status = 'Pending'
            ");
            $stmt->execute([$order_id]);
            
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("
                UPDATE Payments 
                SET payment_status = 'Failed',
                    rejection_reason = ?,
                    verified_at = NOW(),
                    verified_by = ?
                WHERE order_id = ?
                AND payment_method = 'Vodafone Cash'
            ");
            $stmt->execute([$reason, $_SESSION['admin_id'], $order_id]);
            
            $stmt = $pdo->prepare("
                UPDATE Orders 
                SET status = 'Cancelled'
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);
                    
            $_SESSION['message'] = "Payment rejected and order cancelled";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: orders.php');
    exit;
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';

$query = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.delivery_fee,
        o.status,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
        u.email AS customer_email,
        p.payment_method,
        p.payment_status,
        p.transaction_id,
        (SELECT COUNT(*) FROM OrderItems oi WHERE oi.order_id = o.order_id) AS item_count
    FROM Orders o
    LEFT JOIN Payments p ON o.order_id = p.order_id
    LEFT JOIN Users u ON o.user_id = u.user_id
";

$count_query = "
    SELECT COUNT(DISTINCT o.order_id) 
    FROM Orders o
    LEFT JOIN Payments p ON o.order_id = p.order_id
    LEFT JOIN Users u ON o.user_id = u.user_id
";

// Add filters if specified
$where_clauses = [];
$params = [];
$count_params = [];

if ($status_filter) {
    $where_clauses[] = "o.status = ?";
    $params[] = $status_filter;
    $count_params[] = $status_filter;
}

if ($search_query) {
    $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.order_id = ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_query;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_query;
}

if ($payment_method) {
    $where_clauses[] = "p.payment_method = ?";
    $params[] = $payment_method;
    $count_params[] = $payment_method;
}

// Complete the queries
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
    $count_query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Append LIMIT and OFFSET as positional placeholders
$query .= " GROUP BY o.order_id ORDER BY o.order_date DESC LIMIT ? OFFSET ?";

// Append limit and offset to params
$params[] = $perPage;
$params[] = $offset;

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status options for filter and update
$status_options = [
    'Pending' => 'Pending',
    'Confirmed' => 'Confirmed',
    'Processing' => 'Processing',
    'Shipped' => 'Shipped',
    'Delivered' => 'Delivered',
    'Cancelled' => 'Cancelled'
];

// Payment method options
$payment_methods = [
    'COD' => 'Cash on Delivery',
    'Credit Card' => 'Credit Card',
    'Vodafone Cash' => 'Vodafone Cash',
    'Fawry' => 'Fawry',
    'Apple Pay' => 'Apple Pay',
    'Samsung Pay' => 'Samsung Pay'
];
?>

<!-- Main Content -->
<main class="container-fluid py-4" style="margin-top: 70px;">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-3 mb-md-0 header-text">Orders Management</h2>
        
        <!-- Role Indicator -->
        <div class="badge bg-dark mb-2 mb-md-0">
            <?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($status_options as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $status_filter === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="">All Methods</option>
                            <?php foreach ($payment_methods as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $payment_method === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search orders..." 
                                   value="<?= htmlspecialchars($search_query) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </div>
                
                <?php if ($status_filter || $search_query || $payment_method): ?>
                <div class="mt-3">
                    <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold"><?= htmlspecialchars($order['customer_name']) ?></span>
                                    <span class="small text-muted"><?= htmlspecialchars($order['customer_email']) ?></span>
                                </div>
                            </td>
                            <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                            <td>
                                <?php if (!empty($order['payment_method'])): ?>
                                    <span class="badge bg-light text-dark">
                                        <?= $order['payment_method'] ?>
                                        <?php if ($order['payment_method'] === 'Vodafone Cash' && $order['payment_status'] === 'Pending Verification'): ?>
                                            <span class="badge bg-warning ms-1">Verify</span>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>EGP <?= number_format($order['total_amount'] + $order['delivery_fee'], 2) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $order['status'] === 'Pending' ? 'bg-warning text-dark' : 
                                    ($order['status'] === 'Confirmed' ? 'bg-info' : 
                                    ($order['status'] === 'Processing' ? 'bg-primary' : 
                                    ($order['status'] === 'Shipped' ? 'bg-secondary' : 
                                    ($order['status'] === 'Delivered' ? 'bg-success' : 'bg-danger')))) ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="viewOrderDetails(<?= $order['order_id'] ?>)" 
                                        class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                                
                                <?php if ($order['payment_method'] === 'Vodafone Cash' && 
                                        $order['payment_status'] === 'Pending Verification' && 
                                        in_array($_SESSION['role'], ['super_admin', 'admin'])): ?>
                                    <!-- Vodafone Cash verification button -->
                                    <button class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#verifyVodafoneModal"
                                            data-order-id="<?= $order['order_id'] ?>">
                                        <i class="fas fa-check-circle me-1"></i> Verify
                                    </button>
                                <?php else: ?>
                                    <!-- Regular status dropdown -->
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown"
                                                <?= ($order['status'] === 'Delivered' || $order['status'] === 'Cancelled') ? 'disabled' : '' ?>>
                                            <i class="fas fa-cog me-1"></i> Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($status_options as $value => $label): ?>
                                                <?php if ($value !== $order['status']): ?>
                                                    <li>
                                                        <form method="POST" class="dropdown-item p-0">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                            <input type="hidden" name="status" value="<?= $value ?>">
                                                            <button type="submit" name="update_status" 
                                                                    class="btn btn-link text-decoration-none w-100 text-start">
                                                                <?= $label ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php
            $count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($count_params);
$totalOrders = $count_stmt->fetchColumn(); // total row count

// Now calculate total pages
$totalPages = ceil($totalOrders / $perPage);
            if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="small text-muted mb-0">
                        Showing <span class="fw-bold"><?= $offset + 1 ?></span> to 
                        <span class="fw-bold"><?= min($offset + $perPage, $totalOrders) ?></span> of 
                        <span class="fw-bold"><?= $totalOrders ?></span> results
                    </p>
                </div>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="orders.php?page=<?= $page - 1 ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?><?= $payment_method ? '&payment_method=' . urlencode($payment_method) : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="orders.php?page=<?= $i ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?><?= $payment_method ? '&payment_method=' . urlencode($payment_method) : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="orders.php?page=<?= $page + 1 ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?><?= $search_query ? '&search=' . urlencode($search_query) : '' ?><?= $payment_method ? '&payment_method=' . urlencode($payment_method) : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-header text-success">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded dynamically via JavaScript -->
                    <div class="text-center py-4">
                        <div class="spinner-border header-text" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="printOrderDetails()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vodafone Cash Verification Modal -->
    <div class="modal fade" id="verifyVodafoneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Verify Vodafone Cash Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="verifyVodafoneForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="order_id" id="verifyOrderId">
                        
                        <div class="mb-3">
                            <p>Please verify that the payment was received in the store's Vodafone Cash wallet (01011960681).</p>
                            <p class="fw-bold">Amount: <span id="verifyAmount"></span></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="approvePayment" value="approve" checked>
                                <label class="form-check-label" for="approvePayment">
                                    Approve Payment
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="rejectPayment" value="reject">
                                <label class="form-check-label" for="rejectPayment">
                                    Reject Payment
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="rejectReasonContainer" style="display: none;">
                            <label for="rejectReason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="verify_vodafone" class="btn btn-warning">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Make order-related functions available globally
// Function to view order details
async function viewOrderDetails(orderId) {
    try {
        // Show loading spinner
        document.getElementById('orderDetailsContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Show modal immediately while loading
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
        
        const response = await fetch(`get_order_details.php?id=${orderId}`);
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load order details');
        
        populateOrderDetailsModal(data);
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('orderDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                Failed to load order details: ${error.message}
            </div>
        `;
    }
}

// Function to populate modal with order data
function populateOrderDetailsModal(data) {
    const order = data.order;
    const items = data.items;
    const payment = data.payment;
    
    // Format date
    const orderDate = new Date(order.order_date);
    const formattedDate = orderDate.toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
    
    // Create the order details content
    const content = `
        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Order Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Order Number</p>
                            <p class="fw-bold">#${order.order_id.toString().padStart(5, '0')}</p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Order Date</p>
                            <p class="fw-bold">${formattedDate}</p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Status</p>
                            <p>
                                <span class="badge ${getStatusBadgeClass(order.status)}">
                                    ${order.status}
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Customer</p>
                            <p class="fw-bold">${escapeHtml(order.customer_name)}</p>
                            <p class="small">${escapeHtml(order.customer_email)}</p>
                            <p class="small">${escapeHtml(order.customer_phone)}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Information -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Delivery Information</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-bold">${escapeHtml(order.delivery_address)}</p>
                        <p>
                            ${escapeHtml(order.delivery_city)}, 
                            ${escapeHtml(order.delivery_region_name)}
                        </p>
                        <p class="small text-muted">
                            Estimated Delivery: ${order.estimated_delivery_days}
                        </p>
                        ${order.notes ? `<div class="mt-3">
                            <p class="small text-muted mb-1">Customer Notes</p>
                            <p>${escapeHtml(order.notes)}</p>
                        </div>` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Order Items (${items.length})</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(item => `
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/products/${escapeHtml(item.image_url || 'default-product.jpg')}" 
                                                         class="rounded-circle me-2" width="40" height="40" 
                                                         onerror="this.src='assets/images/default-product.jpg'">
                                                    <div>
                                                        <div class="fw-bold">${escapeHtml(item.product_name)}</div>
                                                        ${item.original_product_name !== item.product_name ? 
                                                          `<div class="small text-muted">${escapeHtml(item.original_product_name)}</div>` : ''}
                                                        <div class="small text-muted">SKU: ${item.product_id}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>${item.quantity}</td>
                                            <td>EGP ${item.unit_price.toFixed(2)}</td>
                                            <td>EGP ${(item.quantity * item.unit_price).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Summary -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Payment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>EGP ${order.total_amount.toFixed(2)}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>EGP ${order.delivery_fee.toFixed(2)}</span>
                        </div>
                        
                        <hr class="my-2">
                        
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span>Total:</span>
                            <span>EGP ${(order.total_amount + order.delivery_fee).toFixed(2)}</span>
                        </div>
                        
                        ${payment ? `
                            <div class="mt-3">
                                <p class="small text-muted mb-1">Payment Method</p>
                                <p class="fw-bold">${payment.payment_method}</p>
                                
                                <p class="small text-muted mb-1">Payment Status</p>
                                <p>
                                    <span class="badge ${payment.payment_status === 'Completed' ? 'bg-success' : 
                                        (payment.payment_status === 'Pending Verification' ? 'bg-warning text-dark' : 
                                        (payment.payment_status === 'Pending' ? 'bg-warning text-dark' : 
                                        (payment.payment_status === 'Failed' ? 'bg-danger' : 'bg-secondary')))}">
                                        ${payment.payment_status}
                                    </span>
                                </p>
                                
                                ${payment.transaction_id ? `
                                    <p class="small text-muted mb-1">Transaction ID</p>
                                    <p class="fw-bold">${escapeHtml(payment.transaction_id)}</p>
                                ` : ''}
                                
                                ${payment.payment_method === 'Vodafone Cash' && payment.payment_status === 'Pending Verification' ? `
                                    <div class="alert alert-warning mt-3">
                                        <p class="mb-1"><strong>Vodafone Cash Payment Pending</strong></p>
                                        <p class="small mb-1">Customer should send payment to: <strong>01011960681</strong></p>
                                        <p class="small mb-0">Amount: <strong>EGP ${(order.total_amount + order.delivery_fee).toFixed(2)}</strong></p>
                                    </div>
                                ` : ''}
                            </div>
                        ` : '<p class="text-muted">No payment information available</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Update the modal body
    document.getElementById('orderDetailsContent').innerHTML = content;
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Helper function for status badge classes
function getStatusBadgeClass(status) {
    switch(status) {
        case 'Pending': return 'bg-secondary';
        case 'Confirmed': return 'bg-primary';
        case 'Processing': return 'bg-info';
        case 'Shipped': return 'bg-warning text-dark';
        case 'Delivered': return 'bg-success';
        case 'Cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Print order details
function printOrderDetails() {
    const printContent = document.getElementById('orderDetailsContent').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div class="container mt-4">
            <h2 class="mb-4">Order Details - Bta3Ward Store</h2>
            ${printContent}
            <div class="text-muted small mt-4">Printed on ${new Date().toLocaleString()}</div>
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
}

// Initialize modal close functionality
document.addEventListener('DOMContentLoaded', function() {
    const orderModal = document.getElementById('orderDetailsModal');
    
    // Close when clicking outside modal
    orderModal.addEventListener('click', function(e) {
        if (e.target === orderModal) {
            const modal = bootstrap.Modal.getInstance(orderModal);
            modal.hide();
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(orderModal);
            if (modal) modal.hide();
        }
    });
});

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Helper function for status badge classes
function getStatusBadgeClass(status) {
    switch(status) {
        case 'Pending': return 'bg-secondary';
        case 'Confirmed': return 'bg-primary';
        case 'Processing': return 'bg-info';
        case 'Shipped': return 'bg-warning text-dark';
        case 'Delivered': return 'bg-success';
        case 'Cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Initialize modal close functionality
document.addEventListener('DOMContentLoaded', function() {
    const orderModal = document.getElementById('orderDetailsModal');
    
    // Close when clicking outside modal
    orderModal.addEventListener('click', function(e) {
        if (e.target === orderModal) {
            const modal = bootstrap.Modal.getInstance(orderModal);
            modal.hide();
        }
    });
    
    // Close when clicking X button
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(orderModal);
            modal.hide();
        });
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(orderModal);
            if (modal) modal.hide();
        }
    });
});

// Helper function to get badge class based on status
function getStatusBadgeClass(status) {
    switch(status) {
        case 'Pending': return 'bg-warning text-dark';
        case 'Confirmed': return 'bg-info';
        case 'Processing': return 'bg-primary';
        case 'Shipped': return 'bg-secondary';
        case 'Delivered': return 'bg-success';
        case 'Cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Vodafone Cash verification modal setup
document.addEventListener('DOMContentLoaded', function() {
    const verifyModal = document.getElementById('verifyVodafoneModal');
    if (verifyModal) {
        verifyModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderRow = button.closest('tr');
            const amount = orderRow.querySelector('td:nth-child(5)').textContent;
            
            document.getElementById('verifyOrderId').value = orderId;
            document.getElementById('verifyAmount').textContent = amount;
        });
        
        // Show/hide reject reason based on selection
        document.querySelectorAll('input[name="action"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const rejectReasonContainer = document.getElementById('rejectReasonContainer');
                rejectReasonContainer.style.display = this.value === 'reject' ? 'block' : 'none';
            });
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>