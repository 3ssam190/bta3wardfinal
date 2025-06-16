<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Home';

// Get featured products with primary images
try {
    $query = "SELECT 
                p.*, 
                pi.image_url,
                t_name.translated_text as arabic_name,
                t_desc.translated_text as arabic_description
              FROM Products p
              LEFT JOIN ProductImages pi ON p.product_id = pi.product_id AND pi.is_primary = 1
              LEFT JOIN Translations t_name ON (
                  t_name.entity_type = 'product' AND 
                  t_name.entity_id = p.product_id AND 
                  t_name.field_name = 'name' AND 
                  t_name.language_code = 'ar'
              )
              LEFT JOIN Translations t_desc ON (
                  t_desc.entity_type = 'product' AND 
                  t_desc.entity_id = p.product_id AND 
                  t_desc.field_name = 'description' AND 
                  t_desc.language_code = 'ar'
              )
              WHERE p.is_featured = 1
              LIMIT 8";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featuredProducts = [];
    error_log("Database error: " . $e->getMessage());
}

// Get categories for filter dropdown
try {
    $categoriesQuery = "SELECT * FROM Categories";
    $categoriesStmt = $conn->prepare($categoriesQuery);
    $categoriesStmt->execute();
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!-- Modern Full-Width Hero Section -->
<!-- Modern Full-Width Hero Section -->
<section class="containerh full-width-hero">
    <div class="hero-background">
        <div class="hero-slides">
            <div class="slide active" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/hero1.jpg');"></div>
            <div class="slide" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/hero2.avif');"></div>
            <div class="slide" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/hero3.jpg');"></div>
        </div>
        <div class="hero-overlay"></div>
    </div>
    
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100"><?php echo __('welcome'); ?></h1>
            <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200"><?php echo __('discover'); ?></p>
            <div class="hero-cta" data-aos="fade-up" data-aos-delay="300">
                <a href="/shop" class="cta-primary">
                    <?php echo __('shop_now'); ?>
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <a href="#featured" class="cta-secondary">
                    <?php echo __('featured_plants'); ?>
                    <i class="fas fa-leaf ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll-indicator" data-aos="fade-up" data-aos-delay="400">
        <span><?php echo $current_lang === 'ar' ? 'انتقل لأسفل' : 'Scroll Down'; ?></span>
        <div class="scroll-line"></div>
    </div>
    
    <div class="hero-slide-controls">
        <button class="slide-prev"><i class="fas fa-chevron-left"></i></button>
        <div class="slide-indicators"></div>
        <button class="slide-next"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- Featured Plants Section -->
<section id="featured" class="featured-plants">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title" data-aos="fade-up"><?php echo __('featured_plants'); ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo __('our_popular'); ?></p>
            
            <!-- Category Filter -->
            <?php if (!empty($categories)): ?>
            <div class="category-filter" data-aos="fade-up" data-aos-delay="150">
                <select id="categoryFilter" class="filter-select">
                    <option value=""><?php echo __('all_categories'); ?></option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($featuredProducts)): ?>
        <div class="plant-grid">
    <?php foreach ($featuredProducts as $product): ?>
    <div class="plant-card" data-category="<?php echo htmlspecialchars($product['category_id']); ?>" data-aos="fade-up">
        <?php if ($product['is_featured']): ?>
        <div class="plant-badge"><?php echo $current_lang === 'ar' ? 'مميز' : 'Featured'; ?></div>
        <?php endif; ?>
        
        <div class="plant-image-container">
            <?php if (!empty($product['image_url'])): ?>
            <img src="./admin/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                 alt="<?php echo $current_lang === 'ar' && !empty($product['arabic_name']) ? 
                     htmlspecialchars($product['arabic_name']) : 
                     htmlspecialchars($product['name']); ?>" 
                 loading="lazy"
                 onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/assets/images/placeholder-plant.jpg'">
            <?php else: ?>
            <div class="no-image-placeholder">
                <i class="fas fa-leaf"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="plant-details">
            <div class="plant-info">
                <h3><?php echo $current_lang === 'ar' && !empty($product['arabic_name']) ? 
                    htmlspecialchars($product['arabic_name']) : 
                    htmlspecialchars($product['name']); ?></h3>
                <?php if (!empty($product['category_name'])): ?>
                <div class="plant-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($product['description']) || !empty($product['arabic_description'])): ?>
            <p class="plant-description">
                <?php echo $current_lang === 'ar' && !empty($product['arabic_description']) ? 
                    htmlspecialchars($product['arabic_description']) : 
                    htmlspecialchars($product['description']); ?>
            </p>
            <?php endif; ?>
            
            <div class="plant-footer">
                <div class="plant-price-stock">
                    <div class="plant-price">L.E<?php echo number_format($product['price'], 2); ?></div>
                    <div class="plant-stock <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $product['stock_quantity'] > 0 ? 
                            ($current_lang === 'ar' ? 'متوفر' : 'In Stock') : 
                            ($current_lang === 'ar' ? 'غير متوفر' : 'Out of Stock'); ?>
                    </div>
                </div>
                <a href="/product?id=<?php echo $product['product_id']; ?>" 
                   class="add-to-cart">
                    <span><?php echo __('view_details'); ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
        <?php else: ?>
        <div class="no-products" data-aos="fade-up">
            <p><?php echo $current_lang === 'ar' ? 'لا توجد منتجات مميزة حالياً. يرجى التحقق لاحقاً.' : 'No featured products found. Please check back later.'; ?></p>
            <a href="/shop" class="btn btn-success"><?php echo $current_lang === 'ar' ? 'تصفح جميع النباتات' : 'Browse All Plants'; ?></a>
        </div>
        <?php endif; ?>
        
        <div class="view-all-container" data-aos="fade-up">
            <a href="/shop" class="view-all-btn">
                <?php echo $current_lang === 'ar' ? 'عرض جميع النباتات' : 'View All Plants'; ?>
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* ========== Modern Full-Width Hero Section ========== */

