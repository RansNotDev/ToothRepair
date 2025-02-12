<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['available_dates']) && isset($_POST['time_start']) && 
        isset($_POST['time_end']) && isset($_POST['max_appointments'])) {
        
        $dates = $_POST['available_dates'];
        $timeStart = date("H:i:s", strtotime($_POST['time_start']));
        $timeEnd = date("H:i:s", strtotime($_POST['time_end']));
        $maxAppointments = intval($_POST['max_appointments']);

        try {
            $conn->begin_transaction();

            // Update max_daily_appointments in clinic_settings
            $updateSettings = $conn->prepare("UPDATE clinic_settings SET max_daily_appointments = ?");
            $updateSettings->bind_param("i", $maxAppointments);
            $updateSettings->execute();

            // Clear existing availability
            $conn->query("DELETE FROM availability_tb");
            
            // Insert new availability
            $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end) VALUES (?, ?, ?)");
            
            foreach ($dates as $date) {
                if (DateTime::createFromFormat('Y-m-d', $date)) {
                    $stmt->bind_param("sss", $date, $timeStart, $timeEnd);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Error saving data: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>