<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token";
    header('Location: ../flowers_covers.php');
    exit;
}

// Validate inputs
$required = ['name', 'color', 'price_per_unit', 'season'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Please fill all required fields";
        header('Location: ../flowers_covers.php');
        exit;
    }
}

try {
    $pdo = Database::connect();
    $pdo->beginTransaction();

    // Handle file upload
    $imagePath = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/flowers/';
        $fileExt = pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION);
        $fileName = 'flower_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image_url']['tmp_name'], $targetPath)) {
            $imagePath = $fileName;
        } else {
            throw new Exception("Failed to upload image");
        }
    } else {
        throw new Exception("Image is required");
    }

    // Insert flower
    $stmt = $pdo->prepare("
        INSERT INTO Flowers (name, image_url, price_per_unit, color, season)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['name'],
        $imagePath,
        $_POST['price_per_unit'],
        $_POST['color'],
        $_POST['season']
    ]);
    
    $flowerId = $pdo->lastInsertId();

    // Save Arabic translations
    if (!empty($_POST['name_ar'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Translations (entity_type, entity_id, field_name, language_code, translated_text)
            VALUES ('flower', ?, 'name', 'ar', ?)
            ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)
        ");
        $stmt->execute([$flowerId, $_POST['name_ar']]);
    }
    
    if (!empty($_POST['color_ar'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Translations (entity_type, entity_id, field_name, language_code, translated_text)
            VALUES ('flower', ?, 'color', 'ar', ?)
            ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)
        ");
        $stmt->execute([$flowerId, $_POST['color_ar']]);
    }

    $pdo->commit();
    $_SESSION['message'] = "Flower added successfully";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error adding flower: " . $e->getMessage();
}

header('Location: ../flowers_covers.php');
exit;
?>