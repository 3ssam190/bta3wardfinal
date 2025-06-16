<?php // NO WHITESPACE BEFORE THIS LINE - CHECK WITH HEX EDITOR IF NEEDED
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
require_once __DIR__ . '/../config/database.php';


// Start session securely - must be first operation
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Security headers - must come before any output
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');

// Regenerate session ID
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (!headers_sent()) {
        header('Location: login.php');
        exit;
    } else {
        die('Session validation failed - headers already sent');
    }
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}




$pdo = Database::connect(); // Get the PDO instance from your singleton

$stmt = $pdo->prepare("SELECT first_name, admin_photo FROM Admins WHERE email = ?");
$stmt->execute([$_SESSION['admin_email']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$profilePic = '../../assets/images/profile_photos/default-profile.png';

if ($admin && !empty($admin['admin_photo'])) {
    $profilePic = $admin['admin_photo'];
}

$adminName = $admin ? htmlspecialchars($admin['first_name']) : 'Admin';

// Only after all headers are sent, start HTML output
?>
<!DOCTYPE html>
<html lang="en" class="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?> | Bta3Ward Admin</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    
    
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    AOS.init({
      duration: 800,
      once: true
    });
  });
</script>
    
    <!-- AOS Animation -->
    
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Chart.js -->
    <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
 :root {
            /* Plant-inspired Color System */
            --primary-color: #4a8f29; /* Deep green */
            --primary-light: #6bbd4b; /* Lighter green */
            --primary-dark: #2d5e1a; /* Darker green */
        /*    --primary-50: #f0f7eb;*/
        /*    --primary-100: #d9eecd;*/
            
            --secondary-color: #8f6a29; /* Earthy brown */
        /*    --secondary-light: #bd8f4b;*/
        /*    --secondary-dark: #5e421a;*/
            
            --accent-color: #d4a017; /* Golden yellow */
        /*    --accent-light: #ffc845;*/
        /*    --accent-dark: #a67c0e;*/
            
        /*    --success-color: #28a745;*/
        /*    --info-color: #17a2b8;*/
        /*    --warning-color: #ffc107;*/
        /*    --danger-color: #dc3545;*/
            
            /* Neutral colors */
        /*    --dark-color: #212529;*/
        /*    --light-color: #f8f9fa;*/
        /*    --gray-100: #f8f9fa;*/
        /*    --gray-200: #e9ecef;*/
        /*    --gray-300: #dee2e6;*/
        /*    --gray-400: #ced4da;*/
        /*    --gray-500: #adb5bd;*/
        /*    --gray-600: #6c757d;*/
        /*    --gray-700: #495057;*/
        /*    --gray-800: #343a40;*/
        /*    --gray-900: #212529;*/
            
             Layout 
            --sidebar-width: 280px;
            --topbar-height: 70px;
            --glass-blur: 12px;
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
  /* Top Navigation Bar */
.top-navbar {
    background-color: var(--primary-color);
    box-shadow: var(--box-shadow);
    padding: 0.5rem 1rem;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    transition: all var(--transition-speed) ease;
}

.top-navbar .navbar-brand {
    display: flex;
    align-items: center;
    color: white;
    font-weight: 600;
}

.top-navbar .navbar-brand img {
    height: 40px;
    margin-right: 10px;
}

.top-navbar .brand-text {
    font-size: 1.25rem;
}

.top-navbar .nav-link {
    padding: 0.75rem 1rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
    border-radius: var(--border-radius);
    margin: 0 2px;
    transition: all var(--transition-speed) ease;
    display: flex;
    align-items: center;
}

.top-navbar .nav-link i {
    font-size: 0.9rem;
}

.top-navbar .nav-link:hover {
    color: white;
    background-color: var(--primary-dark);
}

.top-navbar .nav-link.active {
    color: white;
    background-color: var(--primary-dark);
    font-weight: 600;
}

.top-navbar .navbar-toggler {
    border: none;
    padding: 0.5rem;
    color: white;
}

.top-navbar .topbar-search {
    position: relative;
    margin-right: 1rem;
}

.top-navbar .topbar-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
}

