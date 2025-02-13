<?php

error_log("delete_services.php executed"); // Add this line
include_once('../../database/db_connection.php');

// Add these lines to check the connection details
error_log("Database host: " . $conn->host_info);
// error_log("Database name: " . $conn->database); // This line is removed because mysqli does not have a database property

if (isset($_POST['service_id'])) {
    $serviceId = $_POST['service_id'];
    error_log("Service ID received by delete_services.php: " . $serviceId); // Log the service ID

    // Perform the deletion
    $sql = "DELETE FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $serviceId); // "i" for integer

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error; // Add this line
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error"; // No service ID provided
}
?>