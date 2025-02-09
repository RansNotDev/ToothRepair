<?php
require("../database/db_connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book-appointment.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

try {
    // Check if user has pending or confirmed appointments
    $check_query = "SELECT COUNT(*) as count FROM appointments 
                   WHERE user_id = ? AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count >= 3) {
        $_SESSION['error'] = "You cannot book more than 3 active appointments.";
        header("Location: book-appointment.php");
        exit;
    }

    // Check if timeslot is still available
    $check_slot = "SELECT COUNT(*) as booked FROM appointments 
                  WHERE appointment_date = ? AND appointment_time = ? 
                  AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($check_slot);
    $stmt->bind_param("ss", $appointment_date, $appointment_time);
    $stmt->execute();
    $slot_result = $stmt->get_result();
    
    if ($slot_result->fetch_assoc()['booked'] > 0) {
        $_SESSION['error'] = "This time slot is no longer available.";
        header("Location: book-appointment.php");
        exit;
    }

    // Insert new appointment
    $insert_query = "INSERT INTO appointments (user_id, service_id, appointment_date, 
                    appointment_time, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiss", $user_id, $service_id, $appointment_date, $appointment_time);
    $stmt->execute();

    $_SESSION['success'] = "Appointment booked successfully!";
    header("Location: userdashboard.php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error booking appointment: " . $e->getMessage();
    header("Location: book-appointment.php");
    exit;
}
?>