<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Shop';

$current_lang = $_SESSION['lang'] ?? 'en';

// Get all products with primary images and categories
$products = getAllProducts($conn, $current_lang);
$categories = getCategories($conn, $current_lang);
?>

<!-- Shop Main Section -->
<section class="shop-section py-5">
    <div class="container">
        <!-- Hero Header -->
        <div class="shop-header text-center mb-5">
            <h1 class="display-5 fw-bold mb-3"><?php echo $current_lang === 'ar' ? 'مجموعة النباتات' : 'Plant Collection'; ?></h1>
            <p class="lead text-muted"><?php echo $current_lang === 'ar' ? 'اكتشف مجموعتنا المختارة من النباتات الداخلية والخارجية' : 'Discover our curated selection of indoor and outdoor plants'; ?></p>
        </div>

        <!-- Filter Controls -->
        <div class="shop-controls mb-5">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-md-6">
                    <div class="search-box position-relative">
                        <input type="text" id="searchInput" class="form-control form-control-lg ps-5" 
                               placeholder="<?php echo $current_lang === 'ar' ? 'ابحث عن النباتات...' : 'Search plants...'; ?>">
                        <i class="fas fa-search position-absolute start-0 top-50 translate-middle-y ms-3"></i>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="col-md-4">
                    <select id="categoryFilter" class="form-select form-select-lg">
                        <option value=""><?php echo $current_lang === 'ar' ? 'كل الفئات' : 'All Categories'; ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select id="sortOptions" class="form-select form-select-lg">
                        <option value="default"><?php echo $current_lang === 'ar' ? 'ترتيب حسب' : 'Sort By'; ?></option>
                        <option value="price-low"><?php echo $current_lang === 'ar' ? 'السعر: من الأقل للأعلى' : 'Price: Low to High'; ?></option>
                        <option value="price-high"><?php echo $current_lang === 'ar' ? 'السعر: من الأعلى للأقل' : 'Price: High to Low'; ?></option>
                        <option value="name-asc"><?php echo $current_lang === 'ar' ? 'الاسم: أ-ي' : 'Name: A-Z'; ?></option>
                        <option value="name-desc"><?php echo $current_lang === 'ar' ? 'الاسم: ي-أ' : 'Name: Z-A'; ?></option>
                    </select>
                </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4" id="productsGrid">
            <?php foreach ($products as $product): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-6 plant-item" 
                     data-category="<?php echo htmlspecialchars($product['category_id']); ?>"
                     data-price="<?php echo htmlspecialchars($product['price']); ?>"
                     data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>">
                    
                    <div class="product-card card h-100 border-0 shadow-sm overflow-hidden">
                        <!-- Product Badge -->
                        <?php if ($product['is_featured']): ?>
                            <div class="product-badge">
                        <?php echo $current_lang === 'ar' ? 'مميز' : 'Featured'; ?>
                    </div>
                        <?php endif; ?>
                        
                        <!-- Product Image -->
                        <div class="product-image-container">
                            <a href="product?id=<?php echo $product['product_id']; ?>">
                                <img src="<?php echo BASE_URL; ?>/admin/assets/images/products/<?php echo htmlspecialchars($product['primary_image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="card-img-top"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/assets/images/placeholder-plant.jpg'">
                            </a>
                        </div>
                        
                        <!-- Product Body -->
                        <div class="card-body position-relative">
                            <!-- Category -->
                            <div class="product-category mb-1">
                                <span class="badge bg-light text-dark">
                                    <?php echo htmlspecialchars($product['category_name'] ?? ($current_lang === 'ar' ? 'عام' : 'General')); ?>
                                </span>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="product-title h5 mb-2">
                                <a href="product?id=<?php echo $product['product_id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <!-- Price -->
                            <div class="product-price mb-3">
                                <span class="text-success fw-bold">
                            <?php echo $current_lang === 'ar' ? 'ج.م ' : 'L.E '; ?>
                            <?php echo number_format($product['price'], 2); ?>
                        </span>
                                <?php if ($product['stock_quantity'] <= 0): ?>
                            <span class="badge bg-danger <?php echo $current_lang === 'ar' ? 'me-2' : 'ms-2'; ?>">
                                <?php echo $current_lang === 'ar' ? 'نفذت الكمية' : 'Sold Out'; ?>
                            </span>
                        <?php endif; ?>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="product-actions d-flex justify-content-between">
                                <a href="product?id=<?php echo $product['product_id']; ?>" class="btn btn-outline-success btn-sm flex-grow-1">
                            <?php echo $current_lang === 'ar' ? 'عرض التفاصيل' : 'View Details'; ?>
                        </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <img src="<?php echo BASE_URL; ?>/assets/images/no-results.svg" alt="No results" class="img-fluid mb-4" style="max-width: 300px;">
            <h3 class="h4"><?php echo $current_lang === 'ar' ? 'لم يتم العثور على نباتات' : 'No plants found'; ?></h3>
            <p class="text-muted"><?php echo $current_lang === 'ar' ? 'حاول تعديل معايير البحث أو التصفية' : 'Try adjusting your search or filter criteria'; ?></p>
            <button id="resetFilters" class="btn btn-success mt-3"><?php echo $current_lang === 'ar' ? 'إعادة تعيين جميع الفلاتر' : 'Reset All Filters'; ?></button>
        </div>
    </div>