body, html {
    margin: 0;
    padding: 0;
    width: 100%;
    overflow-x: hidden;
}

/* Remove container padding/margin */
.containerh {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
}


.full-width-hero {
    position: relative;
    width: 100vw;
    height: 100vh;
    min-height: 600px;
    max-height: 1200px;
    overflow: hidden;
    margin: 0;
    padding: 0;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-slides {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slides .slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
    z-index: 1;
}

.hero-slides .slide.active {
    opacity: 1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 3;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    margin: 0 auto;
    padding: 0 20px;
    max-width: 1200px;
}

.hero-text {
    max-width: 800px;
    padding: 0 20px;
    transform: translateY(-50px);
}

.hero-title {
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    animation: textGlow 3s infinite alternate;
}

@keyframes textGlow {
    0% { text-shadow: 0 0 10px rgba(255,255,255,0.3); }
    100% { text-shadow: 0 0 20px rgba(255,255,255,0.6); }
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    margin-bottom: 2.5rem;
    opacity: 0.9;
    line-height: 1.6;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-cta {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-primary {
    background-color: #fff;
    color: #2e7d32;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
}

.cta-primary::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(255,255,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cta-primary:hover {
    background-color: #f1f8e9;
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    border-color: #2e7d32;
}

.cta-primary:hover::after {
    opacity: 1;
}

.cta-secondary {
    color: white;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    border: 2px solid rgba(255,255,255,0.5);
    background-color: rgba(255,255,255,0.1);
    backdrop-filter: blur(5px);
    font-size: 1.1rem;
    display: inline-flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.cta-secondary::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cta-secondary:hover {
    background-color: rgba(255,255,255,0.2);
    border-color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.cta-secondary:hover::after {
    opacity: 1;
}

.hero-scroll-indicator {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 0.7;
    cursor: pointer;
    font-size: 0.9rem;
    animation: bounce 2s infinite;
}

.scroll-line {
    width: 1px;
    height: 50px;
    background: linear-gradient(to bottom, white, transparent);
    margin-top: 8px;
}

.hero-slide-controls {
    position: absolute;
    bottom: 40px;
    right: 40px;
    z-index: 4;
    display: flex;
    align-items: center;
    gap: 15px;
}

.slide-prev, .slide-next {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.slide-prev:hover, .slide-next:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.slide-indicators {
    display: flex;
    gap: 8px;
}

.slide-indicators .indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    cursor: pointer;
    transition: all 0.3s ease;
}

.slide-indicators .indicator.active {
    background: white;
    transform: scale(1.2);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .full-width-hero {
        min-height: 500px;
        height: 80vh;
    }
    
    .hero-title {
        font-size: 2.2rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .hero-cta {
        gap: 1rem;
    }
    
    .cta-primary, .cta-secondary {
        padding: 0.9rem 2rem;
        font-size: 1rem;
    }
    
    .hero-scroll-indicator {
        bottom: 30px;
    }
    
    .hero-slide-controls {
        bottom: 30px;
        right: 20px;
    }
}

@media (max-width: 576px) {
    .full-width-hero {
        min-height: 450px;
        height: 85vh;
    }
    
    .hero-title {
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-cta {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .cta-primary, .cta-secondary {
        width: 100%;
        max-width: 250px;
        margin: 0 auto;
    }
    
    .hero-scroll-indicator {
        bottom: 20px;
        font-size: 0.8rem;
    }
    
    .scroll-line {
        height: 40px;
    }
    
    .hero-slide-controls {
        display: none;
    }
}

/* Rest of your existing CSS remains the same */
</style>

<script>
// Initialize AOS animation library
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});

// Hero Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slides .slide');
    const indicatorsContainer = document.querySelector('.slide-indicators');
    const prevBtn = document.querySelector('.slide-prev');
    const nextBtn = document.querySelector('.slide-next');
    let currentSlide = 0;
    
    // Create indicators
    slides.forEach((slide, index) => {
        const indicator = document.createElement('div');
        indicator.classList.add('indicator');
        if (index === 0) indicator.classList.add('active');
        indicator.addEventListener('click', () => goToSlide(index));
        indicatorsContainer.appendChild(indicator);
    });
    
    // Auto slide change
    let slideInterval = setInterval(nextSlide, 5000);
    
    function nextSlide() {
        goToSlide((currentSlide + 1) % slides.length);
    }
    
    function prevSlide() {
        goToSlide((currentSlide - 1 + slides.length) % slides.length);
    }
    
    function goToSlide(index) {
        slides[currentSlide].classList.remove('active');
        document.querySelectorAll('.slide-indicators .indicator')[currentSlide].classList.remove('active');
        
        currentSlide = index;
        
        slides[currentSlide].classList.add('active');
        document.querySelectorAll('.slide-indicators .indicator')[currentSlide].classList.add('active');
        
        // Reset timer
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    // Button controls
    nextBtn.addEventListener('click', () => {
        nextSlide();
    });
    
    prevBtn.addEventListener('click', () => {
        prevSlide();
    });
    
    // Pause on hover
    const hero = document.querySelector('.full-width-hero');
    hero.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    hero.addEventListener('mouseleave', () => {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    });
});

// Category filter functionality
document.getElementById('categoryFilter').addEventListener('change', function() {
    const selectedCategory = this.value;
    const plantCards = document.querySelectorAll('.plant-card');
    
    plantCards.forEach(card => {
        if (selectedCategory === '' || card.dataset.category === selectedCategory) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>