<?php
header('Content-Type: application/json');
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and include config
session_start();
require_once __DIR__ . '/../../config.php';

// Set JSON header

// Initialize response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'debug' => []
];

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate and sanitize inputs
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'default' => 1]
    ]);

    if (!$productId || $productId <= 0) {
        throw new Exception('Invalid product ID');
    }

    // Verify database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Debug: Log received data
    error_log("Add to Cart - Product ID: $productId, Quantity: $quantity");

    // Get or create cart
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    $response['debug']['user_id'] = $userId;
    $response['debug']['session_id'] = $sessionId;

    $cart = getOrCreateCart($userId, $sessionId);
    $cartId = $cart['cart_id'];
    $_SESSION['cart_id'] = $cartId;

    $response['debug']['cart_id'] = $cartId;

    // Verify product exists and has stock
    $product = getProductById($productId);
    if (!$product) {
        throw new Exception('Product not found');
    }

    // Add to cart logic
    $stmt = $conn->prepare("SELECT * FROM CartItems WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        // Update existing item
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE CartItems SET quantity = ? WHERE cart_item_id = ?");
        $stmt->execute([$newQuantity, $existingItem['cart_item_id']]);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO CartItems (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cartId, $productId, $quantity, $product['price']]);
    }

    // Get updated cart count
    // After updating cart items:
$stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM CartItems WHERE cart_id = ?");
$stmt->execute([$cartId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['cart_count'] = (int)$result['count'];
    
    $response = [
        'success' => true,
        'message' => 'Product added to cart',
        'cartCount' => (int)($result['count'] ?? 0)
    ];
    // $_SESSION['cart_count'] = $response['cartCount'];
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['debug']['db_error'] = $e->getMessage();
    error_log("PDOException: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Exception: " . $e->getMessage());
}

// Output the response
echo json_encode($response);
exit;