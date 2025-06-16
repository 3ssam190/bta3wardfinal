<?php
require_once __DIR__ . '/../config.php';

// Enable maximum error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug incoming data
    error_log("===== UPLOAD DEBUG =====");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    $orderId = (int)$_POST['order_id'] ?? 0;
    $transactionId = $_POST['transaction_number'] ?? ''; // This will map to transaction_id in DB
    
    // Validate order exists - using correct status from your table
    $stmt = $conn->prepare("SELECT * FROM Payments WHERE order_id = ? AND payment_status = 'Pending' AND verification_expiry > NOW()");
    $stmt->execute([$orderId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        error_log("Invalid payment record for order $orderId");
        $_SESSION['error'] = "Invalid order or verification period expired";
        header("Location: order_history.php");
        exit();
    }
    
    // Handle file upload
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/payment_proofs/';
        
        // Verify and create directory if needed
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Directory creation failed: $uploadDir");
                $_SESSION['error'] = "System error: Could not create upload directory";
                header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
                exit();
            }
        }

        // Generate safe filename
        $filename = 'vodafone_' . $orderId . '_' . time() . '_' . preg_replace('/[^a-z0-9\.]/', '', strtolower(basename($_FILES['screenshot']['name'])));
        $targetFile = $uploadDir . $filename;
        
        // Verify file is an actual image
        $check = getimagesize($_FILES['screenshot']['tmp_name']);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image";
            header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
            exit();
        }
        
        // Check file size (2MB max)
        if ($_FILES['screenshot']['size'] > 5000000) {
            $_SESSION['error'] = "File is too large (max 5MB)";
            header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
            exit();
        }
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $_SESSION['error'] = "Only JPG, JPEG & PNG files are allowed";
            header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
            exit();
        }
        
        // Attempt to move file
        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetFile)) {
            // Update database with CORRECT FIELD NAMES
            $update = $conn->prepare("
                UPDATE Payments 
                SET payment_screenshot = ?,
                    transaction_id = ?,
                    payment_status = 'Pending'
                WHERE order_id = ?
            ");
            
            if ($update->execute([$filename, $transactionId, $orderId])) {
                $_SESSION['success'] = "Payment proof submitted successfully!";
                header("Location: order_details.php?order_id=".$orderId);
                exit();
            } else {
                error_log("Database error: " . print_r($conn->errorInfo(), true));
                $_SESSION['error'] = "Error saving payment details";
                header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
                exit();
            }
        } else {
            $errorCode = $_FILES['screenshot']['error'];
            $_SESSION['error'] = "Upload failed (Error: $errorCode)";
            header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
            exit();
        }
    } else {
        $_SESSION['error'] = "No file was uploaded";
        header("Location: vodafone_payment_instructions.php?order_id=".$orderId);
        exit();
    }
}

header("Location: /");
exit();