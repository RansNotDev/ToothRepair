<?php
// Include the database connection
include('../../database/db_connection.php'); // Adjust the path as needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if appointment_id is provided
    if (isset($_POST['appointment_id']) && !empty($_POST['appointment_id'])) {
        // Retrieve form data
        $appointment_id = $_POST['appointment_id'];
        $fullname = $_POST['fullname'];
        $appointment_date = $_POST['appointment_date'];
        $services = $_POST['services'];
        $status = $_POST['status']; // Ensure this matches the form name attribute

        // Update query
        $sql = "UPDATE appointments 
                INNER JOIN users ON appointments.user_id = users.user_id
                SET 
                    appointments.appointment_date = ?, 
                    appointments.service = ?, 
                    appointments.status = ?, 
                    users.fullname = ? 
                WHERE appointments.appointment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $appointment_date, $services, $status, $fullname, $appointment_id);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect back to the table with a success message
            header("Location: ../tables.php?success=1");
            exit();
        } else {
            echo "Error updating appointment: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "No appointment ID provided.";
    }
} else {
    echo "Invalid request.";
}
?>
