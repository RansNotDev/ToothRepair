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

try {
    // Start transaction
    $conn->begin_transaction();

    // Check availability and current appointment count
    $check_availability = "
        SELECT a.max_daily_appointments, 
               COUNT(ap.appointment_id) as current_appointments
        FROM availability_tb a
        LEFT JOIN appointments ap ON ap.appointment_date = a.available_date 
             AND ap.status IN ('confirmed', 'pending')
        WHERE a.available_date = ?
        GROUP BY a.available_date, a.max_daily_appointments";
    
    $stmt = $conn->prepare($check_availability);
    $stmt->bind_param("s", $appointment_date);
    $stmt->execute();
    $avail_result = $stmt->get_result();
    $availability = $avail_result->fetch_assoc();

    if (!$availability) {
        throw new Exception("Selected date is not available for booking.");
    }

    if ($availability['current_appointments'] >= $availability['max_daily_appointments']) {
        throw new Exception("Maximum appointments for this date have been reached.");
    }

    // Check if timeslot is already booked
    $check_slot = "SELECT COUNT(*) as booked 
                   FROM appointments 
                   WHERE appointment_date = ? 
                   AND appointment_time = ? 
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

    // Check if this is a rebooking
    $check_previous = "SELECT COUNT(*) as previous 
                      FROM appointments 
                      WHERE user_id = ? 
                      AND status IN ('completed', 'cancelled')";
    $stmt = $conn->prepare($check_previous);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $previous_result = $stmt->get_result();
    $is_rebooking = ($previous_result->fetch_assoc()['previous'] > 0);

    // Send confirmation email
    $emailSent = sendBookingConfirmationEmail(
        $user_details['email'],
        $_SESSION['fullname'],
        $service_details['service_name'],
        $appointment_date,
        $appointment_time,
        $is_rebooking
    );

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Appointment " . ($is_rebooking ? "rebooked" : "booked") . " successfully! A confirmation email has been sent to your email address.";
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