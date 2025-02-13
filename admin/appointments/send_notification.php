<?php
require_once('../../vendor/autoload.php');
require_once('../includes/email_helper.php');
require_once('../../database/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Fetch appointment details
    $sql = "SELECT 
                appointments.*, 
                users.fullname,
                users.email,
                services.service_name
            FROM appointments
            INNER JOIN users ON appointments.user_id = users.user_id
            INNER JOIN services ON appointments.service_id = services.service_id
            WHERE appointments.appointment_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if ($appointment) {
        // Format appointment data for email
        $appointmentData = [
            'fullname' => $appointment['fullname'],
            'email' => $appointment['email'],
            'appointment_date' => $appointment['appointment_date'],
            'appointment_time' => $appointment['appointment_time'],
            'service_name' => $appointment['service_name'],
            'status' => $appointment['status']
        ];

        // Send email notification
        $emailSent = sendAppointmentEmail($appointment['email'], $appointmentData, 'update');

        echo json_encode([
            'success' => $emailSent,
            'message' => $emailSent ? 'Notification sent successfully' : 'Failed to send notification'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Appointment not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}