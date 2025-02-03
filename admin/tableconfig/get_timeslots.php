<?php
header('Content-Type: application/json');
include_once('../../database/db_connection.php');

try {
    if (!isset($_GET['date'])) {
        throw new Exception('Date is required');
    }

    $date = $_GET['date'];
    
    // Get availability
    $stmt = $conn->prepare("SELECT time_start, time_end FROM availability_tb WHERE available_date = ? AND is_active = 1");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $slots = [];
    if ($row = $result->fetch_assoc()) {
        $start = strtotime($row['time_start']);
        $end = strtotime($row['time_end']);
        
        // Generate 30-minute slots
        for ($time = $start; $time <= $end; $time += (30 * 60)) {
            $slots[] = date('H:i', $time);
        }
    }
    
    // Get booked slots
    $booked_stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ?");
    $booked_stmt->bind_param("s", $date);
    $booked_stmt->execute();
    $booked_result = $booked_stmt->get_result();
    
    $booked = [];
    while ($row = $booked_result->fetch_assoc()) {
        $booked[] = $row['appointment_time'];
    }
    
    echo json_encode([
        'slots' => $slots,
        'booked' => $booked
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>