<?php
$host = "localhost"; // Database host (usually "localhost")
$user = "root";      // Database username (default is "root" in XAMPP)
$pass = "";          // Database password (empty by default in XAMPP)
$dbname = "toothrepair_clinic_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start transaction (if needed)
$conn->begin_transaction();
?>