.top-navbar .topbar-search input {
    padding: 0.375rem 0.75rem 0.375rem 35px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    height: 35px;
    width: 200px;
    transition: all var(--transition-speed) ease;
}

.top-navbar .topbar-search input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.top-navbar .topbar-search input:focus {
    outline: none;
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

/* Dropdowns */
.top-navbar .dropdown-menu {
    border: none;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    padding: 0.5rem 0;
}

.top-navbar .dropdown-item {
    padding: 0.5rem 1.5rem;
    color: var(--gray-700);
}

.top-navbar .dropdown-item:hover {
    background-color: var(--primary-50);
    color: var(--primary-dark);
}

.top-navbar .dropdown-toggle {
    color: white;
    background: none;
    border: none;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
}

.top-navbar .dropdown-toggle::after {
    margin-left: 0.5rem;
}

.top-navbar .badge {
    background-color: var(--accent-color);
    color: var(--dark-color);
    font-size: 0.6rem;
    margin-left: 0.25rem;
}

/* Adjust main content for fixed navbar */
.main-content {
    margin-top: var(--topbar-height);
    padding: 20px;
    min-height: calc(100vh - var(--topbar-height));
}

/* Theme toggle */
.theme-toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.theme-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.theme-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--gray-300);
    transition: .4s;
    border-radius: 24px;
}

.theme-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .theme-slider {
    background-color: var(--primary-color);
}

input:checked + .theme-slider:before {
    transform: translateX(26px);
}

/* Dark mode styles */
.dark-mode .top-navbar {
    background-color: var(--gray-900);
    border-bottom: 1px solid var(--gray-800);
}

.dark-mode .top-navbar .nav-link {
    color: var(--gray-300);
}

.dark-mode .top-navbar .nav-link:hover,
.dark-mode .top-navbar .nav-link.active {
    color: white;
    background-color: var(--gray-800);
}

.dark-mode .top-navbar .topbar-search input {
    background-color: var(--gray-800);
    border-color: var(--gray-700);
    color: var(--gray-200);
}

.dark-mode .top-navbar .topbar-search input::placeholder {
    color: var(--gray-500);
}

.dark-mode .dropdown-menu {
    background-color: var(--gray-800);
    border: 1px solid var(--gray-700);
}

.dark-mode .dropdown-item {
    color: var(--gray-300);
}

.dark-mode .dropdown-item:hover {
    background-color: var(--gray-700);
    color: white;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .top-navbar .topbar-search {
        margin: 10px 0;
        width: 100%;
    }
    
    .top-navbar .topbar-search input {
        width: 100%;
    }
    
    .navbar-collapse {
        padding: 15px 0;
    }
    
    .top-navbar .nav-link {
        padding: 0.5rem 1rem;
        margin: 2px 0;
    }
}
</style>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navbar navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- Brand/logo -->
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/images/admin-logo.png" alt="PlantsStore Logo" height="40">
                <span class="brand-text">Bta3Ward Admin Panel</span>
            </a>
            
            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Main navigation items -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>" href="products.php">
                            <i class="fas fa-leaf me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>" href="orders.php">
                            <i class="fas fa-shopping-cart me-1"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'verify_vodafone_payments.php' ? 'active' : '' ?>" href="verify_vodafone_payments.php">
                            <i class="fas fa-mobile-alt me-1"></i> Vodafone
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>" href="users.php">
                            <i class="fas fa-users me-1"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                            <i class="fas fa-tags me-1"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'flowers_covers.php' ? 'active' : '' ?>" href="flowers_covers.php">
                            <i class="fas fa-spa"></i> Flowers & Covers
                        </a>
                    </li>
                </ul>
                
                
                
                <!-- Right-aligned items -->
                <div class="d-flex align-items-center">
  <div class="dropdown">
    <button class="btn btn-sm dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="<?= htmlspecialchars($profilePic) ?>" alt="Admin Profile" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover;">
      <span><?= htmlspecialchars($adminName) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
      <li><a class="dropdown-item" href="../pages/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
    </ul>
  </div>
</div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <script>
             window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        </script>