<?php
include_once('database/db_connection.php');
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$required_fields = [
    'fullname', 
    'email', 
    'contact_number',
    'address',
    'service',
    'appointment_date',
    'appointment_time'
];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
        exit;
    }
}

$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
$appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
$service = mysqli_real_escape_string($conn, $_POST['service']);

// Generate random password
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Generate and store the plain password before hashing
$plainPassword = generateRandomPassword(10);
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Check if email already exists
    $checkEmail = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        throw new Exception('Email already exists');
    }

    // Insert into users table first (removed role field)
    $userQuery = "INSERT INTO users (fullname, email, password, contact_number, address) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $userQuery);
    mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $hashedPassword, $contact_number, $address);
    mysqli_stmt_execute($stmt);
    $userId = mysqli_insert_id($conn);

    // Insert into appointments table
    $appointmentQuery = "INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, status) 
                        VALUES (?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $appointmentQuery);
    mysqli_stmt_bind_param($stmt, "iiss", $userId, $service, $appointment_date, $appointment_time);
    mysqli_stmt_execute($stmt);

    // Commit transaction
    mysqli_commit($conn);

    // Return success response with the plain password
    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment booked successfully',
        'password' => $plainPassword // Send the plain password back to the client
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error booking appointment: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>