<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Add custom product to cart
    $customProduct = [
        'type' => 'custom_bouquet',
        'bouquet_id' => $data['bouquet_id'],
        'quantity' => 1,
        'price' => $data['total_price']
    ];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][] = $customProduct;
    
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}