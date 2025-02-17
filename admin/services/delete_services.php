<?php

include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (empty($_POST['service_id'])) {
        throw new Exception('Service ID is required');
    }

    $serviceId = intval($_POST['service_id']);
    
    // First check if service exists
    $checkStmt = $conn->prepare("SELECT service_id FROM services WHERE service_id = ?");
    $checkStmt->bind_param("i", $serviceId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Service not found');
    }
    $checkStmt->close();
    
    // Proceed with deletion
    $deleteStmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
    $deleteStmt->bind_param("i", $serviceId);
    
    if (!$deleteStmt->execute()) {
        throw new Exception('Failed to delete service: ' . $deleteStmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Service deleted successfully'
    ]);
    
    $deleteStmt->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>