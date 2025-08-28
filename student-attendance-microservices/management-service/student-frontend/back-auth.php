<?php
// auth.php - Authentication functions
require_once 'config.php';

/**
 * Login user
 */
function loginUser($username, $password) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT Id, firstname, emailAddress, password FROM tbladmin WHERE firstname = ? OR emailAddress = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['username'] = $user['firstname'];
        $_SESSION['email'] = $user['emailAddress'];
        //$_SESSION['role'] = $user['role'];
        //$_SESSION['last_activity'] = time();
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
    }
}

/**
 * Register new user (admin only)
 */
function registerUser($userData) {
    try {
        $pdo = getDbConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM tbladmin WHERE firstname = ? OR emailAddress = ?");
        $stmt->execute([$userData['username'], $userData['email']]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userData['username'],
            $userData['email'],
            $passwordHash,
            $userData['role'] ?? 'staff',
            $userData['status'] ?? 'active'
        ]);
        
        return ['success' => true, 'message' => 'User registered successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
    }
}
?>
