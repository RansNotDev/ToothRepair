<?php
include_once('../../database/db_connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Validate required fields
        $required = ['fullname', 'email', 'contact_number', 'address', 
                    'appointment_date', 'appointment_time', 'service_id', 'status'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate appointment date and time
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];

        // Check if timeslot is available
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) as count 
             FROM appointments 
             WHERE appointment_date = ? 
             AND appointment_time = ? 
             AND status NOT IN ('cancelled')"
        );
        $check_stmt->bind_param("ss", $appointment_date, $appointment_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("This time slot is already booked");
        }

        // Check if within availability
        $avail_stmt = $conn->prepare(
            "SELECT time_start, time_end 
             FROM availability_tb 
             WHERE available_date = ? 
             AND is_active = 1"
        );
        $avail_stmt->bind_param("s", $appointment_date);
        $avail_stmt->execute();
        $avail = $avail_stmt->get_result()->fetch_assoc();
        
        if (!$avail) {
            throw new Exception("No availability for selected date");
        }

        // Create or update user
        $default_password = password_hash('1234', PASSWORD_BCRYPT);
        $user_stmt = $conn->prepare(
            "INSERT INTO users (fullname, email, contact_number, address, password) 
             VALUES (?, ?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE 
             contact_number = VALUES(contact_number),
             address = VALUES(address)"
        );
        $user_stmt->bind_param(
            "sssss", 
            $_POST['fullname'], 
            $_POST['email'], 
            $_POST['contact_number'], 
            $_POST['address'],
            $default_password
        );
        $user_stmt->execute();
        
        // Get user_id (whether inserted or existing)
        $user_id = $user_stmt->insert_id ?: $conn->query(
            "SELECT user_id FROM users WHERE email = '" . 
            $conn->real_escape_string($_POST['email']) . "'"
        )->fetch_object()->user_id;

        // Create appointment
        $stmt = $conn->prepare(
            "INSERT INTO appointments 
             (user_id, service_id, appointment_date, appointment_time, status) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iisss", 
            $user_id, 
            $_POST['service_id'], 
            $appointment_date, 
            $appointment_time,
            $_POST['status']
        );
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Appointment added successfully'
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>
