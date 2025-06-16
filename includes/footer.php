    </main>
<footer class="footer bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <!-- About Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand d-flex align-items-center mb-1">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" height="80">
                </div>
                <p class="text">
                    <?php echo $current_lang === 'ar' ? 
                    'وجهتك الأولى لكل ما يتعلق بالنباتات. نقدم أجود الأنواع مع ضمان الجودة والاستدامة.' : 
                    'Your premier destination for all things plants. We offer premium varieties with quality assurance and sustainability.' ?>
                </p>
                <div class="social-icons mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-uppercase mb-4"><?php echo $current_lang === 'ar' ? 'روابط سريعة' : 'Quick Links' ?></h5>
                <ul style="color:#fff;" class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/index.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'الرئيسية' : 'Home' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/shop.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'المتجر' : 'Shop' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/gifts.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'الهدايا' : 'Gifts' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/about.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'من نحن' : 'About Us' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/contact.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'اتصل بنا' : 'Contact' ?></a></li>
                </ul>
            </div>

            <!-- Customer Service Column -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-uppercase mb-4"><?php echo $current_lang === 'ar' ? 'خدمة العملاء' : 'Customer Service' ?></h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/faq.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'الأسئلة الشائعة' : 'FAQ' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/shipping.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'الشحن والتوصيل' : 'Shipping & Delivery' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/TermsandConditions.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'سياسة الإرجاع' : 'Return Policy' ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/privacy.php" class="text text-decoration-none hover-primary"><?php echo $current_lang === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' ?></a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="col-lg-4 col-md-6">
                <h5 class="text-uppercase mb-4"><?php echo $current_lang === 'ar' ? 'تواصل معنا' : 'Contact Us' ?></h5>
                <ul class="list-unstyled text">
                    <li class="mb-3 d-flex">
                        <i class="fas fa-phone me-3 mt-1"></i>
                        <span>+20 01011960681</span>
                    </li>
                    <li class="mb-3 d-flex">
                        <i class="fas fa-envelope me-3 mt-1"></i>
                        <span>support@bta3ward.shop</span>
                    </li>
                </ul>
                
                <!-- Newsletter Subscription -->
                <div class="newsletter mt-4">
                    <h6 class="text-uppercase mb-3"><?php echo $current_lang === 'ar' ? 'النشرة البريدية' : 'Newsletter' ?></h6>
                    <form class="d-flex">
                        <input type="email" class="form-control rounded-0" placeholder="<?php echo $current_lang === 'ar' ? 'بريدك الإلكتروني' : 'Your email' ?>" required>
                        <button class="btn btn-primary rounded-0 px-3" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text">&copy; <?php echo date('Y'); ?> Plant Store. <?php echo $current_lang === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved' ?>.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="payment-methods">
                    <i class="fab fa-cc-visa fa-lg me-2"></i>
                    <i class="fab fa-cc-mastercard fa-lg me-2"></i>
                    <i class="fab fa-cc-paypal fa-lg me-2"></i>
                    <i class="fab fa-cc-apple-pay fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// Define BASE_URL before loading user.js
const BASE_URL = '<?php echo BASE_URL; ?>';
</script>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/user.js"></script>
    <style>
    /* Footer Specific Styles */
    .footer {
        background: linear-gradient(135deg, #2d3436 0%, #1e272e 100%);
    }
    .text{
        color: #fff;
    }
    
    .footer-brand img {
        transition: transform 0.3s ease;
    }
    
    .footer-brand:hover img {
        transform: rotate(-5deg);
    }
    
    .hover-primary {
        transition: color 0.3s ease;
    }
    
    .hover-primary:hover {
        color: var(--primary-color) !important;
    }
    
    .social-icons a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    
    .social-icons a:hover {
        background-color: var(--primary-color);
        transform: translateY(-3px);
    }
    
    .newsletter input {
        background-color: rgba(255,255,255,0.1);
        border: none;
        color: white;
    }
    
    .newsletter input::placeholder {
        color: rgba(255,255,255,0.6);
    }
    
    .newsletter .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .newsletter .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }
    
    .payment-methods i {
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }
    
    .payment-methods i:hover {
        opacity: 1;
    }
    
    @media (max-width: 767.98px) {
        .footer .col-md-6 {
            margin-bottom: 1.5rem;
        }
        
        .text-md-start, .text-md-end {
            text-align: center !important;
        }
    }
</style>
</body>
</html>