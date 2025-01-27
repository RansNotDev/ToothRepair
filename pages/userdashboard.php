<?php
require("../database/db_connection.php"); // Add this line to include the database connection
include_once("../includes/header.php");
include_once("../includes/topbar.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's appointment
$stmt = $conn->prepare("SELECT appointment_date, appointment_time, service, status FROM appointments WHERE user_id = ? ORDER BY appointment_date LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

?>

<div class="container-fluid min-vh-100 overflow-hidden d-flex flex-column">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>

    <div class="container mt-5 flex-grow-1">
        <div class="text-center">
            <h3 class="text-primary">Dental Clinic Appointment Status</h3>
            <p>Your appointment details are below:</p>
            <div class="my-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 50px; background-color: blue;"></i>
            </div>
        </div>

        <!-- Appointment Details Card -->
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <?php if ($appointment): ?>
                    <h5 class="card-title"><?php echo htmlspecialchars($appointment['service']); ?></h5>
                    <p class="card-text">
                        <strong>Date of Appointment:</strong>
                        <?php echo date('M-d-Y', strtotime($appointment['appointment_date'])); ?> <br>
                        <strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                        <br>
                        <strong>Status:</strong> <?php echo htmlspecialchars($appointment['status']); ?>
                    </p>
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="#" class="btn btn-primary">Add to Google Calendar</a>
                        <form method="POST" action="">
                            <input type="hidden" name="appointment_id" value="<?php echo isset($appointment['id']) ? htmlspecialchars($appointment['id']) : ''; ?>">
                            <button type="submit" name="temporary_logout" class="btn btn-secondary">Temporary Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>No upcoming appointments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        if (!confirm('Are you sure you want to cancel this appointment?')) {
            e.preventDefault();
        }
    });
</script>

<?php include_once("../includes/footer.php"); ?>
</div>

<?php
session_start();

if (isset($_POST['temporary_logout'])) {
    // Destroy the session to log out the user temporarily
    session_destroy();
    // Redirect to the login page or any other page
    header("Location: loginpage.php");
    exit();
}
?>