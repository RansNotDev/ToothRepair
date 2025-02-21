<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // Set timeout to 5 minutes
ini_set('post_max_size', '64M');    // Allow larger POST data
ini_set('upload_max_filesize', '64M'); // Allow larger file uploads

include_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['service_name']) || !isset($_POST['description']) || !isset($_POST['price']) || !isset($_FILES['image'])) {
        throw new Exception('Missing required fields');
    }

    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    
    // Validate image
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed');
    }

    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO services (service_name, description, price, image, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssds", $service_name, $description, $price, $imageData);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Failed to add service');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();