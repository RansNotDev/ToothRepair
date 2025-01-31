<?php
session_start();
include_once('../database/db_connection.php');

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// Fetch availability dates
$result = $conn->query("SELECT available_date FROM availability_tb");
$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['available_date'];
}

header('Content-Type: application/json');
echo json_encode($dates);
exit();
?>