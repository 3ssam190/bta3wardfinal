<?php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Initialize variables
$email = '';
$error = '';
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$showCaptcha = $loginAttempts >= 3;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    
    // Verify CAPTCHA if required
    if ($showCaptcha && (!isset($_SESSION['captcha']) || $captcha != $_SESSION['captcha'])) {
        $error = "Invalid CAPTCHA code";
        $_SESSION['login_attempts'] = $loginAttempts + 1;
    } else {
        // First check Users table
        $user = getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login - reset attempts
            unset($_SESSION['login_attempts']);
            unset($_SESSION['captcha']);
            
            // Check email verification first
            if (!$user['is_verified']) {
                $_SESSION['error'] = "Your account is not yet verified. Please check your email for the verification link or <a href='resend-verification.php?email=" . urlencode($user['email']) . "'>click here to resend it</a>.";
                header("Location: signin.php");
                exit();
            }
            
            // Regular user login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = 'user';
            
            // Handle profile photo
            if (!empty($user['profile_photo'])) {
                $_SESSION['profile_photo'] = strpos($user['profile_photo'], '/') === 0 
                    ? $user['profile_photo'] 
                    : ASSETS_DIR . $user['profile_photo'];
            } else {
                $_SESSION['profile_photo'] = DEFAULT_PROFILE;
            }
            
            // Merge guest cart with user cart
            mergeCarts($user['user_id'], session_id());
            
            // Create or get user cart
            $cart = getOrCreateCart($user['user_id'], session_id());
            $_SESSION['cart_id'] = $cart['cart_id'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header("Location: ../index.php");
            exit();
        }
        // If not found in Users table, check Admins table
        else {
            $admin = getAdminByEmail($email);
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Successful login - reset attempts
                unset($_SESSION['login_attempts']);
                unset($_SESSION['captcha']);
                
                // Admin login
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Handle admin photo
                if (!empty($admin['admin_photo'])) {
                    $_SESSION['profile_photo'] = strpos($admin['admin_photo'], '/') === 0 
                        ? $admin['admin_photo'] 
                        : ASSETS_DIR . $admin['admin_photo'];
                } else {
                    $_SESSION['profile_photo'] = DEFAULT_ADMIN;
                }
                
                // Admins don't need email verification
                session_regenerate_id(true);
                
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password";
                $_SESSION['login_attempts'] = $loginAttempts + 1;
                $showCaptcha = $_SESSION['login_attempts'] >= 3;
                
                // Generate new CAPTCHA if needed
                if ($showCaptcha) {
                    $_SESSION['captcha'] = generateCaptchaCode();
                }
                
                // Log failed login attempt
                error_log("Failed login attempt for email: $email from IP: {$_SERVER['REMOTE_ADDR']}");
            }
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/../includes/header.php';

// Generate CAPTCHA if needed
if ($showCaptcha && !isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = generateCaptchaCode();
}
?>

<!-- Animated Background -->
<div class="auth-background">
    <div class="leaf-animation leaf-1"></div>
    <div class="leaf-animation leaf-2"></div>
    <div class="leaf-animation leaf-3"></div>
    <div class="leaf-animation leaf-4"></div>
</div>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <!-- Animated Auth Card -->
                <div class="auth-card" id="authCard">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" class="auth-logo">
                    </div>
                    
                    <!-- Sign In Form -->
                    <div class="auth-form-container active" id="signinForm">
                        <h2 class="auth-title text-center mb-4">Welcome Back</h2>
                        <p class="auth-subtitle text-center mb-4">Sign in to your account to continue</p>
                        
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm" class="needs-validation" novalidate>
                            <!-- Email Field -->
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="name@example.com" required
                                       value="<?php echo htmlspecialchars($email); ?>">
                                <label for="email">Email address</label>
                                <div class="invalid-feedback">
                                    Please enter a valid email address
                                </div>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="form-floating mb-3">
                                <div class="password-field">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Password" required
                                           minlength="8">
                                    <button type="button" class="btn btn-eye" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Password must be at least 8 characters
                                </div>
                                <div class="text-end mt-2">
                                    <a href="forgot-password.php" class="text-muted small">Forgot password?</a>
                                </div>
                            </div>
                            
                            <!-- CAPTCHA (if needed) -->
                            <?php if ($showCaptcha): ?>
                            <div class="mb-3 captcha-container">
                                <div class="d-flex align-items-center mb-2">
                                    <label class="form-label mb-0">Enter CAPTCHA:</label>
                                    <div class="ms-auto captcha-code">
                                        <?php echo $_SESSION['captcha']; ?>
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="captcha" required
                                       placeholder="Type the code above">
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['error']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>
                            
                            <!-- Remember Me & Submit -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-auth" id="loginButton">
                                    <span class="btn-text">Sign In</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                            
                            
                            
                            <!-- Sign Up Link -->
                            <div class="text-center">
                                <p class="text-muted">Don't have an account? 
                                    <a href="signup.php" class="auth-link">Sign up</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Badges -->
                <div class="security-badges text-center mt-4">
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-lock me-1"></i> SSL Secure
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-shield-alt me-1"></i> Protected
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Auth Section Styles */
.auth-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
    z-index: 1;
}

