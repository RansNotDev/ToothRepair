<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $dates = $_POST['available_dates'];
        $timeStart = date("H:i:s", strtotime($_POST['time_start']));
        $timeEnd = date("H:i:s", strtotime($_POST['time_end']));
        $maxAppointments = intval($_POST['max_daily_appointments']);

        $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end, max_daily_appointments) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE 
                               time_start = VALUES(time_start), 
                               time_end = VALUES(time_end), 
                               max_daily_appointments = VALUES(max_daily_appointments)");

        foreach ($dates as $date) {
            if (DateTime::createFromFormat('Y-m-d', $date)) {
                $stmt->bind_param("sssi", $date, $timeStart, $timeEnd, $maxAppointments);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>