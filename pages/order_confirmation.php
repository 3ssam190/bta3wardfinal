<?php
require_once __DIR__ . '/../config.php';

// Check if order_id exists BEFORE including header.php
if (!isset($_GET['order_id'])) {
    header("Location: shop.php");
    exit();
}

$orderId = (int)$_GET['order_id'];
require_once __DIR__ . '/../includes/header.php';

// $orderId = (int)$_GET['order_id'];

// Get order details
$order = [];
$orderItems = [];
try {
    $stmt = $conn->prepare("
        SELECT o.*, p.payment_method, p.payment_status 
        FROM Orders o
        LEFT JOIN Payments p ON o.order_id = p.order_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

   // Get order items with primary product image
$stmt = $conn->prepare("
    SELECT 
        oi.*, 
        p.name,
        pi.image_url
    FROM OrderItems oi
    JOIN Products p ON oi.product_id = p.product_id
    LEFT JOIN ProductImages pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

$pageTitle = __('order_confirmation');
?>

<!-- Animated Background -->
<div class="confirmation-background">
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
</div>

<section class="confirmation-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php else: ?>
                <div class="confirmation-card">
                    <div class="confirmation-header">
                        <div class="confirmation-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1><?php echo __('thank_you'); ?></h1>
                        <p class="lead"><?php echo __('order_confirmed'); ?></p>
                    </div>
                    
                    <div class="confirmation-body">
                        <div class="order-summary">
                            <div class="order-summary-item">
                                <span><?php echo __('order_number'); ?></span>
                                <strong>#<?php echo $orderId; ?></strong>
                            </div>
                            <div class="order-summary-item">
                                <span><?php echo __('order_date'); ?></span>
                                <strong><?php echo date('F j, Y', strtotime($order['order_date'])); ?></strong>
                            </div>
                            <div class="order-summary-item">
                                <span><?php echo __('order_total'); ?></span>
                                <strong><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></strong>
                            </div>
                            <div class="order-summary-item">
                                <span><?php echo __('payment_method'); ?></span>
                                <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                            </div>
                            <div class="order-summary-item">
                                <span><?php echo __('payment_status'); ?></span>
                                <strong class="text-<?php echo ($order['payment_status'] === 'Completed') ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($order['payment_status']); ?>
                                </strong>
                            </div>
                        </div>
                        
                        <div class="delivery-info mt-4">
                            <h4><?php echo __('delivery_details'); ?></h4>
                            <p>
                                <?php echo htmlspecialchars($order['delivery_address']); ?><br>
                                <?php echo htmlspecialchars($order['delivery_city']); ?>, 
                                <?php echo htmlspecialchars($order['delivery_region']); ?>
                            </p>
                            <?php if (!empty($order['notes'])): ?>
                            <h5><?php echo __('order_notes'); ?></h5>
                            <p><?php echo htmlspecialchars($order['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ordered-items mt-5">
                            <h4><?php echo __('your_order'); ?></h4>
                            <div class="ordered-items-list">
                                <?php foreach ($orderItems as $item): ?>
                                <div class="ordered-item">
                                    <div class="ordered-item-image">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="../admin/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-leaf"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ordered-item-details">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <div class="ordered-item-meta">
                                            <span><?php echo $item['quantity']; ?> × <?php echo CURRENCY . number_format($item['unit_price'], 2); ?></span>
                                            <strong><?php echo CURRENCY . number_format($item['quantity'] * $item['unit_price'], 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="order-totals mt-4">
                            <div class="order-total-row">
                                <span><?php echo __('subtotal'); ?></span>
                                <span><?php echo CURRENCY . number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
                            </div>
                            <div class="order-total-row">
                                <span><?php echo __('delivery_fee'); ?></span>
                                <span><?php echo CURRENCY . number_format($order['delivery_fee'], 2); ?></span>
                            </div>
                            <div class="order-total-row grand-total">
                                <span><?php echo __('total'); ?></span>
                                <span><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="confirmation-footer">
                        <p class="text-muted mb-4"><?php echo __('confirmation_message'); ?></p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="shop.php" class="btn btn-outline-success">
                                <i class="fas fa-shopping-bag me-2"></i><?php echo __('continue_shopping'); ?>
                            </a>
                            <a href="order_history.php" class="btn btn-primary">
                                <i class="fas fa-history me-2"></i><?php echo __('view_order_history'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Confirmation Background */
.confirmation-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9f5e9 100%);
    overflow: hidden;
    z-index: -1;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: #2e7d32;
    opacity: 0.5;
    animation: confetti-fall 5s linear infinite;
}

.confetti:nth-child(1) {
    left: 10%;
    animation-delay: 0s;
    background-color: #2e7d32;
}
.confetti:nth-child(2) {
    left: 30%;
    animation-delay: 1s;
    background-color: #4caf50;
}
.confetti:nth-child(3) {
    left: 50%;
    animation-delay: 2s;
    background-color: #8bc34a;
}
.confetti:nth-child(4) {
    left: 70%;
    animation-delay: 3s;
    background-color: #cddc39;
}
.confetti:nth-child(5) {
    left: 90%;
    animation-delay: 4s;
    background-color: #ffeb3b;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

/* Confirmation Section */
.confirmation-section {
    position: relative;
    z-index: 1;
}

.confirmation-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}

.confirmation-header {
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
    color: white;
}

.confirmation-icon {
    font-size: 5rem;
    color: white;
    margin-bottom: 1rem;
    animation: bounce 1s ease;
}

.confirmation-header h1 {
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.confirmation-header .lead {
    opacity: 0.9;
    font-weight: 300;
}

.confirmation-body {
    padding: 2rem;
}

.order-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.order-summary-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    text-align: left;
}

.order-summary-item span {
    display: block;
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.3rem;
}

.order-summary-item strong {
    font-size: 1.1rem;
}

.delivery-info {
    text-align: left;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.delivery-info h4 {
    margin-bottom: 1rem;
    color: #2e7d32;
}

.ordered-items {
    text-align: left;
}

.ordered-items h4 {
    margin-bottom: 1.5rem;
    color: #2e7d32;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.ordered-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ordered-item {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    align-items: center;
}

.ordered-item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ordered-item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 1.5rem;
}

.ordered-item-details {
    flex: 1;
}

.ordered-item-details h5 {
    margin-bottom: 0.5rem;
}

.ordered-item-meta {
    display: flex;
    justify-content: space-between;
}

.order-totals {
    max-width: 400px;
    margin-left: auto;
}

.order-total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #dee2e6;
}

.order-total-row.grand-total {
    font-weight: 700;
    font-size: 1.1rem;
    border-bottom: none;
    margin-top: 0.5rem;
}

.confirmation-footer {
    padding: 2rem;
    border-top: 1px solid #dee2e6;
}

/* Animations */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

/* Responsive */
@media (max-width: 767.98px) {
    .order-summary {
        grid-template-columns: 1fr;
    }
    
    .ordered-item {
        flex-direction: column;
        text-align: center;
    }
    
    .ordered-item-meta {
        justify-content: center;
        gap: 1rem;
    }
    
    .order-totals {
        max-width: 100%;
    }
}
</style>

<script>
// Simple animation trigger
document.addEventListener('DOMContentLoaded', function() {
    const confirmationIcon = document.querySelector('.confirmation-icon');
    if (confirmationIcon) {
        setTimeout(() => {
            confirmationIcon.style.animation = 'none';
            setTimeout(() => {
                confirmationIcon.style.animation = 'bounce 1s ease';
            }, 10);
        }, 1000);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>