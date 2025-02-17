<?php
require_once('../../database/db_connection.php');
require_once('../includes/mail_config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$appointment_id = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$appointment_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Get appointment details
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_date,
            TIME_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time,
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
    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        throw new Exception('Appointment not found');
    }

    // Prepare email content based on status
    $subject = match($status) {
        'cancelled' => 'Your Appointment Has Been Cancelled',
        'confirmed' => 'Your Appointment Has Been Confirmed',
        'completed' => 'Your Appointment Has Been Completed',
        'pending' => 'Your Appointment Status Update',
        default => 'Appointment Status Update'
    };

    $message = match($status) {
        'cancelled' => "
            <p>Dear {$appointment['fullname']},</p>
            <p>We regret to inform you that your appointment has been cancelled.</p>
            <p><strong>Appointment Details:</strong></p>
            <ul>
                <li>Service: {$appointment['service_name']}</li>
                <li>Date: {$appointment['appointment_date']}</li>
                <li>Time: {$appointment['appointment_time']}</li>
            </ul>
            <p>If you would like to reschedule, please contact us or book a new appointment through our website.</p>
            <p>We apologize for any inconvenience caused.</p>
        ",
        'confirmed' => "
            <p>Dear {$appointment['fullname']},</p>
            <p>Your appointment has been confirmed!</p>
            <p><strong>Appointment Details:</strong></p>
            <ul>
                <li>Service: {$appointment['service_name']}</li>
                <li>Date: {$appointment['appointment_date']}</li>
                <li>Time: {$appointment['appointment_time']}</li>
            </ul>
            <p>We look forward to seeing you!</p>
        ",
        'completed' => "
            <p>Dear {$appointment['fullname']},</p>
            <p>Your appointment has been marked as completed. Thank you for choosing our services!</p>
            <p><strong>Service Details:</strong></p>
            <ul>
                <li>Service: {$appointment['service_name']}</li>
                <li>Date: {$appointment['appointment_date']}</li>
                <li>Time: {$appointment['appointment_time']}</li>
            </ul>
            <p>We hope you had a great experience with us!</p>
        ",
        default => "
            <p>Dear {$appointment['fullname']},</p>
            <p>Your appointment status has been updated to: " . ucfirst($status) . "</p>
            <p><strong>Appointment Details:</strong></p>
            <ul>
                <li>Service: {$appointment['service_name']}</li>
                <li>Date: {$appointment['appointment_date']}</li>
                <li>Time: {$appointment['appointment_time']}</li>
            </ul>
        "
    };

    // Send email using PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    configureMailer($mail);
    
    $mail->addAddress($appointment['email']);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->isHTML(true);
    
    $mail->send();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send notification: ' . $e->getMessage()
    ]);
}
?>