<?php
session_start();

// Replace with your actual public IP
$allowed_ip = '143.44.216.57';

if ($_SERVER['REMOTE_ADDR'] !== $allowed_ip) {
    // Log unauthorized access attempt (optional)
    error_log("Unauthorized access attempt from: " . $_SERVER['REMOTE_ADDR']);
    die("Access Denied: Unauthorized Device.");
}

// Redirect to admin login if the IP is correct
header("Location: admin_login.php");
exit;
?>