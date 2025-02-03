<?php
include_once('database/db_connection.php');
header('Content-Type: application/json');

if (isset($_GET['date'])) {
    $date = $_GET['date'];
    
    // Get availability for the selected date
    $stmt = $conn->prepare("SELECT time_start, time_end FROM availability_tb WHERE available_date = ? AND is_active = 1");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $start_time = strtotime($row['time_start']);
        $end_time = strtotime($row['time_end']);
        
        $time_slots = array();
        
        // Generate 30-minute slots
        for ($time = $start_time; $time <= $end_time; $time += (30 * 60)) {
            $time_slots[] = date('H:i', $time);
        }
        
        // Get booked slots
        $stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status IN ('confirmed','pending')");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $booked = $stmt->get_result();
        $booked_slots = array();
        
        while ($book = $booked->fetch_assoc()) {
            $booked_slots[] = $book['appointment_time'];
        }
        
        echo json_encode([
            'available' => $time_slots,
            'booked' => $booked_slots
        ]);
    } else {
        echo json_encode(['error' => 'No availability found']);
    }
} else {
    echo json_encode(['error' => 'Date not provided']);
}
?>