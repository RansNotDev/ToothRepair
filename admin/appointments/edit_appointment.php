<?php
require_once('../../database/db_connection.php');
require_once('../includes/email_helper.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];

    // Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $status, $appointment_id);
    
    if ($stmt->execute()) {
        // If status is confirmed, send email notification
        if ($status === 'confirmed') {
            // Get appointment details for email
            $stmt = $conn->prepare("
                SELECT 
                    a.appointment_date,
                    a.appointment_time,
                    u.email,
                    u.fullname,
                    s.service_name,
                    a.status
                FROM appointments a
                JOIN users u ON a.user_id = u.user_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.appointment_id = ?
            ");
            
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($appointmentData = $result->fetch_assoc()) {
                sendStatusUpdateEmail($appointmentData['email'], $appointmentData);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Appointment status updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update appointment status');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
