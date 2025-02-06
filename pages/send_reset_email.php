<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

if(isset($_GET['email']) && isset($_GET['token'])) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com';
        $mail->Password = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('toothrepairdentalclinic@gmail.com');
        $mail->addAddress($_GET['email']);
        $mail->isHTML(true);

        $resetLink = "http://localhost/ToothRepair/pages/reset_password.php?token=" . $_GET['token'];
        
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='$resetLink'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";

        $mail->send();
        header("Location: loginpage.php?reset=sent");
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>