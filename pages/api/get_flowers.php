<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

try {
    $query = "SELECT * FROM Flowers ORDER BY name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $flowers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $flowers
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>