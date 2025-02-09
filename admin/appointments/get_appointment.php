<?php
header('Content-Type: application/json');
include_once('../../database/db_connection.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Appointment ID is required');
    }

    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT 
        a.appointment_id,
        u.fullname,
        u.email,
        u.contact_number,
        u.address,
        a.appointment_date,
        TIME_FORMAT(a.appointment_time, '%H:%i:%s') as appointment_time,
        s.service_name,
        a.service_id,
        a.status
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.user_id
    INNER JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Appointment not found');
    }

    echo json_encode($result->fetch_assoc());

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}