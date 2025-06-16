<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $_SESSION['dark_mode'] = (bool)($data['dark_mode'] ?? false);
    echo json_encode(['success' => true]);
    exit;
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'Invalid request']);