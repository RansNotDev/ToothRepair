<?php
session_start();
require("../database/db_connection.php");
require '../vendor/autoload.php'; // Changed to use Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

function sendCancellationEmail($userEmail, $appointmentDetails) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com';
        $mail->Password = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('toothrepairdentalclinic@gmail.com', 'ToothRepair Dental Clinic');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Cancellation Confirmation';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background-color: #4e73df; color: white; padding: 20px; text-align: center; border-radius: 8px;'>
                    <h2 style='margin: 0;'>Appointment Cancellation Confirmation</h2>
                </div>
                <div style='background-color: #f8f9fc; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                    <p>Your appointment has been cancelled successfully:</p>
                    <ul style='list-style: none; padding: 0;'>
                        <li style='margin-bottom: 10px;'><strong>Date:</strong> {$appointmentDetails['date']}</li>
                        <li style='margin-bottom: 10px;'><strong>Time:</strong> {$appointmentDetails['time']}</li>
                        <li style='margin-bottom: 10px;'><strong>Service:</strong> {$appointmentDetails['service']}</li>
                        <li style='margin-bottom: 10px;'><strong>Cancellation Reason:</strong> {$appointmentDetails['reason']}</li>
                    </ul>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #666;'>
                    <p>Thank you for letting us know. We hope to serve you again soon.</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = mysqli_real_escape_string($conn, $_POST['appointment_id']);
    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);
    $user_id = $_SESSION['user_id'];

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Verify the appointment belongs to the user
        $check_query = "SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            throw new Exception('Invalid appointment');
        }

        // Update appointment status
        $update_query = "UPDATE appointments SET 
                        status = 'cancelled',
                        cancel_reason = ?,
                        cancelled_at = NOW()
                        WHERE appointment_id = ? AND user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sii", $cancel_reason, $appointment_id, $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to cancel appointment');
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Get appointment details for email
        $query = "SELECT a.appointment_date, a.appointment_time, s.service_name, u.email 
                  FROM appointments a
                  JOIN services s ON a.service_id = s.service_id
                  JOIN users u ON a.user_id = u.user_id
                  WHERE a.appointment_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $appointment_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $appointmentData = mysqli_fetch_assoc($result);

        $emailDetails = [
            'date' => date('F d, Y', strtotime($appointmentData['appointment_date'])),
            'time' => date('h:i A', strtotime($appointmentData['appointment_time'])),
            'service' => $appointmentData['service_name'],
            'reason' => $cancel_reason
        ];

        // Send cancellation email
        if (!sendCancellationEmail($appointmentData['email'], $emailDetails)) {
            // Log email failure but don't stop the process
            error_log("Failed to send cancellation email to: " . $appointmentData['email']);
        }

        $_SESSION['success_message'] = 'Appointment cancelled successfully';
        header('Location: userdashboard.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: userdashboard.php');
        exit;
    }
}

// Invalid request method
header('Location: userdashboard.php');
exit;
?>