<?php
header('Content-Type: application/json');
require '../../vendor/autoload.php'; // Make sure PHPMailer is installed via Composer
include_once('../../database/db_connection.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $appointment_id = $_POST['id'];
    $delete_reason = $_POST['delete_reason'] ?? '';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get appointment and user details
        $stmt = $conn->prepare("
            SELECT 
                a.appointment_date,
                DATE_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time,
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

        if ($appointment) {
            // Store the deletion record
            $stmt = $conn->prepare("
                INSERT INTO appointment_deletions 
                (appointment_id, delete_reason, deleted_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->bind_param("is", $appointment_id, $delete_reason);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'toothrepairdentalclinic@gmail.com'; // Your Gmail address
                $mail->Password = 'evwt cpxf ywtl zytp'; // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Changed from STARTTLS to SMTPS
                $mail->Port = 465; // Using port 465 for SSL
                $mail->SMTPDebug = 0; // Set to 2 for debugging

                // Recipients
                $mail->setFrom('toothrepairdentalclinic@gmail.com', 'ToothRepair Clinic'); // Changed from placeholder
                $mail->addAddress($appointment['email'], $appointment['fullname']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Appointment Cancellation Notice';
                
                // Email body
                $body = "
                <html>
                <head>
                    <style>
                        .email-container {
                            max-width: 600px;
                            margin: 0 auto;
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            color: #333333;
                            background-color: #f9f9f9;
                            padding: 20px;
                        }
                        .header {
                            background-color: #1a73e8;
                            color: white;
                            padding: 20px;
                            text-align: center;
                            border-radius: 5px 5px 0 0;
                        }
                        .content {
                            background-color: white;
                            padding: 20px;
                            border-radius: 0 0 5px 5px;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        }
                        .appointment-details {
                            background-color: #f5f5f5;
                            padding: 15px;
                            border-left: 4px solid #1a73e8;
                            margin: 15px 0;
                            border-radius: 4px;
                        }
                        .cancel-reason {
                            background-color: #fff3f3;
                            padding: 15px;
                            border-left: 4px solid #dc3545;
                            margin: 15px 0;
                            border-radius: 4px;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 20px;
                            padding-top: 20px;
                            border-top: 1px solid #eee;
                            color: #666;
                        }
                        ul {
                            list-style-type: none;
                            padding-left: 0;
                        }
                        li {
                            margin-bottom: 10px;
                        }
                        .contact-info {
                            background-color: #e8f0fe;
                            padding: 15px;
                            border-radius: 4px;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='header'>
                            <h2 style='margin:0;'>Appointment Cancellation Notice</h2>
                        </div>
                        <div class='content'>
                            <p>Dear {$appointment['fullname']},</p>
                            
                            <p>We regret to inform you that your appointment has been cancelled.</p>
                            
                            <div class='appointment-details'>
                                <h3 style='margin-top:0;color:#1a73e8;'>Appointment Details:</h3>
                                <ul>
                                    <li>📅 <strong>Date:</strong> {$appointment['appointment_date']}</li>
                                    <li>⏰ <strong>Time:</strong> {$appointment['appointment_time']}</li>
                                    <li>🦷 <strong>Service:</strong> {$appointment['service_name']}</li>
                                </ul>
                            </div>
                            
                            <div class='cancel-reason'>
                                <h3 style='margin-top:0;color:#dc3545;'>Reason for Cancellation:</h3>
                                <p>{$delete_reason}</p>
                            </div>
                            
                            <div class='contact-info'>
                                <p><strong>Need to Reschedule?</strong></p>
                                <p>You can easily reschedule your appointment through our website or contact us directly:</p>
                                <ul>
                                    <li>📞 Phone: (123) 456-7890</li>
                                    <li>📧 Email: toothrepairdentalclinic@gmail.com</li>
                                    <li>🌐 Website: www.toothrepair.com</li>
                                </ul>
                            </div>
                            
                            <p>We apologize for any inconvenience this may have caused.</p>
                            
                            <div class='footer'>
                                <p>Best regards,<br><strong>ToothRepair Dental Clinic</strong></p>
                                <small style='color:#666;'>This is an automated message, please do not reply directly to this email.</small>
                            </div>
                        </div>
                    </div>
                </body>
                </html>";
                
                $mail->Body = $body;
                $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

                $mail->send();

                // Delete the appointment
                $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
                $stmt->bind_param("i", $appointment_id);
                $stmt->execute();

                // Commit transaction
                $conn->commit();
                
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
            }
        } else {
            throw new Exception('Appointment not found');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>