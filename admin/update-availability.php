<?php
include_once('../database/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $isChecked = $_POST['checked'] === 'true'; 

    if ($isChecked) {
        // Mark date as available
        $stmt = $conn->prepare("INSERT INTO availability_tb (available_date, time_start, time_end) VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE time_start = VALUES(time_start), time_end = VALUES(time_end)");
        $stmt->bind_param("sss", $date, $_POST['timeStart'], $_POST['timeEnd']);
        $stmt->execute();
        $stmt->close();
    } else {
        // Remove availability when unchecked
        $stmt = $conn->prepare("DELETE FROM availability_tb WHERE available_date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(["success" => true]);
    exit();
}
?>
