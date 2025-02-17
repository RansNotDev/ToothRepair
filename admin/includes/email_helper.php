<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function sendAppointmentEmail($userEmail, $appointmentData, $type = 'new') {
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

        // Add these styles for both email types
        $headerStyle = "color: #2c3e50; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #3498db;";
        $containerStyle = "font-family: 'Helvetica', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); background-color: #ffffff;";
        $detailsBoxStyle = "background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #2ecc71; margin: 20px 0;";
        $listStyle = "list-style: none; padding-left: 0; margin: 15px 0;";
        $listItemStyle = "padding: 8px 0; border-bottom: 1px solid #eee;";
        $emphasizedText = "color: #2980b9; font-weight: bold;";
        $footerStyle = "margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #7f8c8d; font-size: 0.9em;";
        $buttonStyle = "display: inline-block; padding: 12px 25px; background-color: #3498db; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold;";

        // Format date and time
        $formattedDate = date('F j, Y', strtotime($appointmentData['appointment_date']));
        $formattedTime = date('g:i A', strtotime($appointmentData['appointment_time']));

        switch($type) {
            case 'new':
                $mail->Subject = 'Appointment Confirmation - Tooth Repair Dental Clinic';
                $mail->Body = "
                    <div style='background-color: #f4f6f8; padding: 40px 0;'>
                        <div style='{$containerStyle}'>
                            <h2 style='{$headerStyle}'>✅ Appointment Confirmation</h2>
                            <p style='font-size: 16px;'>Dear <strong>{$appointmentData['fullname']}</strong>,</p>
                            <p style='color: #34495e; line-height: 1.6;'>Your appointment has been successfully scheduled at Tooth Repair Dental Clinic.</p>
                            
                            <div style='{$detailsBoxStyle}'>
                                <h3 style='color: #2ecc71; margin-top: 0;'>📅 Appointment Details</h3>
                                <ul style='{$listStyle}'>
                                    <li style='{$listItemStyle}'><strong>📆 Date:</strong> <span style='{$emphasizedText}'>{$formattedDate}</span></li>
                                    <li style='{$listItemStyle}'><strong>⏰ Time:</strong> <span style='{$emphasizedText}'>{$formattedTime}</span></li>
                                    <li style='{$listItemStyle}'><strong>🦷 Service:</strong> <span style='{$emphasizedText}'>{$appointmentData['service_name']}</span></li>
                                    <li style='{$listItemStyle}'><strong>📋 Status:</strong> <span style='{$emphasizedText}'>{$appointmentData['status']}</span></li>
                                </ul>
                            </div>

                            <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                                <p style='margin: 0; color: #856404;'>⚠️ Please arrive 10 minutes before your scheduled appointment time.</p>
                            </div>

                            <a href='#' style='{$buttonStyle}'>View Appointment Details</a>

                            <div style='{$footerStyle}'>
                                <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
                                <p style='color: #95a5a6;'>Thank you for choosing Tooth Repair Dental Clinic! 🦷</p>
                            </div>
                        </div>
                    </div>";
                break;

            case 'update':
                $mail->Subject = 'Appointment Update - Tooth Repair Dental Clinic';
                $mail->Body = "
                    <div style='background-color: #f4f6f8; padding: 40px 0;'>
                        <div style='{$containerStyle}'>
                            <h2 style='{$headerStyle}'>🔄 Appointment Update</h2>
                            <p style='font-size: 16px;'>Dear <strong>{$appointmentData['fullname']}</strong>,</p>
                            <p style='color: #34495e; line-height: 1.6;'>Your appointment status has been updated to: 
                                <span style='background-color: #3498db; color: white; padding: 5px 10px; border-radius: 4px;'>
                                    {$appointmentData['status']}
                                </span>
                            </p>
                            
                            <div style='{$detailsBoxStyle}'>
                                <h3 style='color: #2ecc71; margin-top: 0;'>📅 Updated Appointment Details</h3>
                                <ul style='{$listStyle}'>
                                    <li style='{$listItemStyle}'><strong>📆 Date:</strong> <span style='{$emphasizedText}'>{$formattedDate}</span></li>
                                    <li style='{$listItemStyle}'><strong>⏰ Time:</strong> <span style='{$emphasizedText}'>{$formattedTime}</span></li>
                                    <li style='{$listItemStyle}'><strong>🦷 Service:</strong> <span style='{$emphasizedText}'>{$appointmentData['service_name']}</span></li>
                                </ul>
                            </div>

                            <a href='#' style='{$buttonStyle}'>View Updated Appointment</a>

                            <div style='{$footerStyle}'>
                                <p>If you have any questions, please don't hesitate to contact us.</p>
                                <p style='color: #95a5a6;'>Thank you for choosing Tooth Repair Dental Clinic! 🦷</p>
                                <div style='text-align: center; margin-top: 20px; font-size: 0.8em; color: #bdc3c7;'>
                                    <p>© 2025 Tooth Repair Dental Clinic. All rights reserved.</p>
                                </div>
                            </div>
                        </div>
                    </div>";
                break;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

function sendStatusUpdateEmail($userEmail, $appointmentData) {
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
        $mail->Subject = 'Appointment Status Update - Tooth Repair Dental Clinic';

        // Only send confirmation email for 'confirmed' status
        if ($appointmentData['status'] === 'confirmed') {
            $formattedDate = date('F j, Y', strtotime($appointmentData['appointment_date']));
            $formattedTime = date('g:i A', strtotime($appointmentData['appointment_time']));

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>Appointment Confirmation</h2>
                    <p>Dear {$appointmentData['fullname']},</p>
                    <p>Your appointment has been confirmed with the following details:</p>
                    <ul>
                        <li>Date: {$formattedDate}</li>
                        <li>Time: {$formattedTime}</li>
                        <li>Service: {$appointmentData['service_name']}</li>
                    </ul>
                    <p>Please arrive 10 minutes before your scheduled time.</p>
                    <p>Thank you for choosing our services!</p>
                </div>";

            $mail->send();
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}