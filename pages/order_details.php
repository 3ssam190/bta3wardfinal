<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id exists
if (!isset($_GET['order_id'])) {
    header("Location: order_history.php");
    exit();
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

// Get order details
$order = [];
$orderItems = [];
try {
    // Verify order belongs to user and get details
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            p.payment_method,
            p.payment_status,
            p.rejection_reason,
            p.verification_expiry
        FROM Orders o
        LEFT JOIN Payments p ON o.order_id = p.order_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found or doesn't belong to you");
    }

    // Get order items with product info
    $stmt = $conn->prepare("
        SELECT 
            oi.*, 
            p.name,
            p.description,
            (SELECT image_url FROM ProductImages WHERE product_id = p.product_id LIMIT 1) as image_url
        FROM OrderItems oi
        JOIN Products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

$pageTitle = __('order_details');
?>

<!-- Animated Background -->
<div class="details-background">
    <div class="details-leaf leaf-1"></div>
    <div class="details-leaf leaf-2"></div>
</div>

<section class="details-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                    <p>Please check the order number or contact support.</p>
                    <a href="order_history.php" class="btn btn-outline-secondary mt-2">
                        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_orders'); ?>
                    </a>
                </div>
                <?php elseif (empty($order)): ?>
                <div class="alert alert-warning">
                    <h4><?php echo __('no_orders_found'); ?></h4>
                    <p><?php echo __('no_orders_message'); ?></p>
                    <a href="order_history.php" class="btn btn-outline-secondary mt-2">
                        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_orders'); ?>
                    </a>
                </div>
                <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?php echo __('order_details'); ?></h1>
                    <div class="order-number">
                        <span class="badge bg-light text-dark"><?php echo __('order'); ?>#<?php echo str_pad($orderId, 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
                
                <div class="d-print-none mb-4">
                    <a href="order_history.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_orders'); ?>
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-print me-2"></i><?php echo __('print_order'); ?>
                    </button>
                </div>
                
                <div class="order-details-card">
                    <div class="order-header">
                        <div class="order-status">
                            <span class="badge bg-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            <small class="text-muted"><?php echo __('ordered_on'); ?> <?php echo date('F j, Y \a\t g:i a', strtotime($order['order_date'])); ?></small>
                        </div>
                        <?php if ($order['status'] === 'Pending' || $order['status'] === 'Processing'): ?>
                        <button class="btn btn-sm btn-outline-danger cancel-order" data-order-id="<?php echo $orderId; ?>">
                            <i class="fas fa-times me-2"></i><?php echo __('cancel_order'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="order-items-container">
                                <h5 class="mb-4"><?php echo __('order_items'); ?></h5>
                                <?php foreach ($orderItems as $item): ?>
                                <div class="order-item">
                                    <div class="order-item-image">
                                        <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/admin/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-item-details">
                                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <?php if (!empty($item['description'])): ?>
                                        <p class="text-muted small"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <?php endif; ?>
                                        <div class="order-item-meta">
                                            <span><?php echo $item['quantity']; ?> × <?php echo CURRENCY . number_format($item['unit_price'], 2); ?></span>
                                            <strong><?php echo CURRENCY . number_format($item['quantity'] * $item['unit_price'], 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="receipt-container d-none d-print-block">
    <div class="receipt">
        <div class="receipt-header">
            <h2>Bta3 Ward Store</h2>
            <p>Plant Nursery & Garden Center</p>
            <p>Order Receipt</p>
            <p>Date: <?php echo date('M j, Y g:i a', strtotime($order['order_date'])); ?></p>
            <p>Order #: <?php echo str_pad($orderId, 5, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <div class="receipt-body">
            <div class="receipt-item" style="font-weight: bold;">
                <span>Item</span>
                <span>Total</span>
            </div>
            
            <?php foreach ($orderItems as $item): ?>
            <div class="receipt-item">
                <span><?php echo htmlspecialchars($item['name']); ?> (<?php echo $item['quantity']; ?> × <?php echo CURRENCY . number_format($item['unit_price'], 2); ?>)</span>
                <span><?php echo CURRENCY . number_format($item['quantity'] * $item['unit_price'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            
            <div class="receipt-totals">
                <div class="receipt-item">
                    <span>Subtotal:</span>
                    <span><?php echo CURRENCY . number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
                </div>
                <div class="receipt-item">
                    <span>Delivery Fee:</span>
                    <span><?php echo CURRENCY . number_format($order['delivery_fee'], 2); ?></span>
                </div>
                <div class="receipt-item" style="font-weight: bold;">
                    <span>Total:</span>
                    <span><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p>Contact: support@bta3ward.shop</p>
            <p>Phone: 01011960681</p>
        </div>
    </div>
</div>
                        <div class="col-md-4">
                            <div class="order-summary-card">
                                <h5 class="mb-4"><?php echo __('order_summary'); ?></h5>
                                
                                <div class="delivery-info mb-4">
                                    <h6><?php echo __('delivery_address'); ?></h6>
                                    <p>
                                        <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?><br>
                                        <?php echo htmlspecialchars($order['delivery_city']); ?>, 
                                        <?php echo htmlspecialchars($order['delivery_region']); ?>
                                    </p>
                                    <?php if (!empty($order['notes'])): ?>
                                    <h6 class="mt-3"><?php echo __('order_notes'); ?>:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="payment-info mb-4">
                                    <h6><?php echo __('payment_information'); ?></h6>
                                    <p>
                                        <strong><?php echo __('method'); ?>:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Not specified'); ?><br>
                                        <strong><?php echo __('status'); ?>:</strong> 
                                        <span class="text-<?php 
                                            echo ($order['payment_status'] === 'Completed') ? 'success' : 
                                                 (($order['payment_status'] === 'Rejected' || $order['payment_status'] === 'Failed') ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?>
                                        </span>
                                    </p>
                                    
                                    <?php if (($order['status'] === 'Cancelled' || $order['payment_status'] === 'Rejected') && 
                                              $order['payment_method'] === 'Vodafone Cash' && 
                                              !empty($order['rejection_reason'])): ?>
                                    <div class="alert alert-danger mt-3 p-3">
                                        <h6 class="alert-heading"><?php echo __('rejection_reason'); ?></h6>
                                        <hr>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['rejection_reason'])); ?></p>
                                        <?php if (!empty($order['verification_expiry'])): ?>
                                        <hr>
                                        <small class="text-muted">Verification expired on: <?php echo date('M j, Y g:i a', strtotime($order['verification_expiry'])); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-totals">
                                    <div class="order-total-row">
                                        <span>Subtotal</span>
                                        <span><?php echo CURRENCY . number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
                                    </div>
                                    <div class="order-total-row">
                                        <span>Delivery Fee</span>
                                        <span><?php echo CURRENCY . number_format($order['delivery_fee'], 2); ?></span>
                                    </div>
                                    <div class="order-total-row grand-total">
                                        <span>Total</span>
                                        <span><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
@media print {
    /* Hide all elements except the receipt */
    body * {
        visibility: hidden;
    }
    
    /* Show only the receipt container */
    .receipt-container, .receipt-container * {
        visibility: visible;
    }
    
    /* Position the receipt at the top left */
    .receipt-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
        box-shadow: none;
        border: none;
    }
    
    /* Remove backgrounds and set font colors for better printing */
    .receipt-container {
        background: white;
        color: black;
    }
    
    /* Hide buttons and other unnecessary elements */
    .d-print-none, .order-header, .cancel-order, .details-background {
        display: none !important;
    }
    
    /* Adjust receipt styling */
    .receipt {
        width: 80mm; /* Standard receipt width */
        margin: 0 auto;
        font-family: Arial, sans-serif;
        font-size: 12px;
        padding: 10px;
    }
    
    .receipt-header {
        text-align: center;
        margin-bottom: 15px;
        border-bottom: 1px dashed #000;
        padding-bottom: 10px;
    }
    
    .receipt-header h2 {
        font-size: 18px;
        margin: 5px 0;
    }
    
    .receipt-header p {
        margin: 3px 0;
        font-size: 11px;
    }
    
    .receipt-body {
        margin-bottom: 15px;
    }
    
    .receipt-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .receipt-totals {
        border-top: 1px dashed #000;
        padding-top: 10px;
        margin-top: 10px;
    }
    
    .receipt-footer {
        text-align: center;
        font-size: 10px;
        margin-top: 15px;
        border-top: 1px dashed #000;
        padding-top: 10px;
    }
}
.order-details-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.order-item {
    display: flex;
    gap: 1.5rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f1f1;
}

.order-item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

.order-summary-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
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
}

/* Status badge colors */
.bg-pending { background-color: #ffc107; color: #212529; }
.bg-confirmed { background-color: #0dcaf0; color: #212529; }
.bg-processing { background-color: #fd7e14; color: #212529; }
.bg-shipped { background-color: #20c997; }
.bg-delivered { background-color: #198754; }
.bg-cancelled { background-color: #dc3545; }

/* Rejection reason styling */
.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    border-radius: 0.375rem;
}

.alert-danger hr {
    border-top-color: #f1b0b7;
    margin: 0.5rem 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel order button handler
    document.querySelector('.cancel-order')?.addEventListener('click', function(e) {
        e.preventDefault();
        const orderId = this.dataset.orderId;
        
        if (confirm('Are you sure you want to cancel this order?')) {
            fetch('cancel_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the order');
            });
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 
?>