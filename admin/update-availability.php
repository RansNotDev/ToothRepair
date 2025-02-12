<?php
session_start();
include_once('../database/db_connection.php');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'] ?? '';
    $checked = $_POST['checked'] ?? '';
    $timeStart = $_POST['timeStart'] ?? '';
    $timeEnd = $_POST['timeEnd'] ?? '';

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Date is required']);
        exit();
    }

    try {
        if ($checked === 'true') {
            // Insert or update availability
            $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end) 
                                  VALUES (?, ?, ?)
                                  ON DUPLICATE KEY UPDATE time_start = VALUES(time_start), time_end = VALUES(time_end)");
            $stmt->bind_param("sss", $date, $timeStart, $timeEnd);
        } else {
            // Remove availability
            $stmt = $conn->prepare("DELETE FROM availability_tb WHERE available_date = ?");
            $stmt->bind_param("s", $date);
        }

        $stmt->execute();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error updating availability']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
