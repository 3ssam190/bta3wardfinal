<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/PaymobHelper.php';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set default values for form
    $defaultValues = [
        'full_name' => $userInfo['first_name'] . ' ' . $userInfo['last_name'],
        'email' => $userInfo['email'],
        'phone' => $userInfo['phone'],
        'address' => $userInfo['address'],
        'city' => $userInfo['city'],
        'region' => $userInfo['region']
    ];
} else {
    $defaultValues = [
        'full_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'region' => ''
    ];
}
// Verify cart exists and get items
if (empty($_SESSION['cart_id']) || empty($cartItems = getCartItems($_SESSION['cart_id']))) {
    header("Location: cart");
    exit();
}



$subtotal = calculateCartTotal($cartItems);
$deliveryRegions = getDeliveryRegions();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['full_name', 'email', 'phone', 'address', 'city', 'region', 'payment_method'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Get delivery fee
        $deliveryFee = getDeliveryFee($_POST['region']);
        $totalAmount = $subtotal + $deliveryFee;

        // Start transaction
        $conn->beginTransaction();

        try {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO Orders (user_id, total_amount, delivery_fee, status, delivery_address, delivery_city, delivery_region, notes) 
                VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $totalAmount,
                $deliveryFee,
                $_POST['address'],
                $_POST['city'],
                $_POST['region'],
                $_POST['notes'] ?? null
            ]);
            $orderId = $conn->lastInsertId();

            // Add order items
            foreach ($cartItems as $item) {
                $stmt = $conn->prepare("
                    INSERT INTO OrderItems (order_id, product_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // Handle different payment methods
            if ($_POST['payment_method'] === 'Credit Card') {
                // Process Paymob payment
                $paymob = new PaymobHelper();
                
                // 1. Authenticate with Paymob
                $authToken = $paymob->authenticate();
                if (!$authToken) throw new Exception("Payment authentication failed");
                
                // 2. Create Paymob order
                $paymobOrderId = $paymob->createOrder($authToken, $totalAmount, $orderId, [
                    [
                        'name' => 'Plant Store Purchase',
                        'amount_cents' => $totalAmount * 100,
                        'description' => 'Order #' . $orderId,
                        'quantity' => '1'
                    ]
                ]);
                if (!$paymobOrderId) throw new Exception("Payment order creation failed");
                
                // 3. Get payment key
                $billingData = [
                    "apartment" => "NA", 
                    "email" => $_POST['email'],
                    "floor" => "NA",
                    "first_name" => explode(' ', $_POST['full_name'])[0],
                    "street" => $_POST['address'],
                    "building" => "NA",
                    "phone_number" => $_POST['phone'],
                    "shipping_method" => "NA", 
                    "postal_code" => "NA", 
                    "city" => $_POST['city'], 
                    "country" => "NA", 
                    "last_name" => explode(' ', $_POST['full_name'])[1] ?? '', 
                    "state" => "NA"
                ];
                
                $paymentKey = $paymob->getPaymentKey($authToken, $paymobOrderId, $totalAmount, $billingData);
                if (!$paymentKey) throw new Exception("Payment key generation failed");
                
                // Record payment as pending
                $stmt = $conn->prepare("
                    INSERT INTO Payments (order_id, amount, payment_method, payment_status, transaction_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $totalAmount,
                    'Credit Card',
                    'Pending',
                    $paymobOrderId
                ]);
                
                $conn->commit();
                
                 if (!empty($_SESSION['cart_id'])) {
                    clearCart($_SESSION['cart_id']);
                    unset($_SESSION['cart_id']);
                    unset($_SESSION['cart_count']);
                }
                
                // Redirect to Paymob payment page
                header("Location: https://accept.paymob.com/api/acceptance/iframes/" . PAYMOB_IFRAME_ID . "?payment_token=$paymentKey");
                exit();
            }elseif ($_POST['payment_method'] === 'Vodafone Cash') {
                // Record payment as pending verification
                $verificationExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $stmt = $conn->prepare("
                    INSERT INTO Payments (order_id, amount, payment_method, payment_status, transaction_id, verification_expiry)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $totalAmount,
                    'Vodafone Cash',
                    'Pending',
                    uniqid('VC_'), // Generate unique transaction ID
                    $verificationExpiry
                ]);
                
                $conn->commit();
                // Clear cart
                if (!empty($_SESSION['cart_id'])) {
                    clearCart($_SESSION['cart_id']);
                    unset($_SESSION['cart_id']);
                    unset($_SESSION['cart_count']);
                }
            
                // Redirect to payment instructions page
                header("Location: vodafone_payment_instructions?order_id=".$orderId);
                exit();
            }else {
                // Record other payment methods
                $paymentStatus = ($_POST['payment_method'] === 'COD') ? 'Pending' : 'Completed';
                $stmt = $conn->prepare("
                    INSERT INTO Payments (order_id, amount, payment_method, payment_status)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $totalAmount,
                    $_POST['payment_method'],
                    $paymentStatus
                ]);

                $conn->commit();

                 if (!empty($_SESSION['cart_id'])) {
                    clearCart($_SESSION['cart_id']);
                    unset($_SESSION['cart_id']);
                    unset($_SESSION['cart_count']);
                }

                // Redirect to confirmation
                header("Location: order_confirmation?order_id=".$orderId);
                exit();
            }
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!-- Animated Checkout Background -->
<div class="checkout-background">
    <div class="checkout-leaf leaf-1"></div>
    <div class="checkout-leaf leaf-2"></div>
    <div class="checkout-particle particle-1"></div>
    <div class="checkout-particle particle-2"></div>
</div>

<div class="checkout-container py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="checkout-header mb-5">
                    <div class="d-flex align-items-center">
                        <h1 class="mb-0 me-3"><?php echo __('proceed_checkout'); ?></h1>
                        <div class="checkout-steps">
                            <span class="step active">1. <?php echo __('shipping'); ?></span>
                            <span class="step">2. <?php echo __('payment_info'); ?></span>
                            <span class="step">3. <?php echo __('order_confirmation'); ?></span>
                        </div>
                    </div>
                    <div class="checkout-progress">
                        <div class="progress-bar" style="width: 33%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" class="checkout-form">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5><i class="fas fa-truck me-2"></i> <?php echo __('delivery_details'); ?></h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label"><?php echo __('full_name'); ?></label>
                                    <input type="text" name="full_name" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? $defaultValues['full_name']); ?>">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label"><?php echo __('email'); ?></label>
                                    <input type="email" name="email" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>
                                           <?php echo htmlspecialchars($_POST['email'] ?? $defaultValues['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label"><?php echo __('phone_number'); ?></label>
                                    <input type="tel" name="phone" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? $defaultValues['phone']); ?>">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label"><?php echo __('delivery_region'); ?></label>
                                    <select name="region" class="form-select" required>
                                    <?php 
                                    $deliveryRegions = $conn->query("SELECT * FROM DeliveryPricing ORDER BY region_name")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($deliveryRegions as $region): ?>
                                    <option value="<?php echo $region['region_id']; ?>">
                                        <?php echo htmlspecialchars($region['region_name']); ?> 
                                        (<?php echo CURRENCY . $region['delivery_fee']; ?> - <?php echo $region['estimated_delivery_days']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label"><?php echo __('shipping_address'); ?></label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? $defaultValues['address']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label"><?php echo __('state_province'); ?></label>
                                    <input type="text" name="city" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_POST['city'] ?? $defaultValues['city']); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label"><?php echo __('order_notes'); ?></label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5><i class="fas fa-credit-card me-2"></i> <?php echo __('payment_methods'); ?></h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="cod" value="COD" checked>
                                    <label for="cod">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h6><?php echo __('cash_on_delivery'); ?></h6>
                                            <p><?php echo __('pay_when_receive'); ?></p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="credit" value="Credit Card">
                                    <label for="credit">
                                        <div class="payment-icon">
                                            <i class="far fa-credit-card"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h6><?php echo __('credit_card'); ?></h6>
                                            <p><?php echo __('pay_securely'); ?></p>
                                        </div>
                                    </label>
                                </div>
                                
                                <!--<div class="payment-method">-->
                                <!--    <input type="radio" name="payment_method" id="fawry" value="Fawry">-->
                                <!--    <label for="fawry">-->
                                <!--        <div class="payment-icon">-->
                                <!--            <i class="fas fa-mobile-alt"></i>-->
                                <!--        </div>-->
                                <!--        <div class="payment-details">-->
                                <!--            <h6><?php echo __('fawry'); ?></h6>-->
                                <!--            <p><?php echo __('pay_through_fawry'); ?></p>-->
                                <!--        </div>-->
                                <!--    </label>-->
                                <!--</div>-->
                                
                                <div class="payment-method">
                                    <input type="radio" name="payment_method" id="vodafone" value="Vodafone Cash">
                                    <label for="vodafone">
                                        <div class="payment-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h6><?php echo __('vodafone_cash'); ?></h6>
                                            <p><?php echo __('pay_with_vodafone'); ?></p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <a href="cart" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_cart'); ?>
                        </a>
                        <button type="submit" id="complete-order-btn" class="btn btn-success btn-lg">
                            <?php echo __('complete_order'); ?> <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="col-lg-4">
                <div class="order-summary-card">
                    <div class="order-summary-header">
                        <h5><i class="fas fa-receipt me-2"></i> <?php echo __('order_summary'); ?></h5>
                    </div>
                    <div class="order-summary-body">
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <?php if (!empty($item['image_url'])): ?>
                                    <img src="../admin/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-details">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <div class="d-flex justify-content-between">
                                        <span><?php echo $item['quantity']; ?> × <?php echo CURRENCY . number_format($item['price'], 2); ?></span>
                                        <span><?php echo CURRENCY . number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-summary-divider"></div>
                        
                        <div class="order-summary-row">
                            <span><?php echo __('subtotal'); ?></span>
                            <span><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="order-summary-row">
                            <span><?php echo __('delivery_fee'); ?></span>
                            <span id="delivery-fee"><?php echo CURRENCY . '0.00'; ?></span>
                        </div>
                        
                        <div class="order-summary-divider"></div>
                        
                        <div class="order-summary-row total">
                            <span><?php echo __('total'); ?></span>
                            <span id="order-total"><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Checkout Background */
.checkout-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9f5e9 100%);
    overflow: hidden;
    z-index: -1;
}

