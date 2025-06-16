<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Not logged in']);
    exit;
}

$adminId = $_SESSION['admin_id'];

try {
    $stmt = $pdo->prepare("
        SELECT n.*,
               CASE n.type
                   WHEN 'order' THEN 'New order received'
                   WHEN 'user' THEN 'New user registered'
                   WHEN 'product' THEN 'Product low in stock'
                   WHEN 'system' THEN 'System update available'
                   ELSE 'General notification'
               END AS message
        FROM Notifications n
        WHERE n.admin_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$adminId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
