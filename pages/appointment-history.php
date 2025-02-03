<?php
require("../database/db_connection.php");
include_once("../includes/header.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT a.*, s.service_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="card-title mb-4">Appointment History</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                            <td>
                                <span class="badge <?php echo $appointment['status'] === 'Confirmed' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo htmlspecialchars($appointment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>