<?php
session_start();
include_once('../database/db_connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['date'];
        $isChecked = $_POST['checked'] === 'true';
        
        if ($isChecked) {
            $timeStart = $_POST['timeStart'];
            $timeEnd = $_POST['timeEnd'];
            $maxDailyAppointments = (int)$_POST['max_daily_appointments'];
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Check if date exists
            $checkStmt = $conn->prepare("SELECT id FROM availability_tb WHERE available_date = ?");
            $checkStmt->bind_param("s", $date);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE availability_tb 
                                      SET time_start = ?, 
                                          time_end = ?, 
                                          max_daily_appointments = ? 
                                      WHERE available_date = ?");
                $stmt->bind_param("ssis", $timeStart, $timeEnd, $maxDailyAppointments, $date);
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO availability_tb 
                                      (available_date, time_start, time_end, max_daily_appointments) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $date, $timeStart, $timeEnd, $maxDailyAppointments);
            }
            
            $success = $stmt->execute();
            
            if (!$success) {
                throw new Exception("Failed to save availability: " . $stmt->error);
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Availability saved successfully',
                'data' => [
                    'date' => $date,
                    'timeStart' => $timeStart,
                    'timeEnd' => $timeEnd,
                    'maxDailyAppointments' => $maxDailyAppointments
                ]
            ]);
            
        } else {
            // Delete availability
            $stmt = $conn->prepare("DELETE FROM availability_tb WHERE available_date = ?");
            $stmt->bind_param("s", $date);
            $success = $stmt->execute();
            
            if (!$success) {
                throw new Exception("Failed to remove availability: " . $stmt->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Availability removed successfully'
            ]);
        }
        
    } catch (Exception $e) {
        if ($conn->connect_error) {
            $error = "Database connection failed: " . $conn->connect_error;
        } else {
            $error = $e->getMessage();
        }
        
        if (isset($conn) && $conn->connect_errno === 0) {
            $conn->rollback();
        }
        
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>
