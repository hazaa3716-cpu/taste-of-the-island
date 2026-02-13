<?php
// menu.php - Menu API
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // New schema: products table has category field directly, no categories table
        $stmt = $pdo->query("SELECT id, name, price, category as category_name, image_url, is_available, description FROM products WHERE is_available = 1 ORDER BY id DESC");
        $products = $stmt->fetchAll();
        echo json_encode($products);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
