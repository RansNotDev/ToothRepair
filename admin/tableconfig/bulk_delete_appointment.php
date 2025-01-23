<?php
include_once('../db_connection.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['appointment_ids'])) {
        $appointmentIds = $_POST['appointment_ids']; // Array of IDs

        // Convert the array into a comma-separated string for SQL query
        $ids = implode(',', array_map('intval', $appointmentIds));

        // SQL query to delete appointments
        $sql = "DELETE FROM appointments WHERE appointment_id IN ($ids)";

        if ($conn->query($sql) === TRUE) {
            http_response_code(200); // Success
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500); // Error
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        http_response_code(400); // Bad request
        echo json_encode(['success' => false, 'error' => 'No appointment IDs provided.']);
    }
}
?>
