<?php
require_once('../../vendor/autoload.php');
require_once('../../database/db_connection.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get and decode JSON data
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    $required = ['email', 'name', 'date', 'time', 'service'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    $mail = new PHPMailer(true);

    // Debug mode
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer debug: $str");
    };

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'toothrepairdentalclinic@gmail.com';
    $mail->Password = 'evwt cpxf ywtl zytp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('toothrepairdentalclinic@gmail.com', 'Tooth Repair Dental Clinic');
    $mail->addAddress($data['email'], $data['name']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Appointment Reminder - Tooth Repair Dental Clinic';

    // Format date and time for display
    $formattedDate = date('F j, Y', strtotime($data['date']));
    $formattedTime = date('g:i A', strtotime($data['time']));

    // Email template
    $mail->Body = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
            Appointment Reminder
        </h2>
        
        <p>Dear {$data['name']},</p>
        
        <p>This is a reminder about your upcoming appointment at Tooth Repair Dental Clinic.</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Service:</strong> {$data['service']}</p>
            <p><strong>Date:</strong> {$formattedDate}</p>
            <p><strong>Time:</strong> {$formattedTime}</p>
        </div>

        <p>Please remember to:</p>
        <ul>
            <li>Arrive 10-15 minutes before your appointment</li>
            <li>Bring any relevant medical records</li>
            <li>Contact us if you need to reschedule</li>
        </ul>

        <p style="color: #7f8c8d; font-size: 0.9em; margin-top: 30px;">
            If you have any questions, please don't hesitate to contact us.<br>
            Thank you for choosing Tooth Repair Dental Clinic!
        </p>
    </div>
HTML;

    $mail->send();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Appointment reminder sent successfully'
    ]);

} catch (Exception $e) {
    error_log("Email Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error sending notification: ' . $e->getMessage()
    ]);
}
?>

<script>
$(document).ready(function() {
    // Notify button handler
    $('.notify-btn').on('click', function() {
        const btn = $(this);
        const data = {
            email: btn.data('email'),
            name: btn.data('name'),
            date: btn.data('date'),
            time: btn.data('time'),
            service: btn.data('service')
        };

        // Disable button while processing
        btn.prop('disabled', true);
        
        // Show loading state
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        // Send notification
        $.ajax({
            url: 'appointments/send_notification.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Notification sent successfully'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to send notification'
                });
                console.error('Error:', xhr.responseText);
            },
            complete: function() {
                // Reset button state
                btn.prop('disabled', false);
                btn.html(originalText);
            }
        });
    });
});
</script>

<style>
.notify-btn {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.notify-btn:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #000;
}

.notify-btn:disabled {
    background-color: #ffd754;
    border-color: #ffd754;
}
</style>