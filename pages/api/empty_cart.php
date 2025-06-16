<?php
// MUST be first line - no whitespace before!
header('Content-Type: application/json');

// Disable displaying errors to users
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config.php';

try {
    // Verify session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in or has a session cart
    $cartId = null;
    if (isset($_SESSION['user_id'])) {
        // Get user's cart
        $stmt = $conn->prepare("SELECT cart_id FROM Carts WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        $cartId = $cart ? $cart['cart_id'] : null;
    } elseif (isset($_SESSION['cart_id'])) {
        $cartId = $_SESSION['cart_id'];
    }

    if (!$cartId) {
        throw new Exception('No active cart found');
    }

    // Delete all cart items
    $stmt = $conn->prepare("DELETE FROM CartItems WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    // Also delete the cart record if it exists
    $stmt = $conn->prepare("DELETE FROM Carts WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    // Update session
    unset($_SESSION['cart_id']);
    $_SESSION['cart_count'] = 0;

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Cart emptied successfully',
        'cartCount' => 0
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}