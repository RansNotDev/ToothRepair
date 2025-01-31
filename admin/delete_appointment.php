<?php
session_start();
require_once('../includes/auth.php');
checkAdminRole();

if (!isset($_POST['id']) || !isset($_POST['csrf_token'])) {
    die("Invalid request");
}

// Validate CSRF token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
}

include('../database/db_connection.php');
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $_POST['id']);

if ($stmt->execute()) {
    header('Location: appointmentlist.php?success=3');
} else {
    die("Error deleting appointment: " . $conn->error);
}