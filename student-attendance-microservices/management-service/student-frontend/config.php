<?php
// config.php - Configuration file for PHP frontend
session_start();

// API Configuration
define('API_BASE_URL', 'http://student-management-service:3001'); // Change to your Node.js service URL
define('API_TIMEOUT', 30); // API request timeout in seconds

// Database Configuration (for PHP authentication only)
define('DB_HOST', 'mysql');
define('MYSQL_NAME', 'attendancemsystem');
define('MYSQL_USER', 'attendance_user');
define('MYSQL_PASS', 'attendance_pass');

// Application Configuration
define('APP_NAME', 'Student Attendance Management System');
define('APP_VERSION', '1.0.0');

// Authentication Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 6);

// Error Reporting (set to false in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Database connection for PHP authentication
 */
function getDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4",
            MYSQL_USER,
            MYSQL_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Require authentication for protected pages
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current user information
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Check if user has required role
 */
function hasRole($requiredRole) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $roleHierarchy = ['staff' => 1, 'teacher' => 2, 'admin' => 3];
    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
