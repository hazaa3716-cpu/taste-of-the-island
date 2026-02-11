<?php
// auth.php - Authentication API
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = JSON_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if ($action === 'register') {
        if (empty($username) || empty($password)) {
            echo JSON_encode(['success' => false, 'message' => 'Username and password required']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashedPassword]);
            echo JSON_encode(['success' => true, 'message' => 'Registration successful']);
        }
        catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo JSON_encode(['success' => false, 'message' => 'Username already exists']);
            }
            else {
                echo JSON_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
            }
        }
    }
    elseif ($action === 'login') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']); // Don't send password hash to frontend
                echo JSON_encode(['success' => true, 'user' => $user]);
            }
            else {
                echo JSON_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        }
        catch (PDOException $e) {
            echo JSON_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list_users') {
    try {
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users");
        $users = $stmt->fetchAll();
        echo JSON_encode($users);
    }
    catch (PDOException $e) {
        echo JSON_encode(['error' => $e->getMessage()]);
    }
}
?>
