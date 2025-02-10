<?php
require("../database/db_connection.php");
require("../includes/email_helper.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book-appointment.php");
    exit;
}

// Validate input data
if (empty($_POST['service_id']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: book-appointment.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

// Validate appointment date is in the future
if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = "Please select a future date.";
    header("Location: book-appointment.php");
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if user has pending or confirmed appointments
    $check_query = "SELECT COUNT(*) as count FROM appointments 
                   WHERE user_id = ? AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count >= 3) {
        throw new Exception("You cannot book more than 3 active appointments.");
    }

    // Check if timeslot is available in availability_tb
    $check_availability = "SELECT COUNT(*) as available FROM availability_tb 
                         WHERE available_date = ? AND is_active = 1";
    $stmt = $conn->prepare($check_availability);
    $stmt->bind_param("s", $appointment_date);
    $stmt->execute();
    $avail_result = $stmt->get_result();
    
    if ($avail_result->fetch_assoc()['available'] == 0) {
        throw new Exception("Selected date is not available for booking.");
    }

    // Check if timeslot is already booked
    $check_slot = "SELECT COUNT(*) as booked FROM appointments 
                  WHERE appointment_date = ? AND appointment_time = ? 
                  AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($check_slot);
    $stmt->bind_param("ss", $appointment_date, $appointment_time);
    $stmt->execute();
    $slot_result = $stmt->get_result();
    
    if ($slot_result->fetch_assoc()['booked'] > 0) {
        throw new Exception("This time slot is no longer available.");
    }

    // Get service details
    $service_query = "SELECT service_name FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($service_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service_result = $stmt->get_result();
    $service_details = $service_result->fetch_assoc();

    // Insert new appointment
    $insert_query = "INSERT INTO appointments (user_id, service_id, appointment_date, 
                    appointment_time, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiss", $user_id, $service_id, $appointment_date, $appointment_time);
    $stmt->execute();

    // Get user email
    $user_query = "SELECT email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_details = $user_result->fetch_assoc();

    // Send confirmation email
    $emailSent = sendBookingConfirmationEmail(
        $user_details['email'],
        $_SESSION['fullname'],
        $service_details['service_name'],
        $appointment_date,
        $appointment_time
    );

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Appointment booked successfully! A confirmation email has been sent to your email address.";
    header("Location: userdashboard.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = "Error booking appointment: " . $e->getMessage();
    header("Location: book-appointment.php");
    exit;
}
?>