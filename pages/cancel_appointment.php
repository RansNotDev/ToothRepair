<?php
require_once("../database/db_connection.php");
require("../phpmailer/PHPMailer.php");
require("../phpmailer/SMTP.php");
require("../phpmailer/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

function sendCancellationEmail($userEmail, $appointmentDetails) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com'; // Your Gmail
        $mail->Password = 'evwt cpxf ywtl zytp'; // Your Gmail App Password
        $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('toothrepairdentalclinic@gmail.com');

    $mail->addAddress($_POST['email']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Cancellation Confirmation';
        $mail->Body = "
            <h2>Appointment Cancellation Confirmation</h2>
            <p>Your appointment has been cancelled:</p>
            <ul>
                <li>Date: {$appointmentDetails['date']}</li>
                <li>Time: {$appointmentDetails['time']}</li>
                <li>Service: {$appointmentDetails['service']}</li>
                <li>Cancellation Reason: {$appointmentDetails['reason']}</li>
            </ul>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $cancel_reason = $_POST['cancel_reason'] ?? '';

    if (!$appointment_id) {
        echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
        exit;
    }

    // Prepare the update data
    $appointmentValues = [];
    $appointmentTypes = '';
    $appointmentUpdates = [];

    // Add status update
    $appointmentUpdates[] = "status = ?";
    $appointmentValues[] = 'Cancelled';
    $appointmentTypes .= 's';

    // Add cancel reason if provided
    if (!empty($cancel_reason)) {
        $appointmentUpdates[] = "cancel_reason = ?";
        $appointmentValues[] = $cancel_reason;
        $appointmentTypes .= 's';
    }

    try {
        $conn->begin_transaction();

        // Update appointment status
        $sql = "UPDATE appointments SET " . implode(', ', $appointmentUpdates) . 
               " WHERE appointment_id = ?";
        $appointmentValues[] = $appointment_id;
        $appointmentTypes .= 'i';
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($appointmentTypes, ...$appointmentValues);
        $stmt->execute();

        // Get appointment details for email notification
        $query = "SELECT u.email, u.fullname, a.appointment_date, a.appointment_time, s.service_name 
                 FROM appointments a 
                 JOIN users u ON a.user_id = u.user_id 
                 JOIN services s ON a.service_id = s.service_id 
                 WHERE a.appointment_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment = $result->fetch_assoc();

        $conn->commit();

        // Send email notification (you can implement this part)
        // sendCancellationEmail($appointment['email'], $appointment);

        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully',
            'appointment' => [
                'date' => $appointment['appointment_date'],
                'time' => $appointment['appointment_time'],
                'service' => $appointment['service_name']
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Cancel appointment error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error cancelling appointment: ' . $e->getMessage()
        ]);
    }
    exit;
}