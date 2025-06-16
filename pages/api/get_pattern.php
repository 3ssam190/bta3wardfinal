<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

try {
    $query = "SELECT * FROM BouquetPatterns ORDER BY name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $patterns
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>