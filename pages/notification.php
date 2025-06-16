<?php
function sendOrderNotifications($orderId, $customerData) {
    // Get order details
    $order = getOrderDetails($orderId);
    
    // Customer email
    sendOrderEmail($customerData['email'], $order);
    
    // Admin email
    sendAdminNotification($order);
    
    // WhatsApp message
    if (!empty($customerData['phone'])) {
        sendWhatsAppNotification($customerData['phone'], $order);
    }
    
    // Dashboard notification
    createAdminNotification($orderId);
}

function sendOrderEmail($email, $order) {
    $subject = "Order Confirmation #" . $order['order_id'];
    $message = "
    <html>
    <head>
        <title>Your Order Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your order!</h2>
        <p>Order #: {$order['order_id']}</p>
        <p>Date: {$order['order_date']}</p>
        <p>Total: " . CURRENCY . number_format($order['total_amount'], 2) . "</p>
        
        <h3>Order Items:</h3>
        <ul>
    ";
    
    foreach ($order['items'] as $item) {
        $message .= "<li>{$item['quantity']} x {$item['name']} - " . CURRENCY . number_format($item['unit_price'], 2) . "</li>";
    }
    
    $message .= "
        </ul>
        <p>We'll notify you when your order ships.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@yourdomain.com\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendAdminNotification($order) {
    $adminEmail = "admin@yourdomain.com";
    $subject = "New Order #" . $order['order_id'];
    
    $message = "New order received:\n\n";
    $message .= "Order #: {$order['order_id']}\n";
    $message .= "Customer: {$order['customer_name']}\n";
    $message .= "Total: " . CURRENCY . number_format($order['total_amount'], 2) . "\n";
    $message .= "Payment Method: {$order['payment_method']}\n\n";
    $message .= "View order in dashboard: " . BASE_URL . "/admin/orders.php?order_id={$order['order_id']}";
    
    mail($adminEmail, $subject, $message);
}

function sendWhatsAppNotification($phone, $order) {
    $message = urlencode(
        "Thank you for your order #{$order['order_id']}\n" .
        "Total: " . CURRENCY . number_format($order['total_amount'], 2) . "\n" .
        "We'll notify you when your order ships."
    );
    
    $url = "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', $phone) . "&text=$message";
    
    // In a real app, you would use a WhatsApp API service to send this automatically
    // For now, we'll log it
    error_log("WhatsApp message URL: $url");
}

function createAdminNotification($orderId) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO AdminNotifications (order_id, message, is_read) 
                           VALUES (?, ?, 0)");
    $stmt->execute([
        $orderId,
        "New order #$orderId received"
    ]);
}
?>