<?php
// orders.php - Orders API
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    $data = JSON_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? null;
    $totalPrice = $data['totalPrice'] ?? 0;
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $items = $data['items'] ?? [];

    if (empty($items)) {
        echo JSON_encode(['success' => false, 'message' => 'No items in order']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $totalPrice, $phone, $address]);
        $orderId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }

        $pdo->commit();
        echo JSON_encode(['success' => true, 'orderId' => $orderId]);
    }
    catch (PDOException $e) {
        $pdo->rollBack();
        echo JSON_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    // For admin or user order history
    $userId = $_GET['userId'] ?? null;
    try {
        if ($userId) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
        }
        else {
            $stmt = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        }
        $orders = $stmt->fetchAll();
        echo JSON_encode($orders);
    }
    catch (PDOException $e) {
        echo JSON_encode(['error' => $e->getMessage()]);
    }
}
?>
