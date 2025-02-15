<?php
require("../database/db_connection.php");
header('Content-Type: application/json');

$date = $_GET['date'] ?? null;

if (!$date) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

// Get availability for the selected date
$stmt = $conn->prepare("SELECT time_start, time_end, max_daily_appointments 
                       FROM availability_tb 
                       WHERE available_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['available' => false]);
    exit;
}

$availability = $result->fetch_assoc();

// Get booked slots
$stmt = $conn->prepare("SELECT appointment_time 
                       FROM appointments 
                       WHERE appointment_date = ? 
                       AND status IN ('confirmed', 'pending')");
$stmt->bind_param("s", $date);
$stmt->execute();
$bookedResult = $stmt->get_result();

$bookedSlots = [];
while ($row = $bookedResult->fetch_assoc()) {
    $bookedSlots[] = $row['appointment_time'];
}

echo json_encode([
    'available' => true,
    'time_start' => $availability['time_start'],
    'time_end' => $availability['time_end'],
    'max_daily_appointments' => $availability['max_daily_appointments'],
    'booked_slots' => $bookedSlots
]);