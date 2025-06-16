<?php
require_once __DIR__ . '/Database.php';

// Establish connection
try {
    $pdo = Database::connect();
} catch (RuntimeException $e) {
    // Handle error appropriately (don't show details to users in production)
    die("Database connection error. Please contact administrator.");
}