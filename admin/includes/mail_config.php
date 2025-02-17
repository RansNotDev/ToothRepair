<?php
use PHPMailer\PHPMailer\PHPMailer;

// Email Configuration
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USERNAME', '2e9b13214e6b97');
define('SMTP_PASSWORD', '5e77377c88fc33');
define('SMTP_FROM_EMAIL', 'toothrepair@gmail.com');
define('SMTP_FROM_NAME', 'ToothRepair Dental Clinic');

// Configure PHPMailer with your settings
function configureMailer(PHPMailer $mail) {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
}
?>