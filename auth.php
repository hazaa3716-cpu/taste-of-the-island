<?php
// auth.php - Authentication API
session_start();
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
            echo JSON_encode(['success' => false, 'message' => 'Registration failed: ' . ($e->getCode() == 23000 ? 'Username exists' : $e->getMessage())]);
        }
    }
    elseif ($action === 'login') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                unset($user['password']);
                echo JSON_encode(['success' => true, 'user' => $user]);
            }
            else {
                echo JSON_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        }
        catch (PDOException $e) {
            echo JSON_encode(['success' => false, 'message' => 'Login error']);
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'verify') {
        if (isset($_SESSION['user_id'])) {
            echo JSON_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ]);
        }
        else {
            echo JSON_encode(['authenticated' => false]);
        }
    }
    elseif ($action === 'logout') {
        session_destroy();
        echo JSON_encode(['success' => true]);
    }
    elseif ($action === 'list_users') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            exit;
        }
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users");
        echo JSON_encode($stmt->fetchAll());
    }
}
?>
