<?php
// auth.php - Authentication API (Updated for food_ordering_db)
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // 'username' field from frontend is used as Email for users, Username for admin
    $loginInput = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if ($action === 'register') {
        // Registration is only for Customers (Users table)
        if (empty($loginInput) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            exit;
        }

        // Simple name extraction from email unique part for now, or use input if we added name field to form
        $name = explode('@', $loginInput)[0];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            // New schema: users(name, email, password)
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $loginInput, $hashedPassword]);
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        }
        catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . ($e->getCode() == 23000 ? 'Email already registered' : $e->getMessage())]);
        }
    }
    elseif ($action === 'login') {
        try {
            // 1. Try Admin Login first
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$loginInput]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role'] = 'admin'; // Explicitly set role
                echo json_encode(['success' => true, 'user' => ['id' => $admin['id'], 'username' => $admin['username'], 'role' => 'admin']]);
                exit;
            }

            // 2. Try User Login (using Email)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$loginInput]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name']; // Use name as username for display
                $_SESSION['role'] = 'user';
                echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['name'], 'role' => 'user']]);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            }
        }
        catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Login error']);
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'verify') {
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'], // This will be 'name' for users
                    'role' => $_SESSION['role']
                ]
            ]);
        }
        else {
            echo json_encode(['authenticated' => false]);
        }
    }
    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'list_users') {
        // Admin only endpoint
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $stmt = $pdo->query("SELECT id, name as username, email, created_at FROM users");
        echo json_encode($stmt->fetchAll());
    }
}
?>
