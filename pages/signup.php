<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

// Verify database connection
if (!isset($conn)) {
    die("Database connection failed. Please try again later.");
}

// Verify required functions exist
if (!function_exists('getUserByEmail') || !function_exists('createUser')) {
    die("System configuration error. Please contact administrator.");
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'password' => $_POST['password'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'region' => $_POST['region'] ?? 'Unknown',
        'postal_code' => $_POST['postal_code'] ?? ''
    ];
    
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($data['first_name']) || empty($data['last_name'])) {
        $errors[] = "First and last name are required";
    }
    
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (strlen($data['password']) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($data['password'] !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email exists
    if (getUserByEmail($data['email'])) {
        $errors[] = "Email already registered";
    }
    
    // Handle profile photo upload
    $profilePhoto = '';
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = __DIR__ . '/../assets/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
            $profilePhoto = 'assets/uploads/profiles/' . $filename;
        } else {
            $errors[] = "Error uploading profile photo";
        }
    }
    
    
    
    
    if (empty($errors)) {
    // Generate verification token and expiry (24 hours from now)
    $verificationToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', time() + 24 * 3600);
    
    // Add verification data to user data
    $data['is_verified'] = 0;
    $data['verification_token'] = $verificationToken;
    $data['token_expires_at'] = $tokenExpiry;
    
    if (!empty($profilePhoto)) {
        $data['profile_photo'] = $profilePhoto;
    }
    
    if (createUser($data)) {
        // Get the newly created user
        $user = getUserByEmail($data['email']);
        
        // Send verification email
        $verificationLink = BASE_URL . "/pages/verify-email.php?token=" . urlencode($verificationToken) . "&email=" . urlencode($user['email']);
        
        $subject = "Verify Your Email - Plant Store";
        $htmlContent = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { color: #2e7d32; font-size: 24px; margin-bottom: 20px; }
                    .button { 
                        display: inline-block; 
                        padding: 10px 20px; 
                        background-color: #2e7d32; 
                        color: white !important; 
                        text-decoration: none; 
                        border-radius: 5px; 
                        margin: 20px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>Email Verification</div>
                    <p>Hello {$user['first_name']},</p>
                    <p>Thank you for registering with Plant Store. Please verify your email address by clicking the button below:</p>
                    <p><a href='$verificationLink' class='button'>Verify Email Address</a></p>
                    <p>If you didn't create an account, you can safely ignore this email.</p>
                    <p>This link will expire in 24 hours.</p>
                </div>
            </body>
            </html>
        ";
        
        $textContent = "Hello {$user['first_name']},\n\n";
        $textContent .= "Thank you for registering with Plant Store. Please verify your email by visiting this link:\n";
        $textContent .= "$verificationLink\n\n";
        $textContent .= "If you didn't create an account, you can ignore this email.\n";
        $textContent .= "This link expires in 24 hours.\n";
        
        // Send email using PHPMailer
        require_once __DIR__ . '/../vendor/autoload.php';
        
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.titan.email'; // or 'mail.yourdomain.com'
            $mail->SMTPAuth = true;
            $mail->Username = 'Support@bta3ward.shop'; // Your Hostinger email
            $mail->Password = 'ESAM123esam@'; // The password you set
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            $mail->setFrom('Support@bta3ward.shop', 'Bta3 Ward');
            $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlContent;
            $mail->AltBody = $textContent;
            
            $mail->send();
            
            // Store email in session for verification-sent.php
            $_SESSION['verification_email'] = $user['email'];
            
            // Redirect to verification notice page
            header("Location: verification-sent.php");
            exit();
            
        } catch (Exception $e) {
                error_log("Failed to send verification email: " . $e->getMessage());
                $errors[] = "We couldn't send the verification email. Please try again later.";
            }
        } else {
            $errors[] = "Error creating user. Please try again.";
        }
    }
}

