<?php
$host = 'localhost';
$user = 'root';
$pass = '';  // Your database password
$db = 'toothrepair_clinic_db'; // The correct database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>