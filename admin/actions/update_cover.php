<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token";
    header('Location: ../flowers_covers.php');
    exit;
}

// Validate inputs
$required = ['cover_id', 'name', 'price'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Please fill all required fields";
        header('Location: ../flowers_covers.php');
        exit;
    }
}

$coverId = intval($_POST['cover_id']);

try {
    $pdo = Database::connect();
    $pdo->beginTransaction();

    // Handle file upload if new image is provided
    $imagePath = null;
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/covers/';
        $fileExt = pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION);
        $fileName = 'cover_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image_url']['tmp_name'], $targetPath)) {
            $imagePath = $fileName;
            
            // Get old image path to delete it later
            $stmt = $pdo->prepare("SELECT image_url FROM BouquetCovers WHERE id = ?");
            $stmt->execute([$coverId]);
            $oldImage = $stmt->fetchColumn();
        } else {
            throw new Exception("Failed to upload image");
        }
    }

    // Update cover
    if ($imagePath) {
        $stmt = $pdo->prepare("
            UPDATE BouquetCovers 
            SET name = ?, image_url = ?, price = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $imagePath,
            $_POST['price'],
            $_POST['description'] ?? null,
            $coverId
        ]);
        
        // Delete old image
        if ($oldImage) {
            $oldImagePath = $uploadDir . $oldImage;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    } else {
        $stmt = $pdo->prepare("
            UPDATE BouquetCovers 
            SET name = ?, price = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['price'],
            $_POST['description'] ?? null,
            $coverId
        ]);
    }

    // Save Arabic translations
    if (isset($_POST['name_ar'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Translations (entity_type, entity_id, field_name, language_code, translated_text)
            VALUES ('bouquet', ?, 'name', 'ar', ?)
            ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)
        ");
        $stmt->execute([$coverId, $_POST['name_ar']]);
    }
    
    if (isset($_POST['description_ar'])) {
        $stmt = $pdo->prepare("
            INSERT INTO Translations (entity_type, entity_id, field_name, language_code, translated_text)
            VALUES ('bouquet', ?, 'description', 'ar', ?)
            ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)
        ");
        $stmt->execute([$coverId, $_POST['description_ar']]);
    }

    $pdo->commit();
    $_SESSION['message'] = "Bouquet cover updated successfully";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error updating bouquet cover: " . $e->getMessage();
}

header('Location: ../flowers_covers.php');
exit;
?>