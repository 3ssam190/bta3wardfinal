<?php
session_start();

// Dummy credentials
$user = 'admin';
$pass = '12345';

if ($_POST['username'] === $user && $_POST['password'] === $pass) {
    $_SESSION['admin'] = $user;
    header("Location: dashboard.php");
    exit;
} else {
    echo "<script>alert('Invalid credentials'); window.location.href='login.php';</script>";
}
?>
