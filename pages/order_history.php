<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$pageTitle = __('order_history');

// Pagination setup
$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

// Get total number of orders
$stmt = $conn->prepare("SELECT COUNT(*) FROM Orders WHERE user_id = ?");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetchColumn();
$totalPages = ceil($totalOrders / $ordersPerPage);

// Get orders with pagination
$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.delivery_fee,
        o.status,
        p.payment_method,
        p.payment_status
    FROM Orders o
    LEFT JOIN Payments p ON o.order_id = p.order_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$userId, $ordersPerPage, $offset]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Animated Background -->
<div class="history-background">
    <div class="history-leaf leaf-1"></div>
    <div class="history-leaf leaf-2"></div>
    <div class="history-particle particle-1"></div>
</div>

<section class="history-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?php echo __('order_history'); ?></h1>
                    <div class="orders-count">
                        <span class="badge bg-success"><?php echo $totalOrders; ?> <?php echo __('orders'); ?></span>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                <div class="empty-history text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="mb-3"><?php echo __('no_orders_found'); ?></h3>
                    <p class="text-muted mb-4"><?php echo __('no_orders_message'); ?></p>
                    <a href="shop.php" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i><?php echo __('start_shopping'); ?>
                    </a>
                </div>
                <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-meta">
                                <div class="order-number">
                                    <span><?php echo __('order'); ?> #<?php echo $order['order_id']; ?></span>
                                    <small class="text-muted"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></small>
                                </div>
                                <div class="order-status">
                                    <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="order-total">
                                <span><?php echo __('total'); ?></span>
                                <strong><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></strong>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="order-info">
                                        <h6><?php echo __('payment_info'); ?></h6>
                                        <p>
                                            <strong><?php echo __('method'); ?>:</strong> 
                                            <?php echo $order['payment_method'] ?? 'N/A'; ?><br>
                                            <strong><?php echo __('status'); ?>:</strong> 
                                            <span class="text-<?php echo ($order['payment_status'] === 'Completed') ? 'success' : 'warning'; ?>">
                                                <?php echo $order['payment_status'] ?? 'Pending'; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="order-actions">
                                        <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-eye me-2"></i><?php echo __('view_details'); ?>
                                        </a>
                                        <?php if ($order['status'] === 'Pending' || $order['status'] === 'Processing'): ?>
                                        <a href="#" class="btn btn-outline-danger cancel-order" data-order-id="<?php echo $order['order_id']; ?>">
                                            <i class="fas fa-times me-2"></i><?php echo __('cancel_order'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* History Background */
.history-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9f5e9 100%);
    overflow: hidden;
    z-index: -1;
}

.history-leaf {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.1;
    z-index: -1;
    animation: float 20s linear infinite;
}

.history-particle {
    position: absolute;
    background-color: rgba(46, 125, 50, 0.1);
    border-radius: 50%;
    z-index: -1;
    animation: float 25s linear infinite;
}

.history-leaf.leaf-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf1.png');
    width: 180px;
    height: 180px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.history-leaf.leaf-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf2.png');
    width: 120px;
    height: 120px;
    top: 70%;
    left: 80%;
    animation-delay: 5s;
    animation-direction: reverse;
}

.history-particle.particle-1 {
    width: 15px;
    height: 15px;
    top: 30%;
    left: 20%;
    animation-delay: 2s;
}

@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-50px) rotate(180deg); }
    100% { transform: translateY(0) rotate(360deg); }
}

/* Order History Styles */
.history-section {
    position: relative;
    z-index: 1;
}

.empty-history {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 4rem;
    color: #2e7d32;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.order-meta {
    display: flex;
    flex-direction: column;
}

.order-number span {
    font-weight: 600;
    font-size: 1.1rem;
}

.order-number small {
    font-size: 0.9rem;
}

.order-status .badge {
    font-size: 0.8rem;
    padding: 0.35rem 0.65rem;
}

.order-total {
    text-align: right;
}

.order-total span {
    display: block;
    font-size: 0.9rem;
    color: #6c757d;
}

.order-total strong {
    font-size: 1.2rem;
    color: #2e7d32;
}

.order-body {
    padding: 1.5rem;
}

.order-info h6 {
    color: #2e7d32;
    margin-bottom: 0.75rem;
}

.order-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

/* Status Colors */
.bg-Pending {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.bg-Processing {
    background-color: #0dcaf0 !important;
    color: #212529 !important;
}

.bg-Completed {
    background-color: #198754 !important;
}

.bg-Cancelled {
    background-color: #dc3545 !important;
}

.bg-Shipped {
    background-color: #fd7e14 !important;
    color: #212529 !important;
}

/* Responsive */
@media (max-width: 767.98px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-total {
        text-align: left;
        width: 100%;
    }
    
    .order-actions {
        justify-content: flex-start;
        margin-top: 1rem;
    }
    
    .order-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel order button handler
    document.querySelectorAll('.cancel-order').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.dataset.orderId;
            const orderCard = this.closest('.order-card');
            
            if (confirm('Are you sure you want to cancel this order?')) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cancelling...';
                this.disabled = true;
                
                fetch('cancel_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}`,
                    credentials: 'same-origin' // Important for session
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update UI without reload
                        const statusBadge = orderCard.querySelector('.order-status .badge');
                        statusBadge.textContent = 'Cancelled';
                        statusBadge.className = 'badge bg-Cancelled';
                        
                        // Remove cancel button
                        this.remove();
                        
                        // Show success message
                        showAlert('Order cancelled successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to cancel order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert(error.message, 'danger');
                    this.innerHTML = '<i class="fas fa-times me-2"></i>Cancel Order';
                    this.disabled = false;
                });
            }
        });
    });
    
    // Helper function to show alerts
    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alert, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    }
});
</script>

<?php 
// Helper function to get status color
function getStatusColor($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'pending': return 'Pending';
        case 'processing': return 'Processing';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        case 'shipped': return 'Shipped';
        default: return 'secondary';
    }
}

require_once __DIR__ . '/../includes/footer.php'; 
?>