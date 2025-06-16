<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::connect();
    
    $productId = intval($_GET['id'] ?? 0);
    
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            (SELECT translated_text FROM Translations 
             WHERE entity_type = 'product' 
             AND entity_id = p.product_id 
             AND field_name = 'name' 
             AND language_code = 'ar' LIMIT 1) AS name_ar,
            (SELECT translated_text FROM Translations 
             WHERE entity_type = 'product' 
             AND entity_id = p.product_id 
             AND field_name = 'description' 
             AND language_code = 'ar' LIMIT 1) AS description_ar,
            (SELECT translated_text FROM Translations 
             WHERE entity_type = 'product' 
             AND entity_id = p.product_id 
             AND field_name = 'environment_suitability' 
             AND language_code = 'ar' LIMIT 1) AS environment_suitability_ar,
            (SELECT translated_text FROM Translations 
             WHERE entity_type = 'product' 
             AND entity_id = p.product_id 
             AND field_name = 'care_instructions' 
             AND language_code = 'ar' LIMIT 1) AS care_instructions_ar
        FROM Products p
        WHERE p.product_id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'error' => 'Product not found'
        ]);
        exit;
    }
    
    // Get product images
    $stmt = $pdo->prepare("SELECT * FROM ProductImages WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $product
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
