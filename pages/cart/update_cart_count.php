<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (isset($_GET['count'])) {
    $_SESSION['cart_count'] = (int)$_GET['count'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}