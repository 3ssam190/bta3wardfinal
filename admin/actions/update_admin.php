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
    $required = ['admin_id', 'first_name', 'last_name', 'email', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $admin_id = intval($_POST['admin_id']);
    
    // Check if email already exists for another admin
    $stmt = $pdo->prepare("SELECT admin_id FROM Admins WHERE email = ? AND admin_id != ?");
    $stmt->execute([$_POST['email'], $admin_id]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists for another admin');
    }
    
    // Check if password is being updated
    $password_update = '';
    $params = [
        $_POST['email'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['role'],
        $admin_id
    ];
    
    if (!empty($_POST['password'])) {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception('Passwords do not match');
        }
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ', password_hash = ?';
        array_unshift($params, $password_hash);
    }
    
    // Update admin
    $stmt = $pdo->prepare("
        UPDATE Admins 
        SET email = ?, first_name = ?, last_name = ?, role = ? $password_update
        WHERE admin_id = ?
    ");
    $stmt->execute($params);
    
    $_SESSION['message'] = "Admin updated successfully";
    header('Location: ../users.php?type=admins');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../users.php?type=admins');
    exit;
}
?>