<?php
include('../../database/db_connection.php'); // Database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $service = $_POST['service'];
    $message = $_POST['message'];
    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Set default password
    $default_password = '1234';
    $hashed_password = password_hash($default_password, PASSWORD_BCRYPT); // Hash password using bcrypt

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $user_sql = "INSERT INTO users (fullname, email, password, contact_number, address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($user_sql);
        $stmt->bind_param("ssssss", $fullname, $email, $hashed_password, $contact_number, $address, $created_at);
        $stmt->execute();

        // Get the last inserted user_id
        $user_id = $conn->insert_id;

        // Insert into appointments table
        $appointment_sql = "INSERT INTO appointments (user_id, appointment_date, appointment_time, service, message, status) 
                            VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($appointment_sql);
        $stmt->bind_param("issss", $user_id, $appointment_date, $appointment_time, $service, $message);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect with success message
        header("Location: ../tables.php?success=1");
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
