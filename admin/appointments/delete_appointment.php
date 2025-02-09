<?php
header('Content-Type: application/json');
require_once('../../database/db_connection.php');

try {
    // Validate appointment ID
    if (!isset($_POST['id'])) {
        throw new Exception('Appointment ID is required');
    }

    $appointment_id = intval($_POST['id']);

    // Begin transaction
    $conn->begin_transaction();

    // Delete appointment
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete appointment');
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception('Appointment not found');
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn && $conn->ping()) {
        $conn->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>