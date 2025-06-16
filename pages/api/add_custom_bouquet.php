<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config.php';

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

try {
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = null; // Handle guest users
    }

    // Get input data
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($json, true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required = ['cover_id', 'flower_count', 'flowers', 'total_price'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate flower data
    if (!is_array($data['flowers']) || count($data['flowers']) === 0) {
        throw new Exception('Invalid flowers data');
    }

    // Process and save the bouquet image if provided
    $imagePath = null;
    if (!empty($data['image_data'])) {
        $imagePath = saveBouquetImage($data['image_data']);
    }

    // Get or create cart
    $userId = $_SESSION['user_id'];
    $sessionId = session_id();
    
    // First try to get existing cart
    $stmt = $conn->prepare("SELECT cart_id FROM Carts WHERE user_id = ? OR session_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$userId, $sessionId]);
    $cart = $stmt->fetch();
    
    if ($cart) {
        $cartId = $cart['cart_id'];
    } else {
        // Create new cart if none exists
        $stmt = $conn->prepare("INSERT INTO Carts (user_id, session_id) VALUES (?, ?)");
        $stmt->execute([$userId, $sessionId]);
        $cartId = $conn->lastInsertId();
    }

    // Prepare custom data with image path if available
    $customData = [
        'cover_id' => (int)$data['cover_id'],
        'flower_count' => (int)$data['flower_count'],
        'flowers' => array_map(function($flower) {
            return [
                'id' => (int)$flower['id'],
                'quantity' => (int)$flower['quantity']
            ];
        }, $data['flowers'])
    ];
    
    if ($imagePath) {
        $customData['image_path'] = $imagePath;
    }

    // Insert into CartItems
    $stmt = $conn->prepare("
        INSERT INTO CartItems 
        (cart_id, is_custom, custom_type, custom_data, quantity, price) 
        VALUES (?, 1, 'bouquet', ?, 1, ?)
    ");
    
    $success = $stmt->execute([
        $cartId,
        json_encode($customData),
        round((float)$data['total_price'], 2)
    ]);

    if (!$success) {
        throw new Exception('Failed to save bouquet to cart');
    }

    // Get updated cart count
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(quantity), 0) as count 
        FROM CartItems 
        WHERE cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $result = $stmt->fetch();
    
    // Update session
    $_SESSION['cart_count'] = (int)$result['count'];
    $_SESSION['cart_id'] = $cartId;
    
    $response = [
        'success' => true,
        'message' => 'Bouquet added to cart successfully',
        'cart_count' => (int)$result['count'],
        'image_path' => $imagePath // Return the image path if needed
    ];

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("PDO Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error: " . $e->getMessage());
}

echo json_encode($response);

/**
 * Saves the base64 encoded bouquet image to the server
 * 
 * @param string $base64Image Base64 encoded image data
 * @return string|null Path to the saved image or null on failure
 */
function saveBouquetImage($base64Image) {
    // Extract the image data from the base64 string
    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
        $imageType = $matches[1];
        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
        $imageData = base64_decode($imageData);
        
        if ($imageData === false) {
            error_log("Failed to decode base64 image data");
            return null;
        }
    } else {
        // If no prefix, assume it's raw base64
        $imageType = 'png'; // default to png
        $imageData = base64_decode($base64Image);
        
        if ($imageData === false) {
            error_log("Failed to decode raw base64 image data");
            return null;
        }
    }
    
    // Validate image type
    $allowedTypes = ['png', 'jpeg', 'jpg', 'gif'];
    if (!in_array(strtolower($imageType), $allowedTypes)) {
        error_log("Unsupported image type: $imageType");
        return null;
    }
    
    // Create directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../assets/images/bouquets/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = 'bouquet_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageType;
    $filePath = $uploadDir . $filename;
    
    // Save the image
    if (file_put_contents($filePath, $imageData)) {
        return '/assets/images/bouquets/' . $filename;
    }
    
    error_log("Failed to save image to $filePath");
    return null;
}