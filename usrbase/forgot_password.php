<?php
require '../database/db_connection.php';
include_once("../includes/header.php");
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

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
            $new_password = generateRandomPassword(); // Generate random password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email);
            
            if($update->execute()) {
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
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #4a90e2;'>Password Reset Successful</h2>
                        <p>Your new password is: <strong style='background: #f5f5f5; padding: 5px 10px; border-radius: 3px;'>{$new_password}</strong></p>
                        <p style='color: #666;'>Please change your password after logging in for security reasons.</p>
                        <p style='color: #ff0000;'>Do not share this password with anyone!</p>
                        <hr>
                        <p style='font-size: 12px; color: #999;'>ToothRepair Dental Clinic</p>
                    </div>
                ";

                $mail->send();
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Reset Successful',
                        text: 'New password has been sent to your email',
                        confirmButtonText: 'Login Now',
                        confirmButtonColor: '#4a90e2'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = './entryvault.php';
                        }
                    });
                </script>";
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
                text: '" . $e->getMessage() . "',
                confirmButtonColor: '#4a90e2'
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
    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .card h3 {
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .form-outline {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-outline input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-outline input:focus {
            border-color: #4a90e2;
            outline: none;
        }

        .form-outline label {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            transition: 0.3s ease;
            pointer-events: none;
        }

        .form-outline input:focus + label,
        .form-outline input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 0.8rem;
            background: white;
            padding: 0 5px;
            color: #4a90e2;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #357abd;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }

        .back-to-login a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>Reset Password</h3>
            <form method="POST">
                <div class="form-outline">
                    <input type="email" name="email" required placeholder=" " />
                    <label>Enter your email</label>
                </div>
                <button type="submit">Reset Password</button>
                <div class="back-to-login">
                    <a href="entryvault.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>