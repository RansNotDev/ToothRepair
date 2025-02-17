<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (empty($_POST['service_id']) || empty($_POST['service_name']) || 
        empty($_POST['description']) || empty($_POST['price'])) {
        throw new Exception('Required fields are missing');
    }

    $serviceId = intval($_POST['service_id']);
    $serviceName = mysqli_real_escape_string($conn, $_POST['service_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    if ($price <= 0) {
        throw new Exception('Invalid price value');
    }

    // Check if new image is uploaded
    if (!empty($_FILES['image']['tmp_name'])) {
        $image = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($image['type'], $allowedTypes)) {
            throw new Exception('Invalid image format');
        }

        if ($image['size'] > 16777216) {
            throw new Exception('Image file is too large (max 16MB)');
        }

        $imageData = file_get_contents($image['tmp_name']);
        
        $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ?, image = ? WHERE service_id = ?");
        $stmt->bind_param("ssdsi", $serviceName, $description, $price, $imageData, $serviceId);
    } else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ? WHERE service_id = ?");
        $stmt->bind_param("ssdi", $serviceName, $description, $price, $serviceId);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to update service');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Service updated successfully'
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