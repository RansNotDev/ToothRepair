<?php
include_once('../database/db_connection.php');

// Validate input
$required = ['fullname', 'email', 'service', 'selectedTime', 'date'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("Missing required field: $field");
    }
}

// Check availability
$date = $_POST['date'];
$time = $_POST['selectedTime'];

$stmt = $conn->prepare("SELECT COUNT(*) FROM appointments 
                      WHERE appointment_date = ? AND appointment_time = ?");
$stmt->bind_param("ss", $date, $time);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    die("This time slot is no longer available");
}

// Insert appointment
$stmt = $conn->prepare("INSERT INTO appointments 
                      (user_id, service_id, appointment_date, appointment_time, status) 
                      VALUES (?, ?, ?, ?, 'pending')");
// Assuming you have user authentication - adjust as needed
$user_id = 1; // Get from session
$stmt->bind_param("iisss", 
    $user_id,
    $_POST['service'],
    $date,
    $time
);

if ($stmt->execute()) {
    echo "Appointment booked successfully";
} else {
    echo "Error booking appointment: " . $conn->error;
}
?>