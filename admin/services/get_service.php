<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (empty($_GET['service_id'])) {
        throw new Exception('Service ID is required');
    }

    $serviceId = intval($_GET['service_id']);
    
    $stmt = $conn->prepare("SELECT service_id, service_name, description, price FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $serviceId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch service details');
    }
    
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    
    if (!$service) {
        throw new Exception('Service not found');
    }

    echo json_encode([
        'status' => 'success',
        'service' => $service
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