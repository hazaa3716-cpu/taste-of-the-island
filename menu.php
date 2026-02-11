<?php
// menu.php - Menu API
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1");
    $products = $stmt->fetchAll();
    echo JSON_encode($products);
}
catch (PDOException $e) {
    http_response_code(500);
    echo JSON_encode(['error' => $e->getMessage()]);
}
?>
