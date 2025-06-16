<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

try {
    $conn = Database::connect();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database connection failed: " . $e->getMessage();
    header('Location: products.php');
    exit;
}

// Validate and sanitize input
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$category_id = intval($_POST['category_id']);
$price = floatval($_POST['price']);
$stock_quantity = intval($_POST['stock_quantity']);
$environment_suitability = trim($_POST['environment_suitability']);
$care_instructions = trim($_POST['care_instructions']);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

// Basic validation
if (empty($name) || empty($description) || $price <= 0 || $stock_quantity < 0) {
    $_SESSION['error'] = "Please fill all required fields with valid data";
    header('Location: products.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Insert product into database
    $insert_query = "INSERT INTO Products (name, description, category_id, price, stock_quantity, 
                    environment_suitability, care_instructions, is_featured) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->execute([
        $name, 
        $description, 
        $category_id, 
        $price, 
        $stock_quantity,
        $environment_suitability, 
        $care_instructions, 
        $is_featured
    ]);

    $product_id = $conn->lastInsertId();
    $ar_name = trim($_POST['name_ar'] ?? '');
    $ar_description = trim($_POST['description_ar'] ?? '');
    $ar_environment = trim($_POST['environment_suitability_ar'] ?? '');
    $ar_care = trim($_POST['care_instructions_ar'] ?? '');

    $translation_fields = [
        'name' => $ar_name ?: $name, // Fallback to English if Arabic empty
        'description' => $ar_description ?: $description,
        'environment_suitability' => $ar_environment ?: $environment_suitability,
        'care_instructions' => $ar_care ?: $care_instructions
    ];

    foreach ($translation_fields as $field => $text) {
        if (!empty($text)) {
            $translation_query = "INSERT INTO Translations 
                                (entity_type, entity_id, field_name, language_code, translated_text) 
                                VALUES ('product', ?, ?, 'ar', ?)";
            $stmt = $conn->prepare($translation_query);
            $stmt->execute([$product_id, $field, $text]);
        }
    }

    // Handle file uploads
    if (!empty($_FILES['product_images']['name'][0])) {
    $upload_dir = __DIR__ . '/../assets/images/products/';
    
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    $primary_set = false;
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
        // Skip if upload error
        if ($_FILES['product_images']['error'][$key] !== UPLOAD_ERR_OK) {
            error_log("Upload error for file: " . $_FILES['product_images']['error'][$key]);
            continue;
        }

        // Validate file size
        if ($_FILES['product_images']['size'][$key] > $max_file_size) {
            error_log("File too large: " . $_FILES['product_images']['name'][$key]);
            continue;
        }

        // Validate file type
        $file_name = $_FILES['product_images']['name'][$key];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            error_log("Invalid file type: $file_ext");
            continue;
        }

        // Generate unique filename
        $new_name = "product_{$product_id}_" . bin2hex(random_bytes(8)) . '.' . $file_ext;
        $upload_path = $upload_dir . $new_name;

        // Validate file content
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp_name);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mime, $allowed_mimes)) {
            error_log("Invalid file mime type: $mime");
            continue;
        }

        // Move uploaded file
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Verify the image was actually saved
            if (!file_exists($upload_path)) {
                error_log("File move failed: $upload_path");
                continue;
            }
            
            // Insert image record
            $is_primary = !$primary_set ? 1 : 0;
            $primary_set = true;
            
            $image_query = "INSERT INTO ProductImages (product_id, image_url, is_primary) 
                           VALUES (?, ?, ?)";
            $img_stmt = $conn->prepare($image_query);
            $img_stmt->execute([$product_id, $new_name, $is_primary]);
            
        } else {
            error_log("Failed to move uploaded file: $tmp_name to $upload_path");
        }
    }
}

    $conn->commit();
    $_SESSION['message'] = "Product added successfully with images";
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header('Location: ../products.php');
exit;
?>