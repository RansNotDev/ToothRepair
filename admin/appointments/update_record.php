<?php
require_once('../../database/db_connection.php');

header('Content-Type: application/json');

try {
    if (empty($_POST['user_id']) || empty($_POST['fullname']) || 
        empty($_POST['contact_number']) || empty($_POST['address'])) {
        throw new Exception('Required fields are missing');
    }

    $user_id = $_POST['user_id'];
    $fullname = trim($_POST['fullname']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    // First get the existing email
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Only update email if a new one is provided, otherwise keep existing
    $email_to_use = !empty($_POST['email']) ? $email : $user['email'];

    // Update user information
    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname = ?, 
            email = ?, 
            contact_number = ?, 
            address = ? 
        WHERE user_id = ?
    ");

    $stmt->bind_param("ssssi", 
        $fullname,
        $email_to_use,
        $contact_number,
        $address,
        $user_id
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Record updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update record');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();