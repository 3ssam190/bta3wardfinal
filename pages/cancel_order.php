<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$orderId = $_POST['order_id'] ?? null;
if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

try {
    // Verify order belongs to user and is cancellable
    $stmt = $conn->prepare("
        SELECT user_id, status 
        FROM Orders 
        WHERE order_id = ? 
        AND status IN ('Pending', 'Processing')
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled']);
        exit();
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Update order status to 'Cancelled' (note lowercase 'c' to match your enum)
    $stmt = $conn->prepare("UPDATE Orders SET status = 'Cancelled' WHERE order_id = ?");
    $stmt->execute([$orderId]);
    
    // Also update payment status if needed
    $stmt = $conn->prepare("
        UPDATE Payments 
        SET payment_status = 'Refunded' 
        WHERE order_id = ? 
        AND payment_status IN ('Pending', 'Completed')
    ");
    $stmt->execute([$orderId]);
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Cancel order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}