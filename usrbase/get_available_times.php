<?php
require("../database/db_connection.php");

if (isset($_GET['date'])) {
    $date = mysqli_real_escape_string($conn, $_GET['date']);
    
    $query = "SELECT time_start, time_end FROM availability_tb 
              WHERE available_date = '$date' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'available' => true,
            'time_start' => $row['time_start'],
            'time_end' => $row['time_end']
        ]);
    } else {
        echo json_encode(['available' => false]);
    }
}
?>