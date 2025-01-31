<?php
// create_admin.php
require_once '../includes/db_connection.php';

$username = 'admin';
$password = password_hash('1234', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param('ss', $username, $password);
$stmt->execute();