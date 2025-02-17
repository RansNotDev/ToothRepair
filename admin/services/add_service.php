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
    if (empty($_POST['service_name']) || empty($_POST['description']) || 
        empty($_POST['price']) || empty($_FILES['image'])) {
        throw new Exception('All fields are required');
    }

    // Sanitize inputs
    $serviceName = mysqli_real_escape_string($conn, $_POST['service_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    // Validate price
    if ($price <= 0) {
        throw new Exception('Invalid price value');
    }

    // Handle image upload
    $image = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($image['type'], $allowedTypes)) {
        throw new Exception('Invalid image format. Only JPG, PNG, and GIF are allowed.');
    }

    // Check file size (max 16MB)
    if ($image['size'] > 16777216) {
        throw new Exception('Image file is too large. Maximum size is 16MB.');
    }

    // Read image file as binary data
    $imageData = null;
    $imagePath = $image['tmp_name'];
    
    if (!file_exists($imagePath)) {
        throw new Exception('Error accessing uploaded file');
    }

    $imageData = file_get_contents($imagePath);
    
    if ($imageData === false) {
        throw new Exception('Error reading image file');
    }

    // First, update the database table to use LONGBLOB if not already done
    $alterTable = "ALTER TABLE services MODIFY COLUMN image LONGBLOB";
    $conn->query($alterTable);
    
    // Prepare and execute the query with LONGBLOB data
    $stmt = $conn->prepare("INSERT INTO services (service_name, description, price, image) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssds", $serviceName, $description, $price, $imageData);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $insertId = $stmt->insert_id;
    $stmt->close();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Service added successfully',
        'service_id' => $insertId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}

$conn->close();