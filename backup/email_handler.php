<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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
?>