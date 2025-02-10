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
    try {
        // Check if required data is present
        if (!isset($_POST['appointment_id']) || !isset($_POST['cancel_reason'])) {
            throw new Exception('Missing required data');
        }

        $appointment_id = intval($_POST['appointment_id']);
        $cancel_reason = trim($_POST['cancel_reason']);

        // Start transaction
        $conn->begin_transaction();

        // Update appointment status and add cancellation reason
        $stmt = $conn->prepare(
            "UPDATE appointments 
             SET status = 'Cancelled', 
                 cancel_reason = ?,
                 cancelled_at = CURRENT_TIMESTAMP 
             WHERE appointment_id = ?"
        );

        $stmt->bind_param("si", $cancel_reason, $appointment_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update appointment");
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully'
        ]);

    } catch (Exception $e) {
        // Rollback on error
        if ($conn->connect_errno) {
            $conn->rollback();
        }

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}