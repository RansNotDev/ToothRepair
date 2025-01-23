<?php
// Include database connection
include_once('db_connection.php');

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']); // Get appointment ID from the URL

    // Fetch appointment details from the database
    $query = "
        SELECT 
            a.appointment_id AS id, 
            u.username AS patient_name, 
            a.appointment_date, 
            a.appointment_time, 
            a.service, 
            a.status, 
            a.payment_status, 
            a.total_cost, 
            a.notes, 
            d.dentist_name
        FROM appointments a
        INNER JOIN users u ON a.user_id = u.user_id
        INNER JOIN dentists d ON a.dentist_id = d.dentist_id
        WHERE a.appointment_id = $appointment_id
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $appointment = mysqli_fetch_assoc($result);

        // Render appointment details
        echo "
        <div>
            <p><strong>Name:</strong> {$appointment['patient_name']}</p>
            <p><strong>Date:</strong> {$appointment['appointment_date']}</p>
            <p><strong>Time:</strong> {$appointment['appointment_time']}</p>
            <p><strong>Service:</strong> {$appointment['service']}</p>
            <p><strong>Status:</strong> {$appointment['status']}</p>
            <p><strong>Payment Status:</strong> {$appointment['payment_status']}</p>
            <p><strong>Total Cost:</strong> \${$appointment['total_cost']}</p>
            <p><strong>Dentist:</strong> {$appointment['dentist_name']}</p>
            <p><strong>Notes:</strong> " . (!empty($appointment['notes']) ? $appointment['notes'] : 'No notes available') . "</p>
        </div>";
    } else {
        echo "<p>Invalid appointment ID or appointment not found.</p>";
    }
} else {
    echo "<p>No appointment ID provided.</p>";
}
?>
