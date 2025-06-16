<?php
require_once __DIR__ . '/../config/database.php';
session_start();

try {
    $pdo = Database::connect();
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }
    
    // Validate input
    $required = ['first_name', 'last_name', 'email', 'password', 'confirm_password', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if ($_POST['password'] !== $_POST['confirm_password']) {
        throw new Exception('Passwords do not match');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT admin_id FROM Admins WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }
    
    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Insert new admin
    $stmt = $pdo->prepare("
        INSERT INTO Admins (email, password_hash, first_name, last_name, role)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['email'],
        $password_hash,
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['role']
    ]);
    
    $_SESSION['message'] = "Admin created successfully";
    header('Location: ../users.php?type=admins');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../users.php?type=admins');
    exit;
}
?>