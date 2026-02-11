<?php
// menu.php - Menu API
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
        $products = $stmt->fetchAll();
        echo JSON_encode($products);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo JSON_encode(['error' => $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Admin only for modifications
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo JSON_encode(['error' => 'Unauthorized']);
        exit;
    }

    $data = JSON_decode(file_get_contents('php://input'), true);

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, price, image_url, discount) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['price'],
                $data['image_url'],
                $data['discount'] ?? 0
            ]);
            echo JSON_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        }
        catch (PDOException $e) {
            echo JSON_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, image_url = ?, discount = ?, is_available = ? WHERE id = ?");
            $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['price'],
                $data['image_url'],
                $data['discount'] ?? 0,
                $data['is_available'] ?? 1,
                $data['id']
            ]);
            echo JSON_encode(['success' => true]);
        }
        catch (PDOException $e) {
            echo JSON_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo JSON_encode(['success' => true]);
        }
        catch (PDOException $e) {
            echo JSON_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
