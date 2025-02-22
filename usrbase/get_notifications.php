<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../database/db_connection.php";
session_start();

// Debugging: Check if session is working
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Debugging: Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch notifications from database
    $stmt = $conn->prepare("
        SELECT notification_id, title, message, created_at 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->bind_param("i", $user_id)) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'title' => htmlspecialchars($row['title']),
            'message' => htmlspecialchars($row['message']),
            'date' => date('M d, Y h:i A', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode([
        'count' => count($notifications),
        'notifications' => $notifications
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Notification Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => $e->getMessage()]);
} 