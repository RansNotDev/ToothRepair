<?php
include_once('../database/db_connection.php'); // Ensure this connects to your database

// Get the ID from the query string
if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Fetch appointment data from the database
    $query = "
        SELECT 
            a.appointment_id, 
            u.fullname AS patient_name, 
            a.appointment_date, 
            a.appointment_time, 
            a.service, 
            a.status
        FROM appointments a
        INNER JOIN users u ON a.user_id = u.user_id
        WHERE a.appointment_id = $appointment_id
    ";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $appointment = mysqli_fetch_assoc($result);
        ?>
        <div>
            <h4>Appointment Details</h4>
            <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
            <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($appointment['appointment_time']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($appointment['status']); ?></p>
        </div>
        <?php
    } else {
        echo "<p>Details not found for this appointment.</p>";
    }
} else {
    echo "<p>Invalid request. No appointment ID provided.</p>";
}
?>
