<?php
require_once 'db_connection.php';

$date = $_GET['date'];
$time = $_GET['time'];

$query = "SELECT COUNT(*) as count FROM appointments WHERE date = ? AND time = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $date, $time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo 'occupied';
} else {
    echo 'available';
}
?>