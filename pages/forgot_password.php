<?php
require '../database/db_connection.php';
include_once("../includes/header.php");
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $new_password = "12345"; // Fixed password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email);
            
            if($update->execute()) {
                // Verify update
                $verify = $conn->prepare("SELECT password FROM users WHERE email = ?");
                $verify->bind_param("s", $email);
                $verify->execute();
                $verify_result = $verify->get_result();
                $user = $verify_result->fetch_assoc();
                
                if(password_verify($new_password, $user['password'])) {
                    // Commit transaction
                    $conn->commit();
                    
                    // Send email with new password
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'toothrepairdentalclinic@gmail.com';
                    $mail->Password = 'evwt cpxf ywtl zytp';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;

                    $mail->setFrom('toothrepairdentalclinic@gmail.com');
                    $mail->addAddress($email);
                    $mail->isHTML(true);

                    $mail->Subject = 'Password Reset - ToothRepair Dental Clinic';
                    $mail->Body = "
                        <h2>Password Reset Successful</h2>
                        <p>Your new password is: <strong>{$new_password}</strong></p>
                        <p>Please change your password after logging in for security.</p>
                    ";

                    $mail->send();
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Reset Successful',
                            text: 'New password has been sent to your email',
                            confirmButtonText: 'Login Now'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'loginpage.php';
                            }
                        });
                    </script>";
                } else {
                    throw new Exception("Password update failed verification");
                }
            } else {
                throw new Exception("Failed to update password");
            }
        } else {
            throw new Exception("Email not found");
        }
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . $e->getMessage() . "'
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password | Tooth Repair Clinic</title>
    <link rel="stylesheet" href="../assets/css/loginpage.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>Reset Password</h3>
            <form method="POST">
                <div class="form-outline">
                    <input type="email" name="email" required />
                    <label>Enter your email</label>
                </div>
                <button type="submit">Reset Password</button>
                <div class="back-to-login">
                    <a href="loginpage.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>