<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendBookingConfirmationEmail($userEmail, $fullName, $serviceName, $appointmentDate, $appointmentTime, $isRebooking = false) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'toothrepairdentalclinic@gmail.com'; // Replace with your email
        $mail->Password   = 'evwt cpxf ywtl zytp'; // Replace with your app password
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('toothrepairdentalclinic@gmail.com', 'Tooth Repair Dental Clinic');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        
        // Customize subject and message based on booking type
        $subject = $isRebooking ? "Appointment Rebooked - Tooth Repair Dental Clinic" : "Appointment Confirmation - Tooth Repair Dental Clinic";
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='https://your-logo-url.com/logo.png' alt='Tooth Repair Logo' style='max-width: 200px;'>
                <h1 style='color: #2c3e50; margin: 20px 0;'>Appointment " . ($isRebooking ? "Rebooked" : "Confirmation") . "</h1>
            </div>
            
            <div style='background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #2c3e50; margin-bottom: 20px;'>Dear {$fullName},</h2>
                
                <p style='color: #34495e; line-height: 1.6;'>" . 
                    ($isRebooking ? "Your appointment has been successfully rebooked" : "Your appointment has been successfully booked") . 
                    " with Tooth Repair Dental Clinic.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong style='color: #2c3e50;'>Service:</strong> 
                        <span style='color: #34495e;'>{$serviceName}</span>
                    </p>
                    <p style='margin: 5px 0;'><strong style='color: #2c3e50;'>Date:</strong> 
                        <span style='color: #34495e;'>{$appointmentDate}</span>
                    </p>
                    <p style='margin: 5px 0;'><strong style='color: #2c3e50;'>Time:</strong> 
                        <span style='color: #34495e;'>{$appointmentTime}</span>
                    </p>
                </div>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='color: #856404; margin: 0;'>⚠️ Please arrive 15 minutes before your scheduled appointment time.</p>
                </div>
                
                <p style='color: #34495e; line-height: 1.6;'>If you need to reschedule or cancel your appointment, please contact us at least 24 hours in advance.</p>
            </div>
            
            <div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;'>
                <p style='color: #34495e; margin: 0;'>Best regards,<br>
                <strong>Tooth Repair Dental Clinic Team</strong></p>
            </div>
            
            <div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;'>
                <p>This is an automated message, please do not reply to this email.</p>
                <p>📞 Contact us: (123) 456-7890 | 📧 Email: info@toothrepair.com</p>
                <p>🏥 Address: Your Clinic Address Here</p>
            </div>
        </div>";

        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $message));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>