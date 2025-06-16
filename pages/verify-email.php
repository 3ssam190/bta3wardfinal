<?php
require_once __DIR__ . '/../config.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($token) || empty($email)) {
    $_SESSION['error'] = "Invalid verification link";
    header("Location: ../index.php");
    exit();
}

// Get user by email
$user = getUserByEmail($email);

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: ../index.php");
    exit();
}

// Check if already verified
if ($user['is_verified']) {
    $_SESSION['message'] = "Your email is already verified";
    header("Location: signin.php");
    exit();
}

// Verify token
if ($user['verification_token'] !== $token) {
    $_SESSION['error'] = "Invalid verification token";
    header("Location: ../index.php");
    exit();
}

// Check token expiry
if (strtotime($user['token_expires_at']) < time()) {
    $_SESSION['error'] = "Verification link has expired. Please request a new one.";
    header("Location: resend-verification.php?email=" . urlencode($email));
    exit();
}

// Mark as verified
$stmt = $conn->prepare("UPDATE Users SET is_verified = TRUE, verification_token = NULL, token_expires_at = NULL WHERE user_id = ?");
$stmt->execute([$user['user_id']]);

// Auto-login the user if needed
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_role'] = 'user';

// Set profile photo in session if exists
if (!empty($user['profile_photo'])) {
    $_SESSION['profile_photo'] = strpos($user['profile_photo'], '/') === 0 
        ? $user['profile_photo'] 
        : ASSETS_DIR . $user['profile_photo'];
} else {
    $_SESSION['profile_photo'] = DEFAULT_PROFILE;
}

$_SESSION['message'] = "Email verified successfully! You are now logged in.";
header("Location: ../index.php");
exit();
?>