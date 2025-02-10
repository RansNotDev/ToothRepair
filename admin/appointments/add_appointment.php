<?php
include_once('../../database/db_connection.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

header('Content-Type: application/json');

function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Validate required fields
        $required = ['fullname', 'email', 'contact_number', 'address', 
                    'appointment_date', 'appointment_time', 'service_id', 'status'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate appointment date and time
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];

        // Check if timeslot is available
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) as count 
             FROM appointments 
             WHERE appointment_date = ? 
             AND appointment_time = ? 
             AND status NOT IN ('cancelled')"
        );
        $check_stmt->bind_param("ss", $appointment_date, $appointment_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("This time slot is already booked");
        }

        // Check if within availability
        $avail_stmt = $conn->prepare(
            "SELECT time_start, time_end 
             FROM availability_tb 
             WHERE available_date = ? 
             AND is_active = 1"
        );
        $avail_stmt->bind_param("s", $appointment_date);
        $avail_stmt->execute();
        $avail = $avail_stmt->get_result()->fetch_assoc();
        
        if (!$avail) {
            throw new Exception("No availability for selected date");
        }

        // Create or update user
        $plainPassword = generateRandomPassword();
        $default_password = password_hash($plainPassword, PASSWORD_BCRYPT);
        $user_stmt = $conn->prepare(
            "INSERT INTO users (fullname, email, contact_number, address, password) 
             VALUES (?, ?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE 
             contact_number = VALUES(contact_number),
             address = VALUES(address)"
        );
        $user_stmt->bind_param(
            "sssss", 
            $_POST['fullname'], 
            $_POST['email'], 
            $_POST['contact_number'], 
            $_POST['address'],
            $default_password
        );
        $user_stmt->execute();
        
        // Get user_id (whether inserted or existing)
        $user_id = $user_stmt->insert_id ?: $conn->query(
            "SELECT user_id FROM users WHERE email = '" . 
            $conn->real_escape_string($_POST['email']) . "'"
        )->fetch_object()->user_id;

        // Create appointment
        $stmt = $conn->prepare(
            "INSERT INTO appointments 
             (user_id, service_id, appointment_date, appointment_time, status) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iisss", 
            $user_id, 
            $_POST['service_id'], 
            $appointment_date, 
            $appointment_time,
            $_POST['status']
        );
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // After successful commit, send email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com';
        $mail->Password = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('toothrepairdentalclinic@gmail.com', 'Tooth Repair Dental Clinic');
        $mail->addAddress($_POST['email']);
        $mail->isHTML(true);

        // Get service name
        $service_stmt = $conn->prepare("SELECT service_name FROM services WHERE service_id = ?");
        $service_stmt->bind_param("i", $_POST['service_id']);
        $service_stmt->execute();
        $service_result = $service_stmt->get_result()->fetch_assoc();
        $service_name = $service_result['service_name'];

        $mail->Subject = 'Appointment Confirmation - Tooth Repair Dental Clinic';
        
        // Create email body
        $emailBody = "
        <h2>Appointment Confirmation</h2>
        <p>Dear {$_POST['fullname']},</p>
        <p>Your appointment has been successfully scheduled. Here are the details:</p>
        <ul>
            <li>Date: {$_POST['appointment_date']}</li>
            <li>Time: {$_POST['appointment_time']}</li>
            <li>Service: {$service_name}</li>
        </ul>
        <p>Your login credentials:</p>
        <ul>
            <li>Username: {$_POST['email']}</li>
            <li>Password: {$plainPassword}</li>
        </ul>
        <p><strong>Important:</strong> Please change your password immediately upon first login for security purposes.</p>
        <p>Thank you for choosing Tooth Repair Dental Clinic!</p>";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        $mail->send();

        // Modified success response with redirect URL
        echo json_encode([
            'success' => true,
            'message' => 'Appointment added successfully and confirmation email sent',
            'redirect' => '../appointmentlist.php'
        ]);
        exit; // Add this to prevent any additional output

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        // Log the full error details
        error_log("Appointment Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>

<script>
// Replace your existing form submission handler
$(document).ready(function() {
    $('#addAppointmentModal form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide modal first
                    $('#addAppointmentModal').modal('hide');
                    // Show success message
                    alert('Appointment added successfully!');
                    // Redirect to appointment list
                    window.location.href = '../appointmentlist.php';
                } else {
                    // Show specific error message if available
                    alert(response.error || 'Failed to add appointment');
                }
            },
            error: function(xhr) {
                // If the response is JSON, parse it
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.error || 'Failed to add appointment');
                } catch(e) {
                    // If not JSON, show generic error
                    alert('Failed to add appointment. Please try again.');
                }
                console.error('Server error:', xhr.responseText);
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>
