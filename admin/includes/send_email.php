<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendAppointmentEmail($userEmail, $appointmentDetails, $type = 'new') {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com';
        $mail->Password = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('toothrepairdentalclinic@gmail.com', 'Tooth Repair Dental Clinic');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);

        switch($type) {
            case 'new':
                $mail->Subject = 'New Appointment Confirmation';
                $message = "
                <h2>Your appointment has been scheduled!</h2>
                <p>Dear {$appointmentDetails['fullname']},</p>
                <p>Your appointment details:</p>
                <ul>
                    <li>Date: {$appointmentDetails['appointment_date']}</li>
                    <li>Time: {$appointmentDetails['appointment_time']}</li>
                    <li>Service: {$appointmentDetails['service_name']}</li>
                    <li>Status: {$appointmentDetails['status']}</li>
                </ul>
                <p>Thank you for choosing Tooth Repair Dental Clinic!</p>";
                break;

            case 'update':
                $mail->Subject = 'Appointment Status Update';
                $message = "
                <h2>Your appointment status has been updated</h2>
                <p>Dear {$appointmentDetails['fullname']},</p>
                <p>Your appointment has been {$appointmentDetails['status']}.</p>
                <p>Appointment details:</p>
                <ul>
                    <li>Date: {$appointmentDetails['appointment_date']}</li>
                    <li>Time: {$appointmentDetails['appointment_time']}</li>
                    <li>Service: {$appointmentDetails['service_name']}</li>
                </ul>
                <p>If you have any questions, please contact us.</p>";
                break;
        }

        $mail->Body = $message;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}