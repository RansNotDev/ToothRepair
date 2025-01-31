<?php
// db_connection.php

$servername = "localhost"; // Replace with your database server
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$dbname = "toothrepair_clinic_db"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
