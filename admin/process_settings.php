<?php
session_start();
include_once('../database/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

try {
    // Sanitize inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $about_content = htmlspecialchars($_POST['about_content']);

    // Handle file uploads
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $logo_path = null;
    $cover_path = null;

    // Process logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo_info = pathinfo($_FILES['logo']['name']);
        $logo_ext = strtolower($logo_info['extension']);
        
        if (in_array($logo_ext, ['jpg', 'jpeg', 'png'])) {
            $logo_path = $uploadDir . 'logo_' . time() . '.' . $logo_ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path);
        }
    }

    // Process cover upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $cover_info = pathinfo($_FILES['cover']['name']);
        $cover_ext = strtolower($cover_info['extension']);
        
        if (in_array($cover_ext, ['jpg', 'jpeg', 'png'])) {
            $cover_path = $uploadDir . 'cover_' . time() . '.' . $cover_ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path);
        }
    }

    // Prepare SQL query
    $query = "INSERT INTO clinic_settings 
              (email, contact_number, address, about_content";
    $values = "VALUES (?, ?, ?, ?";
    $types = "ssss";
    $params = [$email, $contact_number, $address, $about_content];

    if ($logo_path) {
        $query .= ", logo";
        $values .= ", ?";
        $types .= "s";
        $params[] = $logo_path;
    }

    if ($cover_path) {
        $query .= ", cover";
        $values .= ", ?";
        $types .= "s";
        $params[] = $cover_path;
    }

    $query .= ") " . $values . ") ON DUPLICATE KEY UPDATE 
              email=VALUES(email), 
              contact_number=VALUES(contact_number),
              address=VALUES(address),
              about_content=VALUES(about_content)";

    if ($logo_path) {
        $query .= ", logo=VALUES(logo)";
    }
    if ($cover_path) {
        $query .= ", cover=VALUES(cover)";
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Settings updated successfully!';
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>