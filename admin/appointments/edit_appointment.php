<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get current appointment data
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $_POST['appointment_id']);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();

        if (!$current) {
            throw new Exception("Appointment not found");
        }

        // Get current user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $current['user_id']);
        $stmt->execute();
        $currentUser = $stmt->get_result()->fetch_assoc();

        // Update user information - use existing data if fields are empty
        $fullname = !empty($_POST['fullname']) ? $_POST['fullname'] : $currentUser['fullname'];
        $email = !empty($_POST['email']) ? $_POST['email'] : $currentUser['email'];
        $contact = !empty($_POST['contact_number']) ? $_POST['contact_number'] : $currentUser['contact_number'];
        $address = !empty($_POST['address']) ? $_POST['address'] : $currentUser['address'];

        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, contact_number = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $fullname, $email, $contact, $address, $current['user_id']);
        $stmt->execute();

        // Update appointment information - use existing data if fields are empty
        $appointment_date = !empty($_POST['appointment_date']) ? $_POST['appointment_date'] : $current['appointment_date'];
        $appointment_time = !empty($_POST['appointment_time']) ? $_POST['appointment_time'] : $current['appointment_time'];
        $service_id = !empty($_POST['service_id']) ? $_POST['service_id'] : $current['service_id'];
        $status = !empty($_POST['status']) ? $_POST['status'] : $current['status'];

        // Check if new timeslot is available (only if date or time changed)
        if ($appointment_date != $current['appointment_date'] || $appointment_time != $current['appointment_time']) {
            $check_stmt = $conn->prepare(
                "SELECT COUNT(*) as count FROM appointments 
                WHERE appointment_date = ? AND appointment_time = ? 
                AND appointment_id != ? AND status NOT IN ('cancelled')"
            );
            $check_stmt->bind_param("ssi", $appointment_date, $appointment_time, $_POST['appointment_id']);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                throw new Exception("Selected time slot is already booked");
            }
        }

        // Update appointment
        $stmt = $conn->prepare(
            "UPDATE appointments 
            SET appointment_date = ?, appointment_time = ?, service_id = ?, status = ? 
            WHERE appointment_id = ?"
        );
        $stmt->bind_param(
            "ssisi", 
            $appointment_date, 
            $appointment_time, 
            $service_id, 
            $status, 
            $_POST['appointment_id']
        );
        $stmt->execute();

        // If status is being changed to completed
        if ($status === 'completed') {
            try {
                // Check if record already exists in appointment_records
                $check_record = $conn->prepare("SELECT appointment_id FROM appointment_records WHERE appointment_id = ?");
                $check_record->bind_param("i", $_POST['appointment_id']);
                $check_record->execute();
                $exists = $check_record->get_result()->num_rows > 0;

                // Only insert if record doesn't exist
                if (!$exists) {
                    // Insert into appointment_records
                    $insert_sql = "INSERT INTO appointment_records 
                        (appointment_id, user_id, service_id, appointment_date, appointment_time, completion_date, status)
                        VALUES (?, ?, ?, ?, ?, NOW(), 'completed')";
                    
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param(
                        "iiiss", 
                        $_POST['appointment_id'],
                        $appointment['user_id'],
                        $appointment['service_id'],
                        $appointment['appointment_date'],
                        $appointment['appointment_time']
                    );
                    $stmt->execute();
                }

                // Update appointments table status
                $update_sql = "UPDATE appointments SET status = 'completed' WHERE appointment_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("i", $_POST['appointment_id']);
                $stmt->execute();

                // Commit transaction
                $conn->commit();
                
                echo json_encode(['success' => true]);
                exit;

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'error' => 'Database error: ' . $e->getMessage()
                ]);
                exit;
            }
        } else {
            // Normal update for non-completed status
            $update_sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $status, $_POST['appointment_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update appointment'
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
