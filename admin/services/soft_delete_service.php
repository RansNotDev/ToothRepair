<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (empty($_POST['service_id'])) {
        throw new Exception('Service ID is required');
    }

    $serviceId = intval($_POST['service_id']);
    
    // Update service to inactive instead of deleting
    $stmt = $conn->prepare("UPDATE services SET is_active = 0 WHERE service_id = ?");
    $stmt->bind_param("i", $serviceId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to deactivate service: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Service not found');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Service successfully deactivated'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();