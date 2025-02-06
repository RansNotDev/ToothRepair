.php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;



require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if(isset($_POST['sendmail'])) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'toothrepairdentalclinic@gmail.com';
        $mail->Password = 'evwt cpxf ywtl zytp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('toothrepairdentalclinic@gmail.com');
        $mail->addAddress($_POST['email']);
        $mail->isHTML(true);

        $mail->Subject = $_POST['subject'];
        $mail->Body = $_POST['message'];
        $mail->AltBody = strip_tags($_POST['message']); // Plain text version

        $mail->send();
        echo "<script>
            alert('Email sent successfully!');
            window.location.href='test.php';
        </script>";
    } catch (Exception $e) {
        echo "<script>
            alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');
            window.location.href='test.php';
        </script>";
    }
} else {
    header("Location: test.php");
    exit();
}
?>