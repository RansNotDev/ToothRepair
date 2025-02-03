<?php
require("../database/db_connection.php"); // Add this line to include the database connection
include_once("../includes/header.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's appointment
$stmt = $conn->prepare("
    SELECT a.appointment_date, a.appointment_time, a.status, s.service_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

?>

<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary p-4 rounded shadow-sm">
                <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h1>
                <p class="text-white-50">Here's your appointment overview</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointment Status Card -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title text-primary mb-0">Current Appointment</h5>
                        <span class="badge <?php echo $appointment['status'] === 'Confirmed' ? 'bg-success' : 'bg-warning'; ?> px-3 py-2 rounded-pill">
                            <?php echo htmlspecialchars($appointment['status'] ?? 'No Status'); ?>
                        </span>
                    </div>

                    <?php if ($appointment): ?>
                        <div class="appointment-details">
                            <div class="progress-tracker mb-4">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="badge bg-primary">Booked</span>
                                    <span class="badge bg-primary">Confirmed</span>
                                    <span class="badge bg-light text-dark">In Progress</span>
                                    <span class="badge bg-light text-dark">Completed</span>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="detail-card p-3 bg-light rounded">
                                        <i class="fas fa-calendar-alt text-primary mb-2"></i>
                                        <h6 class="text-muted">Date</h6>
                                        <p class="mb-0 fw-bold"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card p-3 bg-light rounded">
                                        <i class="fas fa-clock text-primary mb-2"></i>
                                        <h6 class="text-muted">Time</h6>
                                        <p class="mb-0 fw-bold"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card p-3 bg-light rounded">
                                        <i class="fas fa-tooth text-primary mb-2"></i>
                                        <h6 class="text-muted">Service</h6>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($appointment['service_name']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons mt-4 d-flex gap-3">
                                <button class="btn btn-primary px-4 d-flex align-items-center gap-2">
                                    <i class="fab fa-google"></i> Add to Calendar
                                </button>
                                <button class="btn btn-outline-danger px-4 d-flex align-items-center gap-2">
                                    <i class="fas fa-times"></i> Cancel Appointment
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-appointment.svg" alt="No Appointments" class="mb-3" style="width: 150px;">
                            <h5>No Upcoming Appointments</h5>
                            <p class="text-muted">Schedule your next visit now</p>
                            <a href="book-appointment.php" class="btn btn-primary px-4">Book Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary mb-4">Quick Actions</h5>
                    <div class="d-grid gap-3">
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-history text-primary me-3"></i>
                            <span>View History</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-user text-primary me-3"></i>
                            <span>Update Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    background: linear-gradient(to right, #4e73df, #224abe);
    padding: 2rem;
    border-radius: 1rem;
    color: white;
    margin-bottom: 2rem;
}

.detail-card {
    transition: transform 0.2s;
}

.detail-card:hover {
    transform: translateY(-5px);
}

.action-buttons .btn {
    transition: all 0.3s;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
}

.progress-tracker .badge {
    font-size: 0.8rem;
}

.quick-actions .btn {
    transition: all 0.3s;
}

.quick-actions .btn:hover {
    background:rgb(255, 255, 255);
    transform: translateX(5px);
}
</style>

<?php include_once("../includes/footer.php"); ?>

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