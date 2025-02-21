<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['service_id'])) {
        throw new Exception('Service ID is required');
    }

    $service_id = intval($_GET['service_id']);
    
    $stmt = $conn->prepare("SELECT service_id, service_name, description, price FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($service = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'service' => $service]);
    } else {
        throw new Exception('Service not found');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();