</section>

<style>
/* Shop Section Styles */
.shop-section {
    background-color: #f8f9fa;
}

.product-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.product-image-container {
    height: 220px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f1f8e9;
}

.card-img-top {
    width: 100%;
    height: 200px; /* Fixed height or use aspect-ratio */
    object-fit: cover; /* Keeps aspect ratio without cropping */
    object-position: center;
    background-color: #f8f9fa; /* Fallback background */
}

.product-image-container img {
    max-height: 100%;
    width: auto;
    object-fit: contain;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image-container img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ffc107;
    color: #000;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.product-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 48px;
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .product-image-container {
        height: 180px;
    }
    
    .col-6 {
        padding-left: 5px;
        padding-right: 5px;
    }
}

@media (max-width: 575.98px) {
    .product-image-container {
        height: 150px;
    }
}

[dir="rtl"] .product-badge {
    right: auto;
    left: 15px;
}

[dir="rtl"] .search-box i {
    right: 0;
    left: auto;
    padding-right: 1rem;
}

[dir="rtl"] .ms-2 {
    margin-right: 0.5rem !important;
    margin-left: 0 !important;
}

[dir="rtl"] .me-2 {
    margin-left: 0.5rem !important;
    margin-right: 0 !important;
}

[dir="rtl"] .ps-5 {
    padding-right: 3rem !important;
    padding-left: 1rem !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter products based on search and category
    function filterProducts() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const categoryValue = document.getElementById('categoryFilter').value;
        const sortValue = document.getElementById('sortOptions').value;
        
        const products = document.querySelectorAll('.plant-item');
        let visibleCount = 0;
        
        products.forEach(product => {
            const name = product.dataset.name;
            const category = product.dataset.category;
            const price = parseFloat(product.dataset.price);
            
            // Check search and category filters
            const matchesSearch = name.includes(searchValue);
            const matchesCategory = !categoryValue || category === categoryValue;
            
            if (matchesSearch && matchesCategory) {
                product.style.display = 'block';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        document.getElementById('noResults').classList.toggle('d-none', visibleCount > 0);
        
        // Sort products
        sortProducts(sortValue);
    }
    
    // Sort products based on selected option
    function sortProducts(sortValue) {
        const container = document.getElementById('productsGrid');
        const products = Array.from(document.querySelectorAll('.plant-item'));
        
        products.sort((a, b) => {
            switch(sortValue) {
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'name-asc':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'name-desc':
                    return b.dataset.name.localeCompare(a.dataset.name);
                default:
                    return 0;
            }
        });
        
        // Re-append sorted products
        products.forEach(product => container.appendChild(product));
    }
    
    // Event listeners
    document.getElementById('searchInput').addEventListener('input', filterProducts);
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('sortOptions').addEventListener('change', function() {
        filterProducts();
    });
    
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('sortOptions').value = 'default';
        filterProducts();
    });
    
    // Quick add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            // Add your cart logic here
            alert('Product ' + productId + ' added to cart!');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>