<?php
include('../db_connection.php');

// Check if the delete request has appointment IDs (for bulk deletion)
if (isset($_POST['appointments']) && !empty($_POST['appointments'])) {
    // Get the array of appointment IDs
    $appointment_ids = $_POST['appointments'];

    // Prepare the DELETE query with a placeholder for each appointment ID
    $placeholders = implode(',', array_fill(0, count($appointment_ids), '?'));
    $sql = "DELETE FROM appointments WHERE appointment_id IN ($placeholders)";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Dynamically bind the parameters
    $types = str_repeat('i', count($appointment_ids));  // 'i' for integer type
    $stmt->bind_param($types, ...$appointment_ids);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the table with a success message
        header("Location: ../tables.php?success=1");
        exit();
    } else {
        echo "Error deleting appointments: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} elseif (isset($_GET['id'])) {  // If deleting a single appointment
    $appointment_id = $_GET['id'];

    // Delete query for a single appointment
    $sql = "DELETE FROM appointments WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the table with a success message
        header("Location: ../tables.php?success=1");
        exit();
    } else {
        echo "Error deleting appointment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No appointment ID provided.";
}
?>
