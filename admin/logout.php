<?php
session_start();
include_once('database/db_connection.php');

// Log the logout activity
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_query = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
                  VALUES (?, 'LOGOUT', 'Admin logged out', ?)";
    $stmt = mysqli_prepare($conn, $log_query);
    mysqli_stmt_bind_param($stmt, "is", $admin_id, $ip_address);
    mysqli_stmt_execute($stmt);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: admin_login.php");
exit();
?>