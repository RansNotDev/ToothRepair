<?php
require_once('../../database/db_connection.php');

header('Content-Type: application/json');

if (!isset($_POST['appointment_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Appointment ID is required'
    ]);
    exit;
}

$appointment_id = $_POST['appointment_id'];

try {
    $conn->begin_transaction();

    // Get appointment details
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            u.fullname,
            s.service_name
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN services s ON a.service_id = s.service_id
        WHERE a.appointment_id = ?
    ");
    
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if (!$appointment) {
        throw new Exception('Appointment not found');
    }

    // Insert into cancelled_appointments
    $stmt = $conn->prepare("
        INSERT INTO cancelled_appointments 
        (appointment_id, user_id, service_id, appointment_date, appointment_time) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "iiiss", 
        $appointment['appointment_id'],
        $appointment['user_id'],
        $appointment['service_id'],
        $appointment['appointment_date'],
        $appointment['appointment_time']
    );
    
    $stmt->execute();

    // Delete from appointments table
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment cancelled successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error cancelling appointment: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 