.checkout-leaf {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.1;
    z-index: -1;
    animation: float 20s linear infinite;
}

.checkout-particle {
    position: absolute;
    background-color: rgba(46, 125, 50, 0.1);
    border-radius: 50%;
    z-index: -1;
    animation: float 25s linear infinite;
}

.checkout-leaf.leaf-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf1.png');
    width: 180px;
    height: 180px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.checkout-leaf.leaf-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf2.png');
    width: 120px;
    height: 120px;
    top: 70%;
    left: 80%;
    animation-delay: 5s;
    animation-direction: reverse;
}

.checkout-particle.particle-1 {
    width: 15px;
    height: 15px;
    top: 30%;
    left: 20%;
    animation-delay: 2s;
}

.checkout-particle.particle-2 {
    width: 20px;
    height: 20px;
    top: 60%;
    left: 30%;
    animation-delay: 7s;
}

/* Checkout Container */
.checkout-container {
    position: relative;
    z-index: 1;
}

.checkout-header {
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.checkout-steps {
    display: flex;
    gap: 1.5rem;
}

.checkout-steps .step {
    color: #6c757d;
    font-weight: 500;
    position: relative;
}

.checkout-steps .step.active {
    color: #2e7d32;
    font-weight: 600;
}

.checkout-steps .step.active::after {
    content: '';
    position: absolute;
    bottom: -1.1rem;
    left: 0;
    width: 100%;
    height: 3px;
    background: #2e7d32;
    border-radius: 3px;
}

.checkout-progress {
    height: 3px;
    background: #e9ecef;
    margin-top: 1.5rem;
    border-radius: 3px;
    overflow: hidden;
}

.checkout-progress .progress-bar {
    height: 100%;
    background: #2e7d32;
    transition: width 0.5s ease;
}

/* Checkout Cards */
.checkout-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.checkout-card-header {
    background: #f8f9fa;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.checkout-card-header h5 {
    margin: 0;
    font-weight: 600;
    color: #2e7d32;
}

.checkout-card-body {
    padding: 1.5rem;
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-method {
    position: relative;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-method label {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method input[type="radio"]:checked + label {
    border-color: #2e7d32;
    background-color: rgba(46, 125, 50, 0.05);
}

.payment-method:hover label {
    border-color: #adb5bd;
}

.payment-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #2e7d32;
    font-size: 1.2rem;
}

.payment-details h6 {
    margin: 0;
    font-weight: 600;
}

.payment-details p {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

/* Order Summary */
.order-summary-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.order-summary-header {
    background: #2e7d32;
    color: white;
    padding: 1.25rem 1.5rem;
}

.order-summary-header h5 {
    margin: 0;
    font-weight: 600;
}

.order-summary-body {
    padding: 1.5rem;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.order-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px dashed #e9ecef;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-details {
    flex: 1;
}

.order-item-details h6 {
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.order-item-details span {
    font-size: 0.9rem;
    color: #6c757d;
}

.order-summary-divider {
    height: 1px;
    background: #e9ecef;
    margin: 1.5rem 0;
}

.order-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.order-summary-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 991.98px) {
    .checkout-steps {
        display: none;
    }
    
    .checkout-progress {
        margin-top: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    .payment-method label {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .payment-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Update delivery fee and total when region changes
document.querySelector('select[name="region"]').addEventListener('change', function() {
    const regionId = this.value;
    fetch('<?php echo BASE_URL; ?>/api/get_delivery_fee?region_id=' + regionId)
        .then(response => response.json())
        .then(data => {
            document.getElementById('delivery-fee').textContent = '<?php echo CURRENCY; ?>' + data.fee.toFixed(2);
            const subtotal = <?php echo $subtotal; ?>;
            document.getElementById('order-total').textContent = '<?php echo CURRENCY; ?>' + (subtotal + data.fee).toFixed(2);
        });
});

// Add animation to payment method selection
document.querySelectorAll('.payment-method input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method label').forEach(label => {
            label.style.transform = 'scale(1)';
        });
        
        if (this.checked) {
            this.nextElementSibling.style.transform = 'scale(1.02)';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>