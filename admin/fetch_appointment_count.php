<?php
require_once 'db_connection.php';

$date = $_GET['date'];

$query = "SELECT COUNT(*) as count FROM appointments WHERE date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['count']]);
?>