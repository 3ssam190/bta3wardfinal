<?php
require_once __DIR__ . '/../config.php';
$current_lang = $_SESSION['lang'] ?? 'en';
// Validate product ID
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$productId) {
    header("Location: shop.php");
    exit();
}

// Get product with category info
$product = getProductById($productId, $current_lang);


if (!$product) {
    header("Location: shop.php");
    exit();
}

// Get all product images
$images = getProductImages($productId);
$primaryImage = array_filter($images, fn($img) => $img['is_primary']);
$secondaryImages = array_filter($images, fn($img) => !$img['is_primary']);

$pageTitle = htmlspecialchars($product['name']);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="product-page">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/pages/shop.php">Shop</a></li>
                <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/pages/shop.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Product Gallery -->
            <div class="col-lg-7">
                <div class="product-gallery">
                    <!-- Main Image -->
                    <div class="main-image mb-3">
                        <?php if (!empty($primaryImage) || !empty($images)): ?>
                        <?php $mainImage = !empty($primaryImage) ? reset($primaryImage) : reset($images); ?>
                        <img src="<?php echo BASE_URL; ?>/admin/assets/images/products/<?php echo htmlspecialchars($mainImage['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="img-fluid rounded-3"
                             id="mainProductImage"
                             loading="lazy"
                             onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/assets/images/placeholder-plant.jpg'">
                        <?php else: ?>
                        <div class="no-image-placeholder bg-light d-flex align-items-center justify-content-center rounded-3" style="height: 500px;">
                            <i class="fas fa-leaf fa-5x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnail-gallery">
                        <div class="row g-2">
                            <?php foreach ($images as $index => $image): ?>
                            <div class="col-3">
                                <img src="<?php echo BASE_URL; ?>/admin/assets/images/products/<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     alt="Product thumbnail <?php echo $index + 1; ?>"
                                     class="img-fluid rounded thumbnail <?php echo $image['is_primary'] ? 'active' : ''; ?>"
                                     loading="lazy"
                                     onclick="changeMainImage(this, '<?php echo BASE_URL; ?>/admin/assets/images/products/<?php echo htmlspecialchars($image['image_url']); ?>')"
                                     onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/assets/images/placeholder-plant.jpg'">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-5">
                <div class="product-details p-4 p-lg-0">
                    <!-- Badges -->
                    <div class="badges mb-3">
                        <?php if ($product['is_featured']): ?>
                        <span class="badge bg-warning text-dark me-2"><?php echo __('featured'); ?></span>
                        <?php endif; ?>
                        <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </div>

                    <h1 class="product-title mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <?php if (!empty($product['category_name'])): ?>
                    <p class="text-muted mb-4">
                        <i class="fas fa-tag me-2"></i>
                        Category: <a href="<?php echo BASE_URL; ?>/pages/shop.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category_name']); ?></a>
                    </p>
                    <?php endif; ?>

                    <div class="price-section mb-4">
                        <h3 class="text-success mb-0">L.E<?php echo number_format($product['price'], 2); ?></h3>
                        <?php if ($product['stock_quantity'] > 0): ?>
                        <small class="text-muted"><?php echo $product['stock_quantity']; ?> <?php echo __('available'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="product-description mb-4">
                        <h4 class="mb-3"><i class="fas fa-align-left me-2"></i><?php echo __('description'); ?></h4>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <?php if (!empty($product['care_instructions'])): ?>
                    <div class="care-instructions mb-4">
                        <h4 class="mb-3"><i class="fas fa-seedling me-2"></i><?php echo __('care_instructions'); ?></h4>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($product['care_instructions'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product['environment_suitability'])): ?>
                    <div class="environment mb-4">
                        <h4 class="mb-3"><i class="fas fa-home me-2"></i><?php echo __('environment'); ?></h4>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $environments = explode(',', $product['environment_suitability']); ?>
                            <?php foreach ($environments as $env): ?>
                            <span class="badge bg-info text-dark"><?php echo trim(htmlspecialchars($env)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($product['stock_quantity'] > 0): ?>
                    <form class="add-to-cart-form mt-4" data-product-id="<?php echo $product['product_id']; ?>">
                        <div class="quantity-selector mb-3">
                            <label class="form-label"><?php echo __('quantity'); ?>:</label>
                            <div class="input-group" style="max-width: 150px;">
                                <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                                <input type="number" class="form-control text-center quantity" 
                                       value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 py-3">
                            <i class="fas fa-shopping-cart me-2"></i><?php echo __('add_to_cart'); ?>
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo __('out_of_stock_message'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Product Page Styles */
.product-page {
    padding: 2rem 0;
}

.product-gallery {
    position: sticky;
    top: 20px;
}

.main-image {
    background: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 500px;
}

.main-image img {
    max-height: 100%;
    width: auto;
    object-fit: contain;
}

.thumbnail-gallery {
    margin-top: 1rem;
}

.thumbnail {
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    height: 100px;
    object-fit: cover;
}

.thumbnail:hover {
    border-color: var(--bs-success);
}

.thumbnail.active {
    border-color: var(--bs-success);
}

.product-details {
    background: white;
    border-radius: 12px;
}

.product-title {
    font-weight: 700;
    color: var(--bs-dark);
    font-size: 2rem;
}

.price-section {
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.product-description {
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.care-instructions, .environment {
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.quantity-selector .input-group {
    border-radius: 8px;
    overflow: hidden;
}

.quantity-selector .form-control {
    border-left: none;
    border-right: none;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .main-image {
        height: 400px;
    }
}

@media (max-width: 768px) {
    .main-image {
        height: 350px;
    }
    
    .product-title {
        font-size: 1.75rem;
    }
}
</style>

<script>
function changeMainImage(thumbnail, imageUrl) {
    // Update main image
    document.getElementById('mainProductImage').src = imageUrl;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Quantity selector functionality
document.querySelectorAll('.minus-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentNode.querySelector('.quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });
});




document.querySelectorAll('.plus-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentNode.querySelector('.quantity');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 0;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>