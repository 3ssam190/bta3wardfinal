<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT * FROM BouquetCovers");
    $stmt->execute();
    $covers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($covers);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}