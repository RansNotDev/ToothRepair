<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['service_id'])) {
        throw new Exception('Service ID is required');
    }

    $service_id = intval($_POST['service_id']);
    
    $stmt = $conn->prepare("UPDATE services SET is_active = 0 WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Failed to deactivate service');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();