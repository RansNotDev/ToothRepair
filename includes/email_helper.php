<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendBookingConfirmationEmail($userEmail, $fullName, $serviceName, $appointmentDate, $appointmentTime) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'toothrepairdentalclinic@gmail.com'; // Replace with your email
        $mail->Password   = 'evwt cpxf ywtl zytp'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('noreply@toothrepair.com', 'ToothRepair Dental Clinic');
        $mail->addAddress($userEmail, $fullName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmation - ToothRepair Dental Clinic";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; }
                .content { padding: 20px; }
                .footer { padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Thank you for booking with ToothRepair Dental Clinic!</h2>
                </div>
                <div class='content'>
                    <p>Dear $fullName,</p>
                    <p>Your appointment has been successfully booked. Here are the details:</p>
                    <ul>
                        <li><strong>Service:</strong> $serviceName</li>
                        <li><strong>Date:</strong> " . date('F d, Y', strtotime($appointmentDate)) . "</li>
                        <li><strong>Time:</strong> " . date('h:i A', strtotime($appointmentTime)) . "</li>
                    </ul>
                    <p><strong>Important:</strong> Please arrive 15 minutes before your scheduled appointment.</p>
                    <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>ToothRepair Dental Clinic</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $message));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>