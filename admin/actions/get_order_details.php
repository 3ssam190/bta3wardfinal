<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::connect();
    
    // Get order ID from request
    $orderId = $_GET['id'] ?? 0;
    
    // Get order details with region information
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
            u.email AS customer_email,
            u.phone AS customer_phone,
            dp.region_name AS delivery_region_name,
            dp.delivery_fee,
            dp.estimated_delivery_days
        FROM Orders o
        JOIN Users u ON o.user_id = u.user_id
        JOIN DeliveryPricing dp ON o.delivery_region = dp.region_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Get order items with Arabic names if available
    $stmt = $pdo->prepare("
        SELECT 
            oi.*,
            COALESCE(
                (SELECT translated_text FROM Translations 
                 WHERE entity_type = 'product' 
                 AND entity_id = p.product_id 
                 AND field_name = 'name' 
                 AND language_code = 'ar' 
                 LIMIT 1),
                p.name
            ) AS product_name,
            p.name AS original_product_name,
            pi.image_url
        FROM OrderItems oi
        LEFT JOIN Products p ON oi.product_id = p.product_id
        LEFT JOIN ProductImages pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment details
    $stmt = $pdo->prepare("
        SELECT * FROM Payments 
        WHERE order_id = ?
    ");
    $stmt->execute([$orderId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'payment' => $payment
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}