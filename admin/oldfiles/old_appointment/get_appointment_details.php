<?php
include_once('../database/db_connection.php');

$id = $_GET['id'];
$query = "SELECT patient_name, appointment_date, status FROM appointments WHERE id = $id";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found.']);
}
?>
