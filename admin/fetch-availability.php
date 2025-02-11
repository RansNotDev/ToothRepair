<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$stmt = $conn->prepare("SELECT available_date, time_start, time_end FROM availability_tb");
$stmt->execute();
$result = $stmt->get_result();
$dates = [];

while ($row = $result->fetch_assoc()) {
    $dates[] = $row['available_date'];
}

echo json_encode($dates);
?>