<?php
require_once "db_connection.php"; 
if (isset($_GET['date']) && isset($_GET['time'])) {
    $selectedDate = $_GET['date'];
    $selectedTime = $_GET['time'];

    // Extract start time from the selected time slot (e.g., "08:00 AM-08:30 AM" -> "08:00 AM")
    $startTime = explode('-', $selectedTime)[0];
    $startTime = date('H:i:s', strtotime($startTime)); // Convert to 24-hour format

    // Check if the time slot is already booked (replace 1 with actual dentist ID)
    $sql = "SELECT COUNT(*) FROM appointments 
            WHERE appointment_date = ? 
            AND appointment_time = ? 
            AND dentist_id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $selectedDate, $startTime);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo 'occupied';
    } else {
        echo 'available';
    }

    $conn->close();
}
?>