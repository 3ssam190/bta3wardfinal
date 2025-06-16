<?php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check for required parameters
if (!isset($_GET['token']) || !isset($_GET['email']) || !isset($_GET['type'])) {
    header("Location: forgot-password.php?error=invalid_link");
    exit();
}

$token = $_GET['token'];
$email = $_GET['email'];
$userType = $_GET['type'];

// Validate token
$tokenData = getPasswordResetToken($token, $userType);
if (!$tokenData) {
    header("Location: forgot-password.php?error=expired_link");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$password = '';
$confirmPassword = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($password) || empty($confirmPassword)) {
    $error = "Please fill in all fields";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one number";
    } elseif (!preg_match('/[\W]/', $password)) {
        $error = "Password must contain at least one special character";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Update password in database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        
        if ($userType === 'admin') {
            $stmt = $conn->prepare("UPDATE Admins SET password_hash = ? WHERE admin_id = ?");
            $result = $stmt->execute([$hashedPassword, $tokenData['admin_id']]);
        } else {
            $stmt = $conn->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
            $result = $stmt->execute([$hashedPassword, $tokenData['user_id']]);
        }
        
        if (!$result) {
            $error = "Failed to update password. Please try again.";
            error_log("Password update failed for $email");
        } else {
            // Delete the used token
            deletePasswordResetToken($token, $userType);
            
            $success = "Your password has been reset successfully. You can now <a href='signin.php'>sign in</a> with your new password.";
        }
    }
}

$pageTitle = 'Reset Password';
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
                <div class="auth-card" id="authCard">
                    <div class="text-center mb-4">
                        <img style="height:100px;width:auto;" src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" class="auth-logo">
                    </div>
                    
                    <div class="auth-form-container active">
                        <h2 class="auth-title text-center mb-4">Reset Password</h2>
                        
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                        </div>
                        <?php else: ?>
                        
                        <form method="POST" id="resetPasswordForm" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <div class="password-field">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="New Password" required minlength="8">
                                    <label for="password">New Password</label>
                                    <button type="button" class="btn btn-eye" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Password must be at least 8 characters
                                </div>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <div class="password-field">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm Password" required minlength="8">
                                    <label for="confirm_password">Confirm Password</label>
                                    <button type="button" class="btn btn-eye" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Passwords must match
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-auth" id="submitButton">
                                    <span class="btn-text">Reset Password</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
    const form = document.getElementById('resetPasswordForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('confirm_password').setCustomValidity("Passwords must match");
            } else {
                document.getElementById('confirm_password').setCustomValidity("");
            }
            
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
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';