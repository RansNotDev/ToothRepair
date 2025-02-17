<?php
include_once('database/db_connection.php');
date_default_timezone_set('Asia/Manila');

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

header('Content-Type: application/json');

// Helper function to generate random password
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Email sending function
function sendNewBookingEmail($userDetails, $appointmentDetails, $password) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'toothrepairdentalclinic@gmail.com';
        $mail->Password   = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('toothrepairdentalclinic@gmail.com', 'Tooth Repair Dental Clinic');
        $mail->addAddress($userDetails['email']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Tooth Repair - Your Appointment Details';

        // Email template
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2>Welcome to Tooth Repair Dental Clinic!</h2>
            <p>Dear {$userDetails['fullname']},</p>
            
            <h3>Your Appointment Details:</h3>
            <ul>
                <li>Date: {$appointmentDetails['date']}</li>
                <li>Time: {$appointmentDetails['time']}</li>
                <li>Service: {$appointmentDetails['service']}</li>
            </ul>

            <h3>Your Login Credentials:</h3>
            <p>Username: {$userDetails['email']}</p>
            <p>Password: {$password}</p>
            
            <p><strong>Please login to your account to manage your appointments:</strong><br>
            <a href='http://localhost/ToothRepair/usrbase/entryvault.php'>Click here to login</a></p>

            <div style='background-color: #f8f9fa; padding: 15px; margin-top: 20px;'>
                <h4>Important Reminders:</h4>
                <ul>
                    <li>Please arrive 10 minutes before your appointment</li>
                    <li>Bring valid ID and relevant medical records</li>
                    <li>24-hour notice required for cancellations</li>
                </ul>
            </div>
        </div>";

        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'check_user') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        $query = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo json_encode([
            'exists' => $result->num_rows > 0
        ]);
        exit;
    } else {
        // Handle appointment booking
        $required_fields = [
            'fullname', 
            'email', 
            'contact_number',
            'address',
            'service',
            'appointment_date',
            'appointment_time'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
                exit;
            }
        }

        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
        $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
        $service = mysqli_real_escape_string($conn, $_POST['service']);

        // Check appointment slot availability
        $checkSlot = mysqli_query($conn, 
            "SELECT COUNT(*) as count FROM appointments 
             WHERE appointment_date = '$appointment_date' 
             AND appointment_time = '$appointment_time' 
             AND status IN ('confirmed', 'pending')"
        );
        $slotCount = mysqli_fetch_assoc($checkSlot)['count'];

        if ($slotCount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This time slot is no longer available']);
            exit;
        }

        try {
            // Start transaction
            mysqli_begin_transaction($conn);

            // Check if email exists
            $checkEmail = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email'");
            $existingUser = mysqli_fetch_assoc($checkEmail);
            
            if ($existingUser) {
                $userId = $existingUser['user_id'];
            } else {
                // Generate password for new user
                $plainPassword = generateRandomPassword(10);
                $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

                // Create user account
                $userQuery = "INSERT INTO users (fullname, email, password, contact_number, address) 
                            VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $userQuery);
                mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $hashedPassword, $contact_number, $address);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create user account");
                }
                
                $userId = mysqli_insert_id($conn);
            }

            // Insert appointment
            $appointmentQuery = "INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, status) 
                               VALUES (?, ?, ?, ?, 'pending')";
            $stmt = mysqli_prepare($conn, $appointmentQuery);
            mysqli_stmt_bind_param($stmt, "iiss", $userId, $service, $appointment_date, $appointment_time);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create appointment");
            }

            // Only send email after successful database operations
            if (!$existingUser) {
                // Format time and get service name for email
                $formattedTime = date("g:i A", strtotime($appointment_time));
                $formattedDate = date("F d, Y", strtotime($appointment_date));
                
                $serviceQuery = mysqli_query($conn, "SELECT service_name FROM services WHERE service_id = '$service'");
                $serviceData = mysqli_fetch_assoc($serviceQuery);
                $serviceName = $serviceData['service_name'];

                $userDetails = [
                    'fullname' => $fullname,
                    'email' => $email
                ];

                $appointmentDetails = [
                    'date' => $formattedDate,
                    'time' => $formattedTime,
                    'service' => $serviceName
                ];

                // Email sending for new user
                $emailSent = sendNewBookingEmail($userDetails, $appointmentDetails, $plainPassword);
                
                if (!$emailSent) {
                    error_log("Failed to send email to: $email");
                    // Continue with the transaction even if email fails
                }
            }

            // If we get here, everything succeeded
            mysqli_commit($conn);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Appointment booked successfully!'
            ]);

        } catch (Exception $e) {
            // Rollback all database changes
            mysqli_rollback($conn);
            
            error_log("Appointment booking error: " . $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to book appointment: ' . $e->getMessage()
            ]);
        }
    }
}

// Invalid request
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>