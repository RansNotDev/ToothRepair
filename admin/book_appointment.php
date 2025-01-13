<?php
require_once "db_connection.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedDate = $_POST['date'];
    $appointmentType = $_POST['type'];
    $selectedTimeSlot = $_POST['time'];

    // TODO: 
    // 1. Validate the data (e.g., check if the time slot is still available).
    // 2. Get the user ID (you might need to implement a login system).
    // 3. Get the dentist ID (you might need to allow dentist selection).

    // Placeholder values (replace with actual data)
    $userId = 1; // Replace with the logged-in user's ID
    $dentistId = 1; // Replace with the selected dentist's ID

    // Insert appointment into database
    $sql = "INSERT INTO appointments (user_id, dentist_id, appointment_date, appointment_time, status, notes) 
            VALUES (?, ?, ?, ?, 'pending', ?)"; 

    $stmt = $conn->prepare($sql);

    // Extract start time from the selected time slot
    $startTime = explode('-', $selectedTimeSlot)[0];
    $startTime = date('H:i:s', strtotime($startTime)); // Convert to 24-hour format

    $stmt->bind_param("iisss", $userId, $dentistId, $selectedDate, $startTime, $appointmentType);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error booking appointment: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>