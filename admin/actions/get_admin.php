<?php
require_once __DIR__ . '/../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    // Verify CSRF token and admin permissions
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Unauthorized access');
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Admin ID not provided');
    }

    $admin_id = intval($_GET['id']);
    $pdo = Database::connect();

    $stmt = $pdo->prepare("SELECT * FROM Admins WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        throw new Exception('Admin not found');
    }

    echo json_encode([
        'success' => true,
        'admin' => $admin
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}