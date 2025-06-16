<?php
ob_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
    // Redirect to same page without lang parameter to avoid duplicate switching
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
$current_lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Store | <?php echo $pageTitle ?? 'Home'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #4a934a; /* Updated green tone */
            --primary-hover: #3a7a3a;
            --primary-light: rgba(74, 147, 74, 0.1);
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --text-color: #333;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
            --border-radius: 8px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
        }
        
        /* Modern Navbar */
        .navbar {
            background-color: white;
            box-shadow: var(--shadow-sm);
            padding: 0;
            transition: var(--transition);
            height: 80px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        .navbar.scrolled {
            height: 70px;
            box-shadow: var(--shadow-md);
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .navbar-brand .logo-img {
            height: 50px;
            transition: var(--transition);
        }
        
        .navbar-brand:hover .logo-img {
            transform: scale(1.05);
        }
        
        .navbar-nav {
            gap: 0.5rem;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--text-color) !important;
            padding: 0.75rem 1.25rem !important;
            border-radius: var(--border-radius);
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 1.25rem;
            right: 1.25rem;
            height: 2px;
            background-color: var(--primary-color);
            transform: scaleX(0);
            transform-origin: center;
            transition: var(--transition);
        }
        
        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleX(1);
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
        }
        
        /* Cart Icon */
        .cart-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: var(--transition);
        }
        
        .cart-icon:hover {
            background-color: var(--primary-light);
        }
        
        .cart-count {
            position: absolute;
            top: -2px;
            right: -2px;
            min-width: 20px;
            height: 20px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            font-weight: 600;
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-signin {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-signin:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .btn-signup {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(74, 147, 74, 0.25);
        }
        
        .btn-signup:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 147, 74, 0.35);
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .profile-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: 50px;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .profile-toggle:hover {
            background-color: var(--primary-light);
        }
        
        .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .profile-name {
            font-weight: 500;
            color: var(--text-color);
            margin-right: 0.5rem;
            display: none;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: var(--border-radius);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.2s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .dropdown-divider {
            border-color: rgba(0, 0, 0, 0.05);
        }
        
        /* Language Switcher */
        .language-switcher {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 1rem;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .language-switcher:hover {
            background-color: var(--primary-light);
        }
        
        .language-flag {
            width: 24px;
            height: 16px;
            object-fit: cover;
            border-radius: 2px;
            transition: var(--transition);
            opacity: 0.7;
            cursor: pointer;
        }
        
        .language-flag:hover {
            opacity: 1;
            transform: scale(1.1);
        }
        
        .language-flag.active {
            opacity: 1;
            border: 2px solid var(--primary-color);
        }
        
        /* Mobile Menu */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            font-size: 1.25rem;
            color: var(--primary-color);
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        @media (min-width: 992px) {
            .profile-name {
                display: inline;
            }
        }
        
        @media (max-width: 991.98px) {
            .navbar-collapse {
                padding: 1rem 0;
                background-color: white;
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                box-shadow: var(--shadow-md);
                margin-top: 0.5rem;
            }
            
            .navbar-nav {
                gap: 0.25rem;
            }
            .d-flex.align-items-center {
                flex-direction: row;
                padding: 0 1rem;
                gap: 0.5rem;
            }
            
            .nav-item.me-2.d-lg-none {
                margin-right: 0 !important;
            }
            
            .cart-icon {
                font-size: 1.25rem;
            }
            
            .nav-link {
                padding: 0.75rem 1.5rem !important;
            }
            
            .auth-buttons {
                flex-direction: column;
                padding: 0 1.5rem;
                gap: 0.5rem;
            }
            
            .btn-signin, .btn-signup {
                width: 100%;
                text-align: center;
            }
            
            .language-switcher {
                margin-left: 0;
                padding: 0.75rem 1.5rem;
                justify-content: flex-start;
            }
            
            .profile-dropdown {
                padding: 0.75rem 1.5rem;
            }
        }
        
        /* Animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        /* Utility Classes */
        .scale-0 { transform: scale(0); }
        .hidden { display: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="navbar-container container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" class="logo-img">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pageTitle ?? '') === 'Home' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/">
                            <i class="fas fa-home me-1 d-lg-none"></i>
                            <?php echo $current_lang === 'ar' ? 'الرئيسية' : 'Home'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pageTitle ?? '') === 'Shop' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/shop">
                            <i class="fas fa-store me-1 d-lg-none"></i>
                            <?php echo $current_lang === 'ar' ? 'المتجر' : 'Shop'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pageTitle ?? '') === 'Gifts' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/gifts">
                            <i class="fas fa-gift me-1 d-lg-none"></i>
                            <?php echo $current_lang === 'ar' ? 'الهدايا' : 'Gifts'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pageTitle ?? '') === 'About Us' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/about">
                            <i class="fas fa-store me-1 d-lg-none"></i>
                            <?php echo $current_lang === 'ar' ? 'من نحن ؟' : 'About Us'; ?>
                        </a>
                    </li>
                </ul>
                
                
                <div class="d-flex align-items-center">
                    <!-- Mobile Cart Icon - shown only on mobile -->
                    <div class="nav-item me-2 d-lg-none">
                        <a class="nav-link cart-icon p-2" href="<?php echo BASE_URL; ?>/cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-counter-mobile" class="cart-count <?php echo ($_SESSION['cart_count'] ?? 0) > 0 ? '' : 'd-none'; ?>">
                                <?php echo $_SESSION['cart_count'] ?? 0; ?>
                            </span>
                        </a>
                    </div>
                    <!-- Desktop Cart Icon -->
                    <div class="nav-item me-2 d-none d-lg-block">
                        <a class="nav-link cart-icon p-2" href="<?php echo BASE_URL; ?>/cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-counter" class="cart-count <?php echo ($_SESSION['cart_count'] ?? 0) > 0 ? '' : 'd-none'; ?>">
                                <?php echo $_SESSION['cart_count'] ?? 0; ?>
                            </span>
                        </a>
                    </div>
                    
                    <!-- Language Switcher -->
                    <div class="language-switcher">
                        <a href="?lang=en" title="English">
                            <img src="<?php echo BASE_URL; ?>/assets/images/us-flag.png" alt="English" class="language-flag <?php echo $current_lang === 'en' ? 'active' : ''; ?>">
                        </a>
                        <a href="?lang=ar" title="العربية">
                            <img src="<?php echo BASE_URL; ?>/assets/images/eg-flag.png" alt="العربية" class="language-flag <?php echo $current_lang === 'ar' ? 'active' : ''; ?>">
                        </a>
                    </div>
                    
                   <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
    <?php 
    // Determine user type
    $userType = isset($_SESSION['user_id']) ? 'user' : 'admin';
    $name = $_SESSION[$userType.'_name'] ?? 'Account';
    $defaultPhoto = ($userType === 'admin') ? DEFAULT_ADMIN : DEFAULT_PROFILE;
    
    // Get the correct photo session key based on user type
    $photoSessionKey = $userType . '_photo';
    
    // Initialize photo variable
    $photo = BASE_URL . $defaultPhoto;
    
    // Check for profile photo in session
    if (!empty($_SESSION[$photoSessionKey])) {
        $sessionPhoto = $_SESSION[$photoSessionKey];
        
        // Handle different photo path formats
        if (strpos($sessionPhoto, 'http') === 0) {
            // Already a full URL
            $photo = $sessionPhoto;
        } elseif (strpos($sessionPhoto, '/') === 0) {
            // Absolute path from root
            $photo = BASE_URL . $sessionPhoto;
        } elseif (file_exists(BASE_PATH . '/assets/images/profile_photos/' . basename($sessionPhoto))) {
            // Relative path in profile photos directory
            $photo = BASE_URL . '/assets/images/profile_photos/' . basename($sessionPhoto);
        }
    }
    
    // Add cache-busting parameter using last update time or current time
    $cacheBuster = $_SESSION['profile_photo_updated'] ?? time();
    $photo .= (strpos($photo, '?') === false ? '?' : '&') . 'v=' . $cacheBuster;
    ?>
    
    <div class="dropdown profile-dropdown">
        <button class="profile-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?php echo htmlspecialchars($photo); ?>" 
                 alt="Profile Photo" 
                 class="profile-img"
                 onerror="this.src='<?php echo htmlspecialchars(BASE_URL . $defaultPhoto); ?>?v=<?php echo $cacheBuster; ?>'">
            <span class="profile-name"><?php echo htmlspecialchars($name); ?></span>
            <i class="fas fa-chevron-down ms-1 small"></i>
        </button>
        
        <!-- Dropdown menu -->
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo $current_lang === 'ar' ? 'الملف الشخصي' : 'Profile'; ?>
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/order_history">
                                    <i class="fas fa-history"></i>
                                    <?php echo $current_lang === 'ar' ? 'سجل الطلبات' : 'Order History'; ?>
                                </a></li>
            <?php if ($userType === 'admin'): ?>
            
                <li>
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fas fa-cogs"></i>Admin Dashboard</a></li>
                    
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/signout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <?php echo $current_lang === 'ar' ? 'تسجيل الخروج' : 'Sign Out'; ?>
                                </a></li>
        </ul>
    </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?php echo BASE_URL; ?>/signin" class="btn btn-signin">
                                <?php echo $current_lang === 'ar' ? 'تسجيل الدخول' : 'Sign In'; ?>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/signup" class="btn btn-signup">
                                <?php echo $current_lang === 'ar' ? 'إنشاء حساب' : 'Sign Up'; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="container my-4">
        <!-- Page content goes here -->
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Navbar scroll effect
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Cart counter functionality
        function updateCartCount() {
            const cartCount = localStorage.getItem('cartCount') || 0;
            const cartBadges = document.querySelectorAll('.cart-count');
            
            cartBadges.forEach(badge => {
                badge.textContent = cartCount;
                badge.classList.toggle('d-none', cartCount == 0);
                
                // Add pulse animation when cart updates
                if (cartCount > 0) {
                    badge.classList.add('pulse');
                    setTimeout(() => {
                        badge.classList.remove('pulse');
                    }, 1500);
                }
            });
        }

        // Initialize cart count
        updateCartCount();
        
        // Listen for cart updates from other tabs
        window.addEventListener('storage', updateCartCount);
        
        // Chatbot toggle functionality
    });
    </script>