$pageTitle = 'Sign Up';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Animated Background -->
<div class="auth-background">
    <!-- Floating leaves -->
    <div class="leaf-animation leaf-1"></div>
    <div class="leaf-animation leaf-2"></div>
    <div class="leaf-animation leaf-3"></div>
    <div class="leaf-animation leaf-4"></div>
    
    <!-- Subtle floating plant elements -->
    <div class="plant-animation plant-1"></div>
    <div class="plant-animation plant-2"></div>
    
    <!-- Floating soil/dirt particles -->
    <div class="particle-animation particle-1"></div>
    <div class="particle-animation particle-2"></div>
    <div class="particle-animation particle-3"></div>
    
    <!-- Animated gradient overlay -->
    <div class="gradient-overlay"></div>
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
                    
                    <!-- Sign Up Form -->
                    <div class="auth-form-container active" id="signupForm">
                        <h2 class="auth-title text-center mb-3">Create Your Account</h2>
                        <p class="auth-subtitle text-center mb-4">Join our community today</p>
                        
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="signupForm" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               placeholder="First Name" required
                                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                        <label for="first_name">First Name</label>
                                        <div class="invalid-feedback">
                                            Please enter your first name
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Last Name -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               placeholder="Last Name" required
                                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                        <label for="last_name">Last Name</label>
                                        <div class="invalid-feedback">
                                            Please enter your last name
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="name@example.com" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <label for="email">Email address</label>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="Phone Number"
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    <label for="phone">Phone Number (Optional)</label>
                                </div>
                            </div>
                            
                            <!-- Password -->
                            <div class="mb-3">
                                <div class="form-floating">
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Password" required
                                               minlength="8">
                                        <label for="password">Password</label>
                                        <button type="button" class="btn btn-eye" aria-label="Toggle password visibility">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Password must be at least 8 characters
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <div class="form-floating">
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" placeholder="Confirm Password" required
                                               minlength="8">
                                        <label for="confirm_password">Confirm Password</label>
                                        <button type="button" class="btn btn-eye" aria-label="Toggle password visibility">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Passwords must match
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address" 
                                              placeholder="Address" style="height: 100px"><?php 
                                              echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    <label for="address">Address (Optional)</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- City -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="city" name="city" 
                                               placeholder="City"
                                               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                                        <label for="city">City (Optional)</label>
                                    </div>
                                </div>
                                
                                <!-- Region -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="region" name="region" required>
                                            <option value="Cairo" <?php echo (($_POST['region'] ?? '') === 'Cairo') ? 'selected' : ''; ?>>Cairo</option>
                                            <option value="Alexandria" <?php echo (($_POST['region'] ?? '') === 'Alexandria') ? 'selected' : ''; ?>>Alexandria</option>
                                            <!-- Add more regions as needed -->
                                        </select>
                                        <label for="region">Region</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Profile Photo -->
                            <div class="mb-4">
                                <label for="profile_photo" class="form-label">Profile Photo (Optional)</label>
                                <div class="profile-photo-upload">
                                    <div class="preview-container">
                                        <img id="profilePreview" src="<?php echo BASE_URL; ?>/assets/images/default-profile.png" 
                                             class="img-thumbnail rounded-circle" alt="Profile preview">
                                    </div>
                                    <input type="file" class="form-control" id="profile_photo" name="profile_photo" 
                                           accept="image/*" onchange="previewImage(this)" required>
                                    <small class="text-muted">Max size: 2MB (JPEG, PNG)</small>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-success btn-auth" id="signupButton">
                                    <span class="btn-text">Create Account</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                            
                            
                            
                            <!-- Sign In Link -->
                            <div class="text-center">
                                <p class="text-muted">Already have an account? 
                                    <a href="signin.php" class="auth-link">Sign in</a>
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
.auth-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    overflow: hidden;
    z-index: -1;
}

/* New plant animations */
.plant-animation {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    z-index: -1;
    opacity: 0.1;
}

.plant-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/plant1.webp');
    width: 180px;
    height: 180px;
    bottom: -50px;
    left: 5%;
    animation: sway 12s ease-in-out infinite;
}

.plant-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/plant2.avif');
    width: 150px;
    height: 150px;
    bottom: -30px;
    right: 8%;
    animation: sway 10s ease-in-out infinite reverse;
}

/* Particle animations */
.particle-animation {
    position: absolute;
    background-color: #5d4037;
    border-radius: 50%;
    opacity: 0.1;
    z-index: -1;
}

.particle-1 {
    width: 10px;
    height: 10px;
    top: 20%;
    left: 15%;
    animation: float 15s linear infinite;
}

.particle-2 {
    width: 8px;
    height: 8px;
    top: 65%;
    left: 80%;
    animation: float 12s linear infinite reverse;
}

.particle-3 {
    width: 6px;
    height: 6px;
    top: 40%;
    left: 70%;
    animation: float 18s linear infinite;
}

/* Gradient overlay */
.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at center, transparent 0%, rgba(232, 245, 233, 0.7) 100%);
    z-index: 0;
}

/* Updated leaf animations */
.leaf-animation {
    position: absolute;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.15;
    z-index: -1;
}

.leaf-1 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf1.png');
    width: 180px;
    height: 180px;
    top: 10%;
    left: 5%;
    animation: float 8s ease-in-out infinite, rotate 20s linear infinite;
}

.leaf-2 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf2.png');
    width: 150px;
    height: 150px;
    top: 60%;
    left: 10%;
    animation: float 6s ease-in-out infinite reverse, rotate 25s linear infinite reverse;
}

.leaf-3 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf3.png');
    width: 160px;
    height: 160px;
    top: 30%;
    right: 8%;
    animation: float 7s ease-in-out infinite 1s, rotate 30s linear infinite;
}

.leaf-4 {
    background-image: url('<?php echo BASE_URL; ?>/assets/images/leaf4.webp');
    width: 120px;
    height: 120px;
    bottom: 10%;
    right: 5%;
    animation: float 5s ease-in-out infinite reverse 0.5s, rotate 18s linear infinite reverse;
}

/* New animation for plants */
@keyframes sway {
    0%, 100% { transform: translateY(0) rotate(-5deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

/* Updated float animation with rotation */
@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
    100% { transform: translateY(0) rotate(360deg); }
}

/* Separate rotation animation */
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
/* Add these styles to your existing auth styles */
.profile-photo-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.auth-logo {
    height: 40px;  /* Reduced from 50px */
    width: auto;
    margin-bottom: 1rem;
    transition: transform 0.3s ease; /* Optional: adds hover effect */
}

/* Optional hover effect */
.auth-logo:hover {
    transform: scale(1.05);
}

.preview-container {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e9ecef;
    margin-bottom: 1rem;
    position: relative;
}

#profilePreview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-photo-upload input[type="file"] {
    width: 100%;
    max-width: 300px;
}

/* Password strength meter */
.password-strength {
    height: 5px;
    background: #e9ecef;
    margin-top: 0.5rem;
    border-radius: 3px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0;
    background: #dc3545;
    transition: width 0.3s ease, background 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .preview-container {
        width: 100px;
        height: 100px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const passwordEyes = document.querySelectorAll('.btn-eye');
    
    passwordEyes.forEach(eye => {
        eye.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
    
    // Form validation
    const form = document.getElementById('signupForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                const btn = document.getElementById('signupButton');
                if (btn) {
                    btn.querySelector('.btn-text').classList.add('d-none');
                    btn.querySelector('.spinner-border').classList.remove('d-none');
                    btn.disabled = true;
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords must match");
            } else {
                confirmPassword.setCustomValidity("");
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
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

// Profile photo preview
function previewImage(input) {
    const preview = document.getElementById('profilePreview');
    const file = input.files[0];
    const reader = new FileReader();
    
    if (file) {
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file (JPEG, PNG)');
            input.value = '';
            return;
        }
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        
        reader.readAsDataURL(file);
    }
}
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>