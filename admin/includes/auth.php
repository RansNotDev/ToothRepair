<?php
session_start();

// Database connection
require_once('database/db_connection.php');

function redirect($url) {
    header("Location: $url");
    exit();
}

// Common authentication checks
function checkLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('../login.php'); // Default login page
    }
}

// Admin-specific authentication
function checkAdminRole() {
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ../admin/login.php');
        exit();
    }

    // Optional: Add additional role checks if needed
    if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
        header('Location: unauthorized.php');
        exit();
    }
}
    
    // Verify admin status in database
    global $conn;
    $stmt = $conn->prepare("SELECT role FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        session_destroy();
        redirect('../admin_login.php?error=invalid_session');
    }

// User-specific authentication
function checkUserAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('../user_login.php');
    }
    
    // Verify user status in database
    global $conn;
    $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        session_destroy();
        redirect('../user_login.php?error=invalid_session');
    }
}

// Authorization functions
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function isUser() {
    return isset($_SESSION['user_id']);
}

// Security functions
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auto-check CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
}

// Auto-logout after inactivity
$inactive = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
}
$_SESSION['last_activity'] = time();
?>