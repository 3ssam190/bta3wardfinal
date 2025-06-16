<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
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
$product_id = intval($_POST['product_id']);
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$category_id = intval($_POST['category_id']);
$price = floatval($_POST['price']);
$stock_quantity = intval($_POST['stock_quantity']);
$environment_suitability = trim($_POST['environment_suitability']);
$care_instructions = trim($_POST['care_instructions']);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$delete_images = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];
$set_primary = isset($_POST['set_primary']) ? intval($_POST['set_primary']) : null;

// Basic validation
if (empty($name) || empty($description) || $price <= 0 || $stock_quantity < 0) {
    $_SESSION['error'] = "Please fill all required fields with valid data";
    header('Location: products.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Update product in database
    $update_query = "UPDATE Products SET 
                    name = ?, 
                    description = ?, 
                    category_id = ?, 
                    price = ?, 
                    stock_quantity = ?, 
                    environment_suitability = ?, 
                    care_instructions = ?, 
                    is_featured = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE product_id = ?";

    $stmt = $conn->prepare($update_query);
    $stmt->execute([
        $name, 
        $description, 
        $category_id, 
        $price, 
        $stock_quantity,
        $environment_suitability, 
        $care_instructions, 
        $is_featured, 
        $product_id
    ]);
     $ar_name = trim($_POST['name_ar'] ?? '');
    $ar_description = trim($_POST['description_ar'] ?? '');
    $ar_environment = trim($_POST['environment_suitability_ar'] ?? '');
    $ar_care = trim($_POST['care_instructions_ar'] ?? '');

    $translation_fields = [
        'name' => $ar_name,
        'description' => $ar_description,
        'environment_suitability' => $ar_environment,
        'care_instructions' => $ar_care
    ];

    foreach ($translation_fields as $field => $text) {
        // Check if translation exists
        $check_query = "SELECT translation_id FROM Translations 
                       WHERE entity_type = 'product' 
                       AND entity_id = ? 
                       AND field_name = ? 
                       AND language_code = 'ar'";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([$product_id, $field]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing translation
            $update_query = "UPDATE Translations SET translated_text = ? 
                           WHERE translation_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->execute([$text, $exists['translation_id']]);
        } else if (!empty($text)) {
            // Insert new translation
            $insert_query = "INSERT INTO Translations 
                           (entity_type, entity_id, field_name, language_code, translated_text) 
                           VALUES ('product', ?, ?, 'ar', ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->execute([$product_id, $field, $text]);
        }
    }

    // Handle image deletions
    if (!empty($delete_images)) {
        // Get image paths before deletion
        $select_query = "SELECT image_url FROM ProductImages WHERE image_id IN (" 
                      . implode(',', array_fill(0, count($delete_images), '?')) 
                      . ")";
        $stmt = $conn->prepare($select_query);
        $stmt->execute($delete_images);
        $images_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Delete from database
        $delete_query = "DELETE FROM ProductImages WHERE image_id IN (" 
                      . implode(',', array_fill(0, count($delete_images), '?')) 
                      . ")";
        $stmt = $conn->prepare($delete_query);
        $stmt->execute($delete_images);

        // Delete files from server
        $upload_dir = __DIR__ . '/../assets/images/products/';
        foreach ($images_to_delete as $image) {
            $file_path = $upload_dir . $image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    // Set primary image
    if ($set_primary) {
        // First reset all to non-primary
        $reset_query = "UPDATE ProductImages SET is_primary = 0 WHERE product_id = ?";
        $stmt = $conn->prepare($reset_query);
        $stmt->execute([$product_id]);

        // Set the selected one as primary
        $primary_query = "UPDATE ProductImages SET is_primary = 1 WHERE image_id = ? AND product_id = ?";
        $stmt = $conn->prepare($primary_query);
        $stmt->execute([$set_primary, $product_id]);
    }

    // Handle new image uploads
    if (!empty($_FILES['new_images']['name'][0])) {
        $upload_dir = __DIR__ . '/../assets/images/products/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $has_primary = $conn->query("SELECT 1 FROM ProductImages WHERE product_id = $product_id AND is_primary = 1")->fetchColumn();

        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['new_images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file_name = $_FILES['new_images']['name'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_types)) {
                continue;
            }

            $new_name = "product_{$product_id}_" . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_name;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                $is_primary = !$has_primary ? 1 : 0;
                $has_primary = true;
                
                $image_query = "INSERT INTO ProductImages (product_id, image_url, is_primary) 
                              VALUES (?, ?, ?)";
                $stmt = $conn->prepare($image_query);
                $stmt->execute([$product_id, $new_name, $is_primary]);
            }
        }
    }

    $conn->commit();
    $_SESSION['message'] = "Product updated successfully";
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