<?php
require_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    $conn->begin_transaction();

    // Prepare the data with optional email
    $fullname = trim($_POST['fullname']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $completion_date = $_POST['completion_date'];

    // Check if user exists with this contact number
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE contact_number = ?");
    $stmt->bind_param("s", $contact_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, get user_id
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
    } else {
        // Create new user with optional email
        $stmt = $conn->prepare("
            INSERT INTO users 
            (fullname, email, contact_number, address) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("ssss", 
            $fullname,
            $email,  // This can be null
            $contact_number,
            $address
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user record');
        }
        
        $user_id = $conn->insert_id;
    }

    // Add record to appointment_records
    $stmt = $conn->prepare("
        INSERT INTO appointment_records 
        (user_id, service_id, appointment_date, appointment_time, completion_date) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iisss", 
        $user_id,
        $service_id,
        $appointment_date,
        $appointment_time,
        $completion_date
    );

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Record added successfully'
        ]);
    } else {
        throw new Exception('Failed to add appointment record');
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>