.auth-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4f0e8 100%);
    overflow: hidden;
    z-index: -1;
}

/* Leaf Animations */
.leaf-animation {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.15;
    z-index: -1;
}

.leaf-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf1.png');
    width: 200px;
    height: 200px;
    top: 10%;
    left: 5%;
    animation: float 8s ease-in-out infinite;
}

.leaf-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf2.png');
    width: 150px;
    height: 150px;
    top: 60%;
    left: 10%;
    animation: float 6s ease-in-out infinite reverse;
}

.leaf-3 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf3.png');
    width: 180px;
    height: 180px;
    top: 30%;
    right: 8%;
    animation: float 7s ease-in-out infinite 1s;
}

.leaf-4 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf4.webp');
    width: 120px;
    height: 120px;
    bottom: 10%;
    right: 5%;
    animation: float 5s ease-in-out infinite reverse 0.5s;
}

@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
    100% { transform: translateY(0) rotate(0deg); }
}

/* Auth Card */
.auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    padding: 2.5rem;
    transition: all 0.5s ease;
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.auth-logo {
    height: 50px;
    width: auto;
    margin-bottom: 1rem;
}

.auth-title {
    font-weight: 700;
    color: #2e7d32;
}

.auth-subtitle {
    color: #6c757d;
}

/* Form Styles */
.form-floating {
    position: relative;
}

.password-field {
    position: relative;
}

.btn-eye {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #6c757d;
    z-index: 5;
}

.btn-eye:hover {
    color: #2e7d32;
}

.btn-auth {
    position: relative;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    overflow: hidden;
}

.btn-auth .btn-text {
    position: relative;
    z-index: 2;
}

.btn-auth::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-auth:hover::after {
    opacity: 1;
}

/* Social Buttons */
.btn-social {
    border-radius: 50px;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-google {
    background: #fff;
    color: #db4437;
    border: 1px solid #dee2e6;
}

.btn-google:hover {
    background: #db4437;
    color:#fff;
    border-color: #db4437;
}



.btn-facebook {
    background: #fff;
    color: #3b5998;
    border: 1px solid #dee2e6;
}

.btn-facebook:hover {
    background: #3b5998;
    color: #fff;
    border-color: #3b5998;
}

/* Divider */
.divider-text {
    position: relative;
    text-align: center;
    margin: 1.5rem 0;
    color: #6c757d;
}

.divider-text::before,
.divider-text::after {
    content: "";
    position: absolute;
    top: 50%;
    width: 30%;
    height: 1px;
    background: #dee2e6;
}

.divider-text::before {
    left: 0;
}

.divider-text::after {
    right: 0;
}

/* CAPTCHA Styles */
.captcha-container {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

.captcha-code {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    letter-spacing: 2px;
    color: #2e7d32;
    background: #fff;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: 1px dashed #dee2e6;
}

/* Responsive Adjustments */
@media (max-width: 575.98px) {
    .auth-card {
        padding: 1.5rem;
    }
    
    .auth-logo {
        height: 40px;
    }
    
    .btn-social span {
        display: none;
    }
    
    .btn-social i {
        margin-right: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const passwordEye = document.querySelector('.btn-eye');
    const passwordInput = document.getElementById('password');
    
    if (passwordEye && passwordInput) {
        passwordEye.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form validation
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                const btn = document.getElementById('loginButton');
                if (btn) {
                    btn.querySelector('.btn-text').classList.add('d-none');
                    btn.querySelector('.spinner-border').classList.remove('d-none');
                    btn.disabled = true;
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    }
    
    // Hover effects for auth card
    const authCard = document.getElementById('authCard');
    if (authCard) {
        authCard.addEventListener('mousemove', function(e) {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            authCard.style.transform = `perspective(1000px) rotateY(${(x - 0.5) * 5}deg) rotateX(${(0.5 - y) * 5}deg)`;
        });
        
        authCard.addEventListener('mouseleave', function() {
            authCard.style.transform = 'perspective(1000px) rotateY(0) rotateX(0)';
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';

// Helper function to generate CAPTCHA code
function generateCaptchaCode($length = 6) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}
?>