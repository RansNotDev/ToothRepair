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

$fullname = htmlspecialchars($_POST['fullname']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$contact_number = htmlspecialchars($_POST['contact_number']);
$address = htmlspecialchars($_POST['address']);
$service_id = intval($_POST['service']);

// Format the date to ensure consistency
$appointment_date = date('Y-m-d', strtotime($_POST['appointment_date']));
$appointment_time = $_POST['appointment_time'];

try {
    $conn->begin_transaction();

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $default_password = password_hash('1234', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, contact_number, address) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $default_password, $contact_number, $address);
        $stmt->execute();
        $user_id = $conn->insert_id;
    } else {
        $user_id = $result->fetch_assoc()['user_id'];
    }

    // Check if service exists
    $stmt = $conn->prepare("SELECT service_id FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Invalid service selection.');
    }

    // Check slot availability with timezone-aware date
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments 
                           WHERE appointment_date = ? 
                           AND appointment_time = ? 
                           AND status IN ('confirmed', 'pending')");
    $stmt->bind_param("ss", $appointment_date, $appointment_time);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];

    if ($count > 0) {
        throw new Exception('The selected time slot is not available.');
    }

    // Insert appointment with timezone-aware date
    $stmt = $conn->prepare("INSERT INTO appointments 
                           (user_id, service_id, appointment_date, appointment_time, status) 
                           VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiss", $user_id, $service_id, $appointment_date, $appointment_time);

    if (!$stmt->execute()) {
        throw new Exception("Error saving appointment: " . $stmt->error);
    }

    $conn->commit();
    echo json_encode([
        'status' => 'success', 
        'message' => 'Appointment booked successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Appointment booking error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>