<?php
require_once __DIR__ . '/../config.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If using Composer
require '../vendor/autoload.php';



$email = $_GET['email'] ?? '';

if (empty($email)) {
    $_SESSION['error'] = "Email address required";
    header("Location: signup.php");
    exit();
}

$user = getUserByEmail($email);

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: signup.php");
    exit();
}

if ($user['is_verified']) {
    $_SESSION['message'] = "This email is already verified";
    header("Location: signin.php");
    exit();
}

// Generate new token
$verificationToken = bin2hex(random_bytes(32));
$tokenExpiry = date('Y-m-d H:i:s', time() + 24 * 3600);

// Update user record
$stmt = $conn->prepare("UPDATE Users SET verification_token = ?, token_expires_at = ? WHERE user_id = ?");
$stmt->execute([$verificationToken, $tokenExpiry, $user['user_id']]);

// Verification email content
$verificationLink = BASE_URL . "/pages/verify-email.php?token=" . urlencode($verificationToken) . "&email=" . urlencode($user['email']);

$subject = "Verify Your Email - Plant Store";
$htmlContent = "<h2>Email Verification</h2>
               <p>Please click the following link to verify your email address:</p>
               <p><a href='$verificationLink'>$verificationLink</a></p>
               <p>This link will expire in 24 hours.</p>";

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.titan.email'; // or 'mail.yourdomain.com'
    $mail->SMTPAuth = true;
    $mail->Username = 'Support@bta3ward.shop'; // Your Hostinger email
    $mail->Password = 'ESAM123esam@'; // The password you set
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;       // Define in config.php
    
    // Recipients
    $mail->setFrom('Support@bta3ward.shop', 'Bta3 Ward');
    $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlContent;
    $mail->AltBody = strip_tags($htmlContent);
    
    $mail->send();
    
    $_SESSION['verification_email'] = $email;
    $_SESSION['message'] = "Verification email resent successfully";
    header("Location: verification-sent.php");
    exit();
    
} catch (Exception $e) {
    error_log("Failed to resend verification email: " . $mail->ErrorInfo);
    $_SESSION['error'] = "Failed to resend verification email. Please try again later.";
    header("Location: signup.php");
    exit();
}