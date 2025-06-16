<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'count' => 0,
    'message' => ''
];

try {
    // For guests, use session count
    if (!isset($_SESSION['user_id'])) {
        $response['count'] = $_SESSION['cart_count'] ?? 0;
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    // For logged-in users, check database
    $userId = $_SESSION['user_id'];
    
    // Get active cart count
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0) as count 
        FROM CartItems ci
        JOIN Carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ? AND (c.session_id IS NULL OR c.session_id = ?)
    ");
    $stmt->execute([$userId, session_id()]);
    $result = $stmt->fetch();

    $response = [
        'success' => true,
        'count' => (int)$result['count'],
        'message' => ''
    ];

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log($response['message']);
    // Fallback to session count if available
    $response['count'] = $_SESSION['cart_count'] ?? 0;
}

echo json_encode($response);