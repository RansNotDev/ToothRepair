<?php
include_once('../../database/db_connection.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once('../includes/send_email.php');
require_once('../includes/email_helper.php');

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

        // After successful appointment creation
        if ($stmt->execute()) {
            // Get service name for email
            $service_query = "SELECT service_name FROM services WHERE service_id = ?";
            $stmt = $conn->prepare($service_query);
            $stmt->bind_param("i", $_POST['service_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $service = $result->fetch_assoc();

            // Prepare appointment details for email
            $appointmentData = [
                'fullname' => $_POST['fullname'],
                'email' => $_POST['email'],
                'appointment_date' => $appointment_date,
                'appointment_time' => $appointment_time,
                'service_name' => $service['service_name'],
                'status' => $_POST['status']
            ];

            // Send email notification
            $emailSent = sendAppointmentEmail($_POST['email'], $appointmentData, 'new');
            
            $response = [
                'success' => true,
                'message' => 'Appointment created successfully'
            ];
            if (!$emailSent) {
                $response['emailError'] = 'Appointment created but email notification failed';
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create appointment']);
        }

        // Commit transaction
        $conn->commit();

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
