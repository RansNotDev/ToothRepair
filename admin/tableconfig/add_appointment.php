<?php
include_once('../../database/db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required = ['fullname', 'email', 'contact_number', 'address', 'appointment_date', 'appointment_time', 'service_id'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Check if timeslot is available
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
        $check_stmt->bind_param("ss", $_POST['appointment_date'], $_POST['appointment_time']);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("This time slot is already booked");
        }

        // Check if within availability
        $avail_stmt = $conn->prepare("SELECT time_start, time_end FROM availability_tb WHERE available_date = ? AND is_active = 1");
        $avail_stmt->bind_param("s", $_POST['appointment_date']);
        $avail_stmt->execute();
        $avail = $avail_stmt->get_result()->fetch_assoc();
        
        if (!$avail) {
            throw new Exception("No availability for selected date");
        }

        $selected_time = strtotime($_POST['appointment_time']);
        $start_time = strtotime($avail['time_start']);
        $end_time = strtotime($avail['time_end']);
        
        if ($selected_time < $start_time || $selected_time > $end_time) {
            throw new Exception("Selected time is outside available hours");
        }

        // Create user if not exists
        $user_stmt = $conn->prepare("INSERT INTO users (fullname, email, contact_number, address) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE user_id=LAST_INSERT_ID(user_id)");
        $user_stmt->bind_param("ssss", $_POST['fullname'], $_POST['email'], $_POST['contact_number'], $_POST['address']);
        $user_stmt->execute();
        $user_id = $user_stmt->insert_id;

        // Create appointment
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiss", $user_id, $_POST['service_id'], $_POST['appointment_date'], $_POST['appointment_time']);
        $stmt->execute();

        header('Location: ../appointmentlist.php?success=add');

    } catch (Exception $e) {
        header('Location: ../appointmentlist.php?error=' . urlencode($e->getMessage()));
    }
}
?>
