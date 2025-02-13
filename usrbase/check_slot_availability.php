<?php
require("../database/db_connection.php");
session_start();

if (!isset($_GET['date'])) {
    echo json_encode(['error' => 'Date parameter is required']);
    exit;
}

$date = $_GET['date'];

try {
    // First check if the date is available in availability_tb
    $avail_query = "SELECT time_start, time_end FROM availability_tb 
                    WHERE available_date = ? AND is_active = 1";
    $stmt = $conn->prepare($avail_query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['available' => false]);
        exit;
    }
    
    $availability = $result->fetch_assoc();
    
    // Get all booked slots for this date
    $booked_query = "SELECT appointment_time FROM appointments 
                     WHERE appointment_date = ? 
                     AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($booked_query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $booked_result = $stmt->get_result();
    
    $booked_slots = [];
    while ($row = $booked_result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_time'];
    }
    
    echo json_encode([
        'available' => true,
        'time_start' => $availability['time_start'],
        'time_end' => $availability['time_end'],
        'booked_slots' => $booked_slots
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred while checking availability']);
}