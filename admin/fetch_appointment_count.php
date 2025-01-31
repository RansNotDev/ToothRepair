<?php
include_once('database/db_connection.php');

$result = $conn->query("SELECT available_date, time_start, time_end FROM availability_tb");
$availability = [];

while ($row = $result->fetch_assoc()) {
    $availability[$row['available_date']] = [
        "time_start" => $row['time_start'],
        "time_end" => $row['time_end']
    ];
}

echo json_encode($availability);
?>
