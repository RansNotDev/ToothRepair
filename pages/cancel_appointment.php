<?php
require("../database/db_connection.php");
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

if (isset($_POST['appointment_id'])) {
    try {
        $appointment_id = $_POST['appointment_id'];
        $user_id = $_SESSION['user_id'];
        $cancel_reason = $_POST['cancel_reason'] ?? 'No reason provided';
        $current_time = date('Y-m-d H:i:s');

        // Get appointment and user details
        $stmt = $conn->prepare("
            SELECT a.*, s.service_name, u.email
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            JOIN users u ON a.user_id = u.user_id
            WHERE a.appointment_id = ? AND a.user_id = ?
        ");
        $stmt->bind_param("ii", $appointment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            exit;
        }

        $appointment = $result->fetch_assoc();

        if ($appointment['status'] === 'Cancelled') {
            echo json_encode(['success' => false, 'message' => 'Appointment is already cancelled']);
            exit;
        }

        // Update appointment status
        $update = $conn->prepare("
            UPDATE appointments 
            SET status = 'Cancelled',
                cancel_reason = ?,
                cancelled_at = ?
            WHERE appointment_id = ? AND user_id = ?
        ");
        $update->bind_param("ssii", $cancel_reason, $current_time, $appointment_id, $user_id);

        if ($update->execute()) {
            // Send email
            $emailDetails = [
                'date' => date('F d, Y', strtotime($appointment['appointment_date'])),
                'time' => date('h:i A', strtotime($appointment['appointment_time'])),
                'service' => $appointment['service_name'],
                'reason' => $cancel_reason
            ];

            $emailSent = sendCancellationEmail($appointment['email'], $emailDetails);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment cancelled successfully' . ($emailSent ? ' and notification sent' : ''),
                'appointment' => $emailDetails
            ]);
        } else {
            throw new Exception('Failed to update appointment status');
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No appointment ID provided']);
}