<?php
require("../database/db_connection.php");
header('Content-Type: application/json');

$date = $_GET['date'] ?? null;

if (!$date) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

// Get availability and count existing appointments
$stmt = $conn->prepare("
    SELECT a.time_start, a.time_end, a.max_daily_appointments,
           COUNT(ap.appointment_id) as current_appointments
    FROM availability_tb a
    LEFT JOIN appointments ap ON ap.appointment_date = a.available_date 
         AND ap.status IN ('confirmed', 'pending')
    WHERE a.available_date = ?
    GROUP BY a.available_date, a.time_start, a.time_end, a.max_daily_appointments
");

$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['available' => false]);
    exit;
}

$availability = $result->fetch_assoc();

// Check if max daily appointments reached
if ($availability['current_appointments'] >= $availability['max_daily_appointments']) {
    echo json_encode(['available' => false, 'reason' => 'max_reached']);
    exit;
}

// Get booked slots
$stmt = $conn->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE appointment_date = ? 
    AND status IN ('confirmed', 'pending')
");
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
    'current_appointments' => $availability['current_appointments'],
    'booked_slots' => $bookedSlots
]);