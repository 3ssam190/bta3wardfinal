<?php
ob_start();
include __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
$pageTitle = __('cart');

// Initialize variables with default values
$cartItems = [];
$total = 0;
$subtotal = 0;
$cartId = null;

// Get or create cart
if (isset($_SESSION['user_id'])) {
    $cart = getOrCreateCart($_SESSION['user_id'], null);
} else {
    $cart = getOrCreateCart(null, session_id());
}

if ($cart) {
    $_SESSION['cart_id'] = $cart['cart_id'];
    $cartId = $cart['cart_id'];
    
    // Get cart items with product details or custom data
    $stmt = $conn->prepare("
    SELECT 
        ci.*, 
        p.name, 
        p.stock_quantity,
        ci.custom_data,
        (SELECT image_url FROM ProductImages WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_url
    FROM CartItems ci
    LEFT JOIN Products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
    $stmt->execute([$cartId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    if (!empty($cartItems)) {
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $total = $subtotal; // Delivery fee would be added here if applicable
    }
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    if (!empty($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $itemId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0 && $cartId) {
                // For regular products, check stock
                if (isRegularProduct($itemId, $cartId)) {
                    $stmt = $conn->prepare("
                        SELECT p.stock_quantity 
                        FROM CartItems ci
                        JOIN Products p ON ci.product_id = p.product_id
                        WHERE ci.cart_item_id = ? AND ci.cart_id = ?
                    ");
                    $stmt->execute([$itemId, $cartId]);
                    $itemData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($itemData && $quantity <= $itemData['stock_quantity']) {
                        $stmt = $conn->prepare("UPDATE CartItems SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
                        $stmt->execute([$quantity, $itemId, $cartId]);
                    }
                } else {
                    // For custom items, just update quantity
                    $stmt = $conn->prepare("UPDATE CartItems SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
                    $stmt->execute([$quantity, $itemId, $cartId]);
                }
            }
        }
        header("Location: cart");
        ob_end_flush();
        exit();
    }
}

// Handle item removal
if (isset($_GET['remove']) && $cartId) {
    $stmt = $conn->prepare("DELETE FROM CartItems WHERE cart_item_id = ? AND cart_id = ?");
    $stmt->execute([$_GET['remove'], $cartId]);
    
    // Update cart count in session
    updateCartCount($cartId);
    header("Location: cart");
    exit();
}

// Helper function to check if item is a regular product
function isRegularProduct($itemId, $cartId) {
    global $conn;
    $stmt = $conn->prepare("SELECT product_id FROM CartItems WHERE cart_item_id = ? AND cart_id = ?");
    $stmt->execute([$itemId, $cartId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item && $item['product_id'] != null;
}

// Helper function to update cart count in session
function updateCartCount($cartId) {
    global $conn;
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM CartItems WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['cart_count'] = (int)($result['count'] ?? 0);
}

// Helper functions for bouquet data
function getBouquetCoverById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM BouquetCovers WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFlowersForBouquet($flowerCounts) {
    global $conn;
    $flowers = [];
    
    if (!empty($flowerCounts)) {
        $ids = array_column($flowerCounts, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $conn->prepare("SELECT * FROM Flowers WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $flowerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($flowerCounts as $item) {
            foreach ($flowerData as $flower) {
                if ($flower['id'] == $item['id']) {
                    $flowers[] = [
                        'id' => $flower['id'],
                        'name' => $flower['name'],
                        'quantity' => $item['quantity'],
                        'image_url' => $flower['image_url']
                    ];
                    break;
                }
            }
        }
    }
    
    return $flowers;
}
?>

<!-- Animated Background -->
<div class="cart-background">
    <div class="floating-leaf leaf-1"></div>
    <div class="floating-leaf leaf-2"></div>
    <div class="floating-leaf leaf-3"></div>
    <div class="floating-particle particle-1"></div>
    <div class="floating-particle particle-2"></div>
</div>

<section class="cart-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center mb-4">
                    <h1 class="mb-0 me-3"><?php echo __('your_cart'); ?></h1>
                    <div class="cart-badge">
                        <span class="badge bg-success"><?php echo count($cartItems); ?> <?php echo __('items'); ?></span>
                    </div>
                </div>
                
                <?php if (empty($cartItems)): ?>
                <div class="empty-cart-container text-center py-5">
                    <div class="empty-cart-icon mb-4">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <h3 class="mb-3"><?php echo __('cart_empty'); ?></h3>
                    <p class="text-muted mb-4"><?php echo __('cart_empty'); ?></p>
                    <a href="shop" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-left me-2"></i><?php echo __('continue_shopping'); ?>
                    </a>
                </div>
                <?php else: ?>
                <form method="POST" class="cart-form">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="cart-items-container">
                                <?php foreach ($cartItems as $item): ?>
                                <?php if (!empty($item['custom_data'])): ?>
                                <!-- Custom Bouquet Item -->
                                <?php 
    $bouquetData = json_decode($item['custom_data'], true);
    $cover = getBouquetCoverById($bouquetData['cover_id']);
    $flowers = getFlowersForBouquet($bouquetData['flowers']);
    $hasImage = !empty($bouquetData['image_path']);
?>
<div class="cart-item-card custom-bouquet-item" data-item-id="<?php echo $item['cart_item_id']; ?>">
    <div class="row align-items-center">
        <div class="col-md-2">
            <div class="cart-item-image">
                <?php if ($hasImage): ?>
                <img src="<?php echo BASE_URL . htmlspecialchars($bouquetData['image_path']); ?>" 
                     alt="Custom Bouquet Design" 
                     class="img-fluid rounded"
                     data-bs-toggle="modal" 
                     data-bs-target="#bouquetModal"
                     onclick="showBouquetImage('<?php echo BASE_URL . htmlspecialchars($bouquetData['image_path']); ?>')">
                <?php elseif ($cover && !empty($cover['image_url'])): ?>
                <img src="<?php echo BASE_URL; ?>/admin/assets/images/covers/<?php echo htmlspecialchars($cover['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($cover['name']); ?>" 
                     class="img-fluid rounded">
                <?php else: ?>
                <div class="no-image-placeholder">
                    <i class="fas fa-leaf"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
                                        <div class="col-md-4">
                                            <div class="cart-item-details">
                                                <h5 class="cart-item-title"><?php echo __('custom_flower_bouquet'); ?></h5>
                                                <div class="bouquet-details">
                                                    <p class="mb-1"><strong>Size:</strong> <?php echo $bouquetData['flower_count']; ?> <?php echo __('flowers'); ?></p>
                                                    <p class="mb-1"><strong>Cover:</strong> <?php echo $cover ? htmlspecialchars($cover['name']) : 'Not specified'; ?></p>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 view-bouquet-details">
                                                        <?php echo __('view_bouquet_details'); ?>
                                                    </button>
                                                    <div class="bouquet-flower-details mt-2" style="display: none;">
                                                        <ul class="list-unstyled">
                                                            <?php foreach ($flowers as $flower): ?>
                                                            <li><?php echo $flower['quantity']; ?>x <?php echo htmlspecialchars($flower['name']); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-price">
                                                <?php echo CURRENCY; ?><?php echo number_format($item['price'], 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="quantity-control">
                                                <button type="button" class="quantity-btn minus">-</button>
                                                <input type="number" name="quantities[<?php echo $item['cart_item_id']; ?>]" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" class="quantity-input">
                                                <button type="button" class="quantity-btn plus">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-total">
                                                <?php echo CURRENCY; ?><?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </div>
                                            <button type="button" class="cart-item-remove" data-item-id="<?php echo $item['cart_item_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- Regular Product Item -->
                                <div class="cart-item-card" data-item-id="<?php echo $item['cart_item_id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <div class="cart-item-image">
                                                <?php if (!empty($item['image_url'])): ?>
                                                <img src="../admin/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="img-fluid rounded">
                                                <?php else: ?>
                                                <div class="no-image-placeholder">
                                                    <i class="fas fa-leaf"></i>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="cart-item-details">
                                                <h5 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                                <div class="cart-item-stock">
                                                    <span class="badge bg-<?php echo ($item['stock_quantity'] > 0) ? 'success' : 'danger'; ?>">
                                                        <?php echo ($item['stock_quantity'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-price">
                                                <?php echo CURRENCY; ?><?php echo number_format($item['price'], 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="quantity-control">
                                                <button type="button" class="quantity-btn minus">-</button>
                                                <input type="number" name="quantities[<?php echo $item['cart_item_id']; ?>]" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                       class="quantity-input">
                                                <button type="button" class="quantity-btn plus">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-total">
                                                <?php echo CURRENCY; ?><?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </div>
                                            <button type="button" class="cart-item-remove" data-item-id="<?php echo $item['cart_item_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="cart-actions mt-4">
                                <div class="row">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <a href="shop" class="btn btn-outline-success w-100">
                                            <i class="fas fa-arrow-left me-2"></i><?php echo __('continue_shopping'); ?>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <button type="submit" name="update_cart" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-sync-alt me-2"></i><?php echo __('update_cart'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="order-summary-card">
                                <div class="order-summary-header">
                                    <h5><?php echo __('order_summary'); ?></h5>
                                </div>
                                <div class="order-summary-body">
                                    <div class="order-summary-row">
                                        <span><?php echo __('subtotal'); ?></span>
                                        <span><?php echo CURRENCY; ?><?php echo number_format($subtotal, 2); ?></span>
                                    </div>
                                    <div class="order-summary-row">
                                        <span><?php echo __('shipping'); ?></span>
                                        <span>Free</span>
                                    </div>
                                    <div class="order-summary-divider"></div>
                                    <div class="order-summary-row total">
                                        <span><?php echo __('total'); ?></span>
                                        <span><?php echo CURRENCY; ?><?php echo number_format($total, 2); ?></span>
                                    </div>
                                </div>
                                <div class="order-summary-footer">
                                    <a href="checkout" class="btn btn-success w-100">
                                        <?php echo __('proceed_checkout'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Custom Bouquet Styles */
.custom-bouquet-item {
    border-left: 4px solid #4caf50;
    background-color: rgba(76, 175, 80, 0.05);
}

.custom-bouquet-item .cart-item-title {
    color: #4caf50;
}

.bouquet-details {
    font-size: 0.9rem;
}

.bouquet-flower-details {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.85rem;
}

.view-bouquet-details {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.no-image-placeholder {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    border-radius: 5px;
}

.no-image-placeholder i {
    font-size: 2rem;
}
/* Cart Background Animation */
.cart-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9f5e9 100%);
    overflow: hidden;
    z-index: -1;
}

.floating-leaf {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.1;
    z-index: -1;
    animation: float 15s linear infinite;
}

.floating-particle {
    position: absolute;
    background-color: rgba(46, 125, 50, 0.1);
    border-radius: 50%;
    z-index: -1;
    animation: float 20s linear infinite;
}

.leaf-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf1.png');
    width: 150px;
    height: 150px;
    top: 20%;
    left: 5%;
    animation-delay: 0s;
}

.leaf-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf2.png');
    width: 100px;
    height: 100px;
    top: 60%;
    left: 80%;
    animation-delay: 3s;
    animation-direction: reverse;
}

.leaf-3 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf3.png');
    width: 120px;
    height: 120px;
    top: 30%;
    left: 70%;
    animation-delay: 5s;
}

.particle-1 {
    width: 10px;
    height: 10px;
    top: 40%;
    left: 20%;
    animation-delay: 2s;
}

.particle-2 {
    width: 15px;
    height: 15px;
    top: 70%;
    left: 30%;
    animation-delay: 4s;
}

@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-50px) rotate(180deg); }
    100% { transform: translateY(0) rotate(360deg); }
}

/* Cart Section Styles */
.cart-section {
    position: relative;
    z-index: 1;
}

.cart-badge .badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
}

.empty-cart-container {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.empty-cart-icon {
    font-size: 4rem;
    color: #2e7d32;
}

/* Cart Items */
.cart-items-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cart-item-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cart-item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.cart-item-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #2e7d32;
}

.cart-item-image {
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1/1;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.cart-item-card:hover .cart-item-image img {
    transform: scale(1.05);
}

.cart-item-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.cart-item-price {
    font-weight: 600;
    color: #2e7d32;
    font-size: 1.1rem;
}

.cart-item-total {
    font-weight: 700;
    color: #2e7d32;
    font-size: 1.2rem;
}

/* Quantity Control */
.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quantity-btn:hover {
    background: #e9ecef;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 0.3rem;
    font-weight: 500;
}

/* Remove Button */
.cart-item-remove {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: transparent;
    border: none;
    color: #dc3545;
    font-size: 1.2rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    cursor: pointer;
}

.cart-item-card:hover .cart-item-remove {
    opacity: 1;
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
    padding: 1rem 1.5rem;
}

.order-summary-header h5 {
    margin: 0;
    font-weight: 600;
}

.order-summary-body {
    padding: 1.5rem;
}

.order-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px dashed #dee2e6;
}

.order-summary-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.order-summary-divider {
    height: 1px;
    background: #dee2e6;
    margin: 1rem 0;
}

.order-summary-footer {
    padding: 1.5rem;
    padding-top: 0;
}

/* Responsive */
@media (max-width: 991.98px) {
    .cart-item-card {
        padding: 1rem;
    }
    
    .cart-item-title {
        font-size: 1rem;
    }
    
    .quantity-control {
        justify-content: center;
    }
}

@media (max-width: 767.98px) {
    .cart-item-image {
        margin-bottom: 1rem;
    }
    
    .cart-item-remove {
        opacity: 1;
        position: relative;
        top: auto;
        right: auto;
        display: inline-block;
        margin-top: 1rem;
    }
}

/* [Rest of your existing CSS remains the same] */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                if (value > 1) {
                    input.value = value - 1;
                }
            } else {
                if (value < parseInt(input.max)) {
                    input.value = value + 1;
                }
            }
            
            // Trigger change event
            const event = new Event('change');
            input.dispatchEvent(event);
        });
    });
    
    // Toggle bouquet flower details
    document.querySelectorAll('.view-bouquet-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const details = this.nextElementSibling;
            if (details.style.display === 'none') {
                details.style.display = 'block';
                this.textContent = 'Hide Flower Details';
            } else {
                details.style.display = 'none';
                this.textContent = 'View Flower Details';
            }
        });
    });
    
    // Remove item with confirmation
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                window.location.href = `cart?remove=${itemId}`;
            }
        });
    });
    
    // Empty cart button
    // Empty cart button
    document.getElementById('empty-cart-btn')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to empty your cart?')) {
            fetch('api/empty_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin' // Important for session cookies
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update UI to show empty cart
                    document.querySelector('.cart-items-container').innerHTML = `
                        <div class="empty-cart-container text-center py-5">
                            <div class="empty-cart-icon mb-4">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <h3 class="mb-3"><?php echo __('cart_empty'); ?></h3>
                            <p class="text-muted mb-4"><?php echo __('cart_empty_message'); ?></p>
                            <a href="shop" class="btn btn-success btn-lg">
                                <i class="fas fa-arrow-left me-2"></i><?php echo __('continue_shopping'); ?>
                            </a>
                        </div>
                    `;
                    
                    // Update cart count in header
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(el => {
                        el.textContent = '0';
                    });
                    
                    // Update summary
                    document.querySelector('.order-summary-row.total span:last-child').textContent = '<?php echo CURRENCY; ?>0.00';
                } else {
                    alert('Failed to empty cart: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to empty cart');
            });
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>