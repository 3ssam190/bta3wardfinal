<?php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}
// Verify database connection
if (!isset($conn) || !($conn instanceof PDO)) {
    die("Database connection error. Please try again later.");
}

// Initialize variables
$email = '';
$message = '';
$error = '';
$emailSent = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Basic validation
    if (empty($email)) {
        $error = "Please enter your email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email exists in database
        $user = getUserByEmail($email);
        $admin = getAdminByEmail($email);
        
        if (!$user && !$admin) {
            $error = "No account found with that email address";
        } else {
            // Generate password reset token (32 characters)
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
            
            // Store token in database
            if ($user) {
                storePasswordResetToken($user['user_id'], $token, $expires, 'user');
                $userId = $user['user_id'];
                $name = $user['first_name'] . ' ' . $user['last_name'];
                $userType = 'user';
            } else {
                storePasswordResetToken($admin['admin_id'], $token, $expires, 'admin');
                $userId = $admin['admin_id'];
                $name = $admin['first_name'] . ' ' . $admin['last_name'];
                $userType = 'admin';
            }
            
            // Create reset link
            $resetLink = BASE_URL . "/pages/reset-password.php?token=$token&email=" . urlencode($email) . "&type=$userType";
            
            // Email content
            $subject = "Password Reset Request - Plant Store";
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
                        .footer { margin-top: 30px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>Password Reset Request</div>
                        <p>Hello $name,</p>
                        <p>We received a request to reset your password for your Bta3 Ward Store account.</p>
                        <p>Click the button below to reset your password:</p>
                        <p><a href='$resetLink' class='button'>Reset Password</a></p>
                        <p>If you didn't request this, please ignore this email. The link will expire in 1 hour.</p>
                        <div class='footer'>
                            <p>Thank you,<br>The Bta3 Ward Support Team</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $textContent = "Hello $name,\n\n";
            $textContent .= "We received a request to reset your password for your bta3 ward account.\n\n";
            $textContent .= "Click this link to reset your password:\n$resetLink\n\n";
            $textContent .= "If you didn't request this, please ignore this email. The link will expire in 1 hour.\n\n";
            $textContent .= "Thank you,\nThe Bta3 Ward Support Team";
            
            // Send email using PHPMailer with Mailtrap
            require_once __DIR__ . '/../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Hostinger SMTP Settings
                $mail->isSMTP();
                $mail->Host = 'smtp.titan.email'; // or 'mail.yourdomain.com'
                $mail->SMTPAuth = true;
                $mail->Username = 'Support@bta3ward.shop'; // Your Hostinger email
                $mail->Password = 'ESAM123esam@'; // The password you set
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // Use ENCRYPTION_STARTTLS for port 587
                $mail->Port = 465; // 465 for SSL, 587 for TLS
            
                // Sender & Recipient
                $mail->setFrom('Support@bta3ward.shop', 'Bta3 Ward');
                $mail->addAddress($email, $name); // Recipient from your form
            
                // Email content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlContent;
                $mail->AltBody = $textContent;
            
                $mail->send();
                $emailSent = true;
                $message = "Email sent successfully!";
            } catch (Exception $e) {
                $error = "Failed to send email. Error: " . $e->getMessage();
                error_log("Email error: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
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
                        <img style="height:75px;width:auto;" src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" class="auth-logo">
                    </div>
                    
                    <!-- Forgot Password Form -->
                    <div class="auth-form-container active">
                        <h2 class="auth-title text-center mb-4">Forgot Password</h2>
                        <p class="auth-subtitle text-center mb-4">Enter your email to reset your password</p>
                        
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$emailSent): ?>
                        <form method="POST" id="forgotPasswordForm" class="needs-validation" novalidate>
                            <!-- Email Field -->
                            <div class="form-floating mb-4">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="name@example.com" required
                                       value="<?php echo htmlspecialchars($email); ?>">
                                <label for="email">Email address</label>
                                <div class="invalid-feedback">
                                    Please enter a valid email address
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-auth" id="submitButton">
                                    <span class="btn-text">Reset Password</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                            
                            <!-- Back to Sign In -->
                            <div class="text-center">
                                <p class="text-muted">Remember your password? 
                                    <a href="signin.php" class="auth-link">Sign in</a>
                                </p>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-envelope-open-text fa-4x text-primary mb-3"></i>
                                <h4>Check Your Email</h4>
                                <p class="text-muted">We've sent password reset instructions to your email address.</p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="signin.php" class="btn btn-primary btn-auth">
                                    Back to Sign In
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('forgotPasswordForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                const btn = document.getElementById('submitButton');
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
?>