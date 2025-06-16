<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/PaymobHelper.php';

// Get the raw POST data
$payload = @file_get_contents('php://input');
$data = json_decode($payload, true);

if (!empty($data)) {
    try {
        $paymob = new PaymobHelper();
        
        // Verify HMAC
        if (!$paymob->verifyHmac($data)) {
            throw new Exception("Invalid HMAC signature");
        }
        
        if ($data['success'] === true) {
            // Payment was successful
            $orderId = $data['order']['merchant_order_id'];
            $transactionId = $data['id'];
            $amount = $data['amount_cents'] / 100;
            
            // Update your database
            $conn->beginTransaction();
            
            try {
                // Update payment status
                $stmt = $conn->prepare("
                    UPDATE Payments 
                    SET payment_status = 'Completed', 
                        transaction_id = ?,
                        payment_date = NOW()
                    WHERE order_id = ?
                ");
                $stmt->execute([$transactionId, $orderId]);
                
                // Update order status
                $stmt = $conn->prepare("
                    UPDATE Orders 
                    SET status = 'Processing'
                    WHERE order_id = ?
                ");
                $stmt->execute([$orderId]);
                
                $conn->commit();
                
                // Clear cart if still exists
                if (!empty($_SESSION['cart_id'])) {
                    clearCart($_SESSION['cart_id']);
                    unset($_SESSION['cart_id']);
                    unset($_SESSION['cart_count']);
                }
                
                // Send confirmation email, etc.
                
                http_response_code(200);
                echo "OK";
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        } else {
            // Payment failed
            $orderId = $data['order']['merchant_order_id'];
            
            // Update payment status
            $stmt = $conn->prepare("
                UPDATE Payments 
                SET payment_status = 'Failed'
                WHERE order_id = ?
            ");
            $stmt->execute([$orderId]);
            
            http_response_code(200);
            echo "OK";
        }
    } catch (Exception $e) {
        error_log("Paymob callback error: " . $e->getMessage());
        http_response_code(400);
        echo "Error";
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>