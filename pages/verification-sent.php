<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

$email = $_SESSION['verification_email'] ?? '';
unset($_SESSION['verification_email']);

if (empty($email)) {
    header("Location: signup.php");
    exit();
}
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <img style="height:100px;width:auto;" src="<?php echo BASE_URL; ?>/assets/images/logo1.png" alt="Plant Store Logo" class="auth-logo">
                    </div>
                    
                    <div class="auth-form-container">
                        <h2 class="auth-title text-center mb-4">Verify Your Email</h2>
                        
                        <div class="alert alert-success text-center">
                            <i class="fas fa-envelope fa-3x mb-3 text-primary"></i>
                            <h4>Verification Email Sent</h4>
                            <p>We've sent a verification link to <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
                            <p>Please check your inbox and click the link to verify your email address.</p>
                            <hr>
                            <p class="mb-0">Didn't receive the email? <a href="resend-verification.php?email=<?php echo urlencode($email); ?>" class="alert-link">Resend verification</a></p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="signin.php" class="btn btn-success">Back to Sign In</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>