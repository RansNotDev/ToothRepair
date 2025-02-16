<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../../database/db_connection.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        if (empty($_POST['service_name']) || empty($_POST['description']) || 
            empty($_POST['price']) || !isset($_FILES['image'])) {
            throw new Exception('All fields are required');
        }

        // Image validation
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Image upload failed: ' . $_FILES['image']['error']);
        }

        // Read image file
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        if ($imageData === false) {
            throw new Exception('Failed to read image file');
        }

        // Prepare statement
        $sql = "INSERT INTO services (service_name, description, price, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters - using null for the blob parameter initially
        $null = null;
        $stmt->bind_param("ssdb", $_POST['service_name'], $_POST['description'], $_POST['price'], $null);

        // Now send the blob data separately
        $stmt->send_long_data(3, $imageData);

        // Execute statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Service added successfully'
        ]);

        $stmt->close();

    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Service addition error: " . $e->getMessage());
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    $conn->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}