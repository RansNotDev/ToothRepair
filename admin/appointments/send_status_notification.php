<?php
require_once('../../database/db_connection.php');
require_once('../includes/mail_config.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    // Get appointment details
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_date,
            a.appointment_time,
            u.email,
            u.fullname,
            s.service_name
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN services s ON a.service_id = s.service_id
        WHERE a.appointment_id = ?
    ");

    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Only send email for confirmed status
        if ($status === 'confirmed') {
            $to = $row['email'];
            $subject = "Appointment Status Update - Confirmed";
            
            $message = "
                <html>
                <head>
                    <title>Appointment Confirmation</title>
                </head>
                <body>
                    <h2>Appointment Confirmed</h2>
                    <p>Dear {$row['fullname']},</p>
                    <p>Your appointment has been confirmed with the following details:</p>
                    <ul>
                        <li>Date: {$row['appointment_date']}</li>
                        <li>Time: {$row['appointment_time']}</li>
                        <li>Service: {$row['service_name']}</li>
                    </ul>
                    <p>Thank you for choosing our services.</p>
                </body>
                </html>
            ";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: your-email@domain.com" . "\r\n";

            mail($to, $subject, $message, $headers);
        }

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Appointment not found');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>