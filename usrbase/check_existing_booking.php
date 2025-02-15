<?php
require("../database/db_connection.php");
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$date = $_POST['date'] ?? null;

if (!$date) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

// Check for existing appointments on the same date
$stmt = $conn->prepare(
    "SELECT COUNT(*) as count 
     FROM appointments 
     WHERE user_id = ? 
     AND DATE(appointment_date) = ? 
     AND status IN ('confirmed', 'pending')"
);

$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'hasExistingBooking' => ($row['count'] > 0)
]);