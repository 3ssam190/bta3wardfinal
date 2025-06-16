<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Save bouquet master record
    $stmt = $conn->prepare("
        INSERT INTO UserBouquets 
        (user_id, cover_id, flower_count, total_price) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $data['cover_id'],
        $data['flower_count'],
        $data['total_price']
    ]);
    $bouquetId = $conn->lastInsertId();
    
    // Save bouquet flowers
    $stmt = $conn->prepare("
        INSERT INTO BouquetFlowers 
        (bouquet_id, flower_id, quantity) 
        VALUES (?, ?, ?)
    ");
    
    foreach ($data['flowers'] as $flower) {
        $stmt->execute([$bouquetId, $flower['id'], $flower['quantity']]);
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'bouquet_id' => $bouquetId]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}