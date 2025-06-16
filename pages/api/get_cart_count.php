<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

try {
    $count = 0;
    
    if (isset($_SESSION['user_id'])) {
        // For logged-in users
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ci.quantity), 0) as count 
            FROM Carts c
            LEFT JOIN CartItems ci ON c.cart_id = ci.cart_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int)$result['count'];
    } elseif (isset($_SESSION['cart_id'])) {
        // For guest users with session cart
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(quantity), 0) as count 
            FROM CartItems 
            WHERE cart_id = ?
        ");
        $stmt->execute([$_SESSION['cart_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int)$result['count'];
    }

    echo json_encode(['success' => true, 'count' => $count]);
} catch (PDOException $e) {
    error_log("Cart count error: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0]);
}