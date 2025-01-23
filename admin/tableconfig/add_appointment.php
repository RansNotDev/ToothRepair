<?php
include_once('../db_connection.php'); // Database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password using bcrypt
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $service = $_POST['service'];
    $status = $_POST['status'];
    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $user_sql = "INSERT INTO users (fullname, username, email, password, contact_number, address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($user_sql);
        $stmt->bind_param("sssssss", $fullname, $username, $email, $password, $contact_number, $address, $created_at);
        $stmt->execute();

        // Get the last inserted user_id
        $user_id = $conn->insert_id;

        // Insert into appointments table
        $appointment_sql = "INSERT INTO appointments (user_id, appointment_date, service, status) 
                            VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($appointment_sql);
        $stmt->bind_param("isss", $user_id, $appointment_date, $service, $status);
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
