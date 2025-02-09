<?php
include_once('../../database/db_connection.php');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Manila');

try {
    if (!isset($_GET['date'])) {
        throw new Exception('Date parameter is required');
    }

    $date = $_GET['date'];
    $current_time = $_GET['current_time'] ?? null;

    // Get availability for the selected date
    $stmt = $conn->prepare(
        "SELECT TIME_FORMAT(time_start, '%h:%i %p') as time_start, 
                TIME_FORMAT(time_end, '%h:%i %p') as time_end 
         FROM availability_tb 
         WHERE available_date = ? AND is_active = 1"
    );
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $availability = $result->fetch_assoc();

    if (!$availability) {
        echo json_encode(['error' => 'No availability for selected date']);
        exit;
    }

    // Generate time slots
    $start_time = strtotime($availability['time_start']);
    $end_time = strtotime($availability['time_end']);
    $time_slots = [];

    for ($time = $start_time; $time <= $end_time; $time += (30 * 60)) {
        $time_slots[] = date('h:i A', $time); // Changed to 12-hour format with AM/PM
    }

    // Get booked slots
    $stmt = $conn->prepare(
        "SELECT TIME_FORMAT(appointment_time, '%h:%i %p') as booked_time 
         FROM appointments 
         WHERE appointment_date = ? 
         AND status IN ('confirmed', 'pending')"
    );
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $booked_result = $stmt->get_result();
    $booked_slots = [];

    while ($row = $booked_result->fetch_assoc()) {
        if ($row['booked_time'] !== $current_time) {
            $booked_slots[] = $row['booked_time'];
        }
    }

    echo json_encode([
        'available' => $time_slots,
        'booked' => $booked_slots
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>