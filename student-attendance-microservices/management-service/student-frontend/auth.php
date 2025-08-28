<?php
// auth.php - Authentication functions
require_once 'config.php';

/**
 * Login user
 */
function loginUser($username, $password) {
    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("SELECT Id, firstName, emailAddress, password FROM tbladmin WHERE firstName = ? OR emailAddress = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
/*
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
	}*/

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['username'] = $user['firstName'];
        $_SESSION['email'] = $user['emailAddress'];
        //$_SESSION['role'] = $user['role']; // Column 'role' is not in tbladmin
        //$_SESSION['last_activity'] = time(); // This is a good practice, but not a column in tbladmin

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
        $stmt = $pdo->prepare("SELECT Id FROM tbladmin WHERE firstName = ? OR emailAddress = ?");
        $stmt->execute([$userData['firstName'], $userData['emailAddress']]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO tbladmin (firstName, emailAddress, password) VALUES (?, ?, ?)");
        $stmt->execute([
            $userData['firstName'],
            $userData['emailAddress'],
            $passwordHash
        ]);

        return ['success' => true, 'message' => 'User registered successfully'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
    }
}
?>
