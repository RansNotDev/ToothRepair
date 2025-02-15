<?php
session_start();
include_once('../database/db_connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT available_date, time_start, time_end, max_daily_appointments 
                           FROM availability_tb 
                           WHERE available_date >= CURRENT_DATE()");
    $stmt->execute();
    $result = $stmt->get_result();

    $availability = [];
    while ($row = $result->fetch_assoc()) {
        $availability[] = [
            'date' => $row['available_date'],
            'timeStart' => $row['time_start'],
            'timeEnd' => $row['time_end'],
            'max_daily_appointments' => $row['max_daily_appointments']
        ];
    }

    echo json_encode($availability);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}