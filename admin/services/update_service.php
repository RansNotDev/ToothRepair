<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['service_id']) || !isset($_POST['service_name']) || !isset($_POST['description']) || !isset($_POST['price'])) {
        throw new Exception('Missing required fields');
    }

    $service_id = intval($_POST['service_id']);
    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Update with new image
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ?, image = ? WHERE service_id = ?");
        $stmt->bind_param("ssdsi", $service_name, $description, $price, $imageData, $service_id);
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ? WHERE service_id = ?");
        $stmt->bind_param("ssdi", $service_name, $description, $price, $service_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Failed to update service');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();