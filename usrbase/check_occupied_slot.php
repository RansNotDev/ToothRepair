<?php
require("../database/db_connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE appointment_date = ? 
              AND appointment_time = ? 
              AND status IN ('pending', 'confirmed')";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode(['occupied' => ($row['count'] > 0)]);
}
?>