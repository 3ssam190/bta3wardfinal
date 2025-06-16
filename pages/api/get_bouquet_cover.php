<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID parameter required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM BouquetCovers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $cover = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($cover ?: ['error' => 'Cover not found']);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}