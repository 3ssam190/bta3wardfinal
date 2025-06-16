<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

// Make sure this is the VERY FIRST LINE with no whitespace before it
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Check if user is logged in - BEFORE ANY OUTPUT
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if role is set in session, if not set a default or redirect
if (!isset($_SESSION['role'])) {
    // Either set a default role (if appropriate for your application)
    $_SESSION['role'] = 'Super Admin';
    
    // Or redirect to login if role is required
    header('Location: login.php');
    exit;
}

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

// Handle verification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    $admin_id = $_SESSION['admin_id'];

    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        // Get payment details
        $stmt = $pdo->prepare("
            SELECT p.*, o.total_amount, o.delivery_fee 
            FROM Payments p
            JOIN Orders o ON p.order_id = o.order_id
            WHERE p.order_id = ? AND p.payment_method = 'Vodafone Cash'
        ");
        $stmt->execute([$order_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception('Vodafone Cash payment not found for this order');
        }

        // Start transaction
        $pdo->beginTransaction();

        if ($action === 'approve') {
            // Approve payment
            $stmt = $pdo->prepare("
                UPDATE Payments 
                SET payment_status = 'Completed',
                    verified_at = NOW(),
                    verified_by = ?
                WHERE order_id = ?
            ");
            $stmt->execute([$admin_id, $order_id]);

            // Update order status
            $stmt = $pdo->prepare("
                UPDATE Orders 
                SET status = 'Processing'
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);

            $_SESSION['success'] = "Payment approved and order marked for processing";

        } elseif ($action === 'reject') {
            $reason = $_POST['reason'] ?? 'Payment verification failed';

            // Reject payment
            $stmt = $pdo->prepare("
                UPDATE Payments 
                SET payment_status = 'Rejected',
                    rejection_reason = ?,
                    verified_at = NOW(),
                    verified_by = ?
                WHERE order_id = ?
            ");
            $stmt->execute([$reason, $admin_id, $order_id]);

            // Cancel order
            $stmt = $pdo->prepare("
                UPDATE Orders 
                SET status = 'Cancelled'
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);

            $_SESSION['success'] = "Payment rejected and order cancelled";
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: verify_vodafone_payments.php');
    exit;
}



// Get pending Vodafone Cash payments
$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_date,
        o.status,
        o.total_amount,
        o.delivery_fee,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
        p.payment_id,
        p.payment_status,
        p.transaction_id,
        p.payment_date,
        p.payment_screenshot,
        p.verification_expiry
    FROM Orders o
    JOIN Users u ON o.user_id = u.user_id
    JOIN Payments p ON o.order_id = p.order_id
    WHERE p.payment_method = 'Vodafone Cash'
    AND (p.payment_status IS NULL OR p.payment_status = 'Pending')
    ORDER BY o.order_date DESC
");
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <style>
        .proof-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .proof-thumbnail:hover {
            transform: scale(1.5);
        }
        .verification-badge {
            font-size: 0.8rem;
        }
        .countdown-timer {
            font-family: monospace;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-header header-text">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">
                                <i class="fas fa-mobile-alt me-2"></i> Vodafone Cash Payment Verification
                            </h3>
                            <span class="badge bg-light text-dark">
                                <?= count($payments) ?> Pending
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); endif; ?>

                        <?php if (empty($payments)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle me-2"></i> No pending Vodafone Cash payments to verify
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Proof</th>
                                            <th>Time Left</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td class="fw-bold">#<?= str_pad($payment['order_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                                <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                                                <td><?= date('M j, Y g:i A', strtotime($payment['order_date'])) ?></td>
                                                <td class="fw-bold">EGP <?= number_format($payment['total_amount'] + $payment['delivery_fee'], 2) ?></td>
                                                <td>
                                                    <?php if ($payment['payment_screenshot']): ?>
                                                        <img src="../assets/images/payment_proofs/<?= htmlspecialchars($payment['payment_screenshot']) ?>" 
                                                             class="proof-thumbnail rounded border" 
                                                             data-bs-toggle="modal" 
                                                             data-bs-target="#proofModal"
                                                             data-proof-src="../assets/images/payment_proofs/<?= htmlspecialchars($payment['payment_screenshot']) ?>">
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="countdown-timer" data-expiry="<?= htmlspecialchars(date('c', strtotime($payment['verification_expiry']))) ?>">
                                                    Calculating...
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-success verify-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#verifyModal"
                                                                data-order-id="<?= $payment['order_id'] ?>"
                                                                data-amount="<?= $payment['total_amount'] + $payment['delivery_fee'] ?>">
                                                            <i class="fas fa-check-circle me-1"></i> Verify
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger reject-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal"
                                                                data-order-id="<?= $payment['order_id'] ?>">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="verify_vodafone_payments.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="order_id" id="verifyOrderId">
                        <input type="hidden" name="action" value="approve">
                        
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Confirm you've verified this payment in the store's Vodafone Cash account (01011960681)
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Order ID:</label>
                            <span id="verifyOrderNumber" class="ms-2"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Amount:</label>
                            <span id="verifyAmount" class="ms-2"></span>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmVerification" required>
                            <label class="form-check-label" for="confirmVerification">
                                I confirm the payment has been received
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="verify_vodafone_payments.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="order_id" id="rejectOrderId">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> Please provide a reason for rejecting this payment
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Order ID:</label>
                            <span id="rejectOrderNumber" class="ms-2"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label fw-bold">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Proof Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="proofImage" src="" class="img-fluid" alt="Payment proof">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadProof" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Handle verification modals
    document.addEventListener('DOMContentLoaded', function() {
        // Verification modal setup
        const verifyModal = document.getElementById('verifyModal');
        if (verifyModal) {
            verifyModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const orderId = button.getAttribute('data-order-id');
                const amount = button.getAttribute('data-amount');
                
                document.getElementById('verifyOrderId').value = orderId;
                document.getElementById('verifyOrderNumber').textContent = '#' + orderId.toString().padStart(5, '0');
                document.getElementById('verifyAmount').textContent = 'EGP ' + parseFloat(amount).toFixed(2);
            });
        }

        // Rejection modal setup
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const orderId = button.getAttribute('data-order-id');
                
                document.getElementById('rejectOrderId').value = orderId;
                document.getElementById('rejectOrderNumber').textContent = '#' + orderId.toString().padStart(5, '0');
                document.getElementById('rejectReason').value = '';
            });
        }

        // Proof image modal setup
        const proofModal = document.getElementById('proofModal');
        if (proofModal) {
            proofModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const proofSrc = button.getAttribute('data-proof-src');
                
                document.getElementById('proofImage').src = proofSrc;
                document.getElementById('downloadProof').href = proofSrc;
            });
        }

        // Countdown timers
        function updateCountdown() {
    document.querySelectorAll('.countdown-timer').forEach(timer => {
        const expiryString = timer.getAttribute('data-expiry');
        if (!expiryString) {
            timer.innerHTML = '<span class="badge bg-secondary">N/A</span>';
            return;
        }

        const expiry = new Date(expiryString);
        if (isNaN(expiry.getTime())) {
            timer.innerHTML = '<span class="badge bg-secondary">Invalid Date</span>';
            return;
        }

        const now = new Date();
        const distance = expiry - now;
        
        if (distance < 0) {
            timer.innerHTML = '<span class="badge bg-danger">EXPIRED</span>';
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        timer.innerHTML = `
            <span class="badge bg-${hours < 1 ? 'warning' : 'info'}">
                ${hours}h ${minutes}m ${seconds}s
            </span>
        `;
    });
}

        // Update immediately and every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
    </script>
<?php 
require_once __DIR__ . '/includes/footer.php';
?>