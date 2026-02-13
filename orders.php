<?php
// orders.php - Orders API
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? null;
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    $totalAmount = $data['totalPrice'] ?? 0; // Frontend sends totalPrice
    $address = $data['address'] ?? '';
    $items = $data['items'] ?? [];

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No items in order']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // New schema: total_amount, delivery_address (no phone field)
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $totalAmount, $address]);
        $orderId = $pdo->lastInsertId();

        // New schema: price (not price_at_time)
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'orderId' => $orderId]);
    }
    catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    // For admin or user order history
    $userId = $_GET['userId'] ?? null;

    // Security check: Only admin can list all orders or specific user's orders (unless requester is that user)
    if (!isset($_SESSION['role'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Login required']);
        exit;
    }

    if ($_SESSION['role'] !== 'admin' && ($userId && $_SESSION['user_id'] != $userId)) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if ($_SESSION['role'] !== 'admin' && !$userId) {
        $userId = $_SESSION['user_id']; // Default to current user history
    }

    try {
        if ($userId) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
        }
        else {
            // New schema: users table has 'name' field (not username)
            $stmt = $pdo->query("SELECT o.*, u.name as username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        }
        $orders = $stmt->fetchAll();
        // Fix field name for frontend compatibility: total_amount -> total_price
        foreach ($orders as &$order) {
            $order['total_price'] = $order['total_amount'];
        }
        echo json_encode($orders);
    }
    catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);
        echo json_encode(['success' => true]);
    }
    catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
