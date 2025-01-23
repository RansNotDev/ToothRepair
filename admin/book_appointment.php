<?php
require_once 'db_connection.php';

$date = $_POST['date'];
$type = $_POST['type'];
$time = $_POST['time'];

$query = "INSERT INTO appointments (date, type, time) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $date, $type, $time);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to book appointment.']);
}
?>