<?php
require_once 'database/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $service = trim($_POST['service']);
    $message = trim($_POST['message']);
    $terms = isset($_POST['terms']) ? 1 : 0;

    if (empty($fullname) || empty($email) || empty($contact_number) || empty($address) || empty($appointment_date) || empty($appointment_time) || empty($service) || !$terms) {
        die("Please fill in all required fields and agree to the terms and conditions.");
    }

    // Hash the default password
    $default_password = '1234';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    // Insert user data into the users table
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, contact_number, address, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $contact_number, $address, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert appointment data into the appointments table
        $status = 'pending';
        $created_at = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, appointment_time, service, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $appointment_date, $appointment_time, $service, $message, $status, $created_at);

        if ($stmt->execute()) {
            echo "<script>alert('Appointment booked successfully! You can now login.'); window.location.href='./pages/loginpage.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>