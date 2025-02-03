<?php
$host = 'localhost';
$username = 'root'; 
$password = '';
$database = 'toothrepair_clinic_db';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start transaction (if needed)
$conn->begin_transaction();
?>
