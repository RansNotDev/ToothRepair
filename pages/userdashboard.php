<?php
date_default_timezone_set('Asia/Manila');
require("../database/db_connection.php"); // Add this line to include the database connection
include_once("../includes/header.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Add this helper function at the top of the file
function getProgressBarWidth($status) {
    switch($status) {
        case 'Booked': return "25%";
        case 'Pending': return "50%";
        case 'Confirmed': return "75%";
        case 'Completed': return "100%";
        case 'Cancelled': return "0%"; // Updated to 'Cancelled'
        default: return "25%";
    }
}


function getStatusClass($currentStatus, $status) {
    $statuses = ['Booked', 'Pending', 'Confirmed', 'Completed'];
    $currentIndex = array_search($currentStatus, $statuses);
    $statusIndex = array_search($status, $statuses);
    if($currentStatus === 'Cancelled') return 'bg-danger';
    return ($statusIndex <= $currentIndex) ? 'bg-primary' : 'bg-light text-dark';
}

// Fetch user's appointment with formatted time
$stmt = $conn->prepare("
    SELECT a.appointment_id, 
           a.appointment_date, 
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time, 
           a.status, 
           s.service_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC LIMIT 1
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
                <div class="d-flex justify-content-between align-items-start">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h1>
                        <p class="text-white-50">Here's your appointment overview</p>
                    </div>
                   
                </div>
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

                    <?php if ($appointment && $appointment['status'] === 'Cancelled'): ?>
                        <div class="alert alert-warning">
                            <h4>Appointment Cancelled</h4>
                            <p>Your appointment scheduled for <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> 
                               at <?php echo $appointment['appointment_time']; ?> was cancelled.</p>
                            <a href="book-appointment.php" class="btn btn-primary mt-3">Book New Appointment</a>
                        </div>
                    <?php elseif ($appointment): ?>
                        <div class="appointment-details">
                            <div class="progress-tracker mb-4">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo getProgressBarWidth($appointment['status'] ?? 'Booked'); ?>"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Booked'); ?>">
                                        Booked
                                    </span>
                                    <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Pending'); ?>">
                                        Pending
                                    </span>
                                    <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Confirmed'); ?>">
                                        Confirmed
                                    </span>
                                    <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Completed'); ?>">
                                        Completed
                                    </span>
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
                                        <p class="mb-0 fw-bold"><?php echo $appointment['appointment_time']; ?></p>
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
                                <button onclick="confirmCancelAppointment()" class="btn btn-outline-danger px-4 d-flex align-items-center gap-2">
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
            <div class="card border-0 shadow-sm rounded-lg quick-actions-card">
                <div class="card-body p-4">
                    <h5 class="card-title text-white mb-4">Quick Actions</h5>
                    <div class="d-grid gap-3">
                        <a href="book-appointment.php" class="btn quick-action-btn text-start p-3 d-flex align-items-center">
                            <i class="fas fa-calendar-plus me-3"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn quick-action-btn text-start p-3 d-flex align-items-center">
                            <i class="fas fa-history me-3"></i>
                            <span>View History</span>
                        </a>
                        <a href="profile.php" class="btn quick-action-btn text-start p-3 d-flex align-items-center">
                            <i class="fas fa-user me-3"></i>
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn quick-action-btn text-start p-3 d-flex align-items-center">
                            <i class="fas fa-sign-out-alt me-3"></i>
                            <span>Logout</span>
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

.dashboard-header .btn-light {
    transition: all 0.3s;
    background: rgba(255,255,255,0.9);
    border: none;
    padding: 0.5rem 1rem;
}

.dashboard-header .btn-light:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .dashboard-header .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .dashboard-header .btn-light {
        margin-bottom: 0.5rem;
    }
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

.quick-actions-card {
    background: #ffffff;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.quick-actions-card .card-title {
    color: #4e73df;
}

.quick-action-btn {
    background: rgba(78, 115, 223, 0.1);
    color: #4e73df;
    border: none;
    transition: all 0.3s;
}

.quick-action-btn:hover {
    background: rgba(78, 115, 223, 0.2);
    color: #4e73df;
    transform: translateX(5px);
}

.quick-action-btn i {
    color: #4e73df;
}

@media (max-width: 768px) {
    .quick-actions-card {
        margin-top: 1rem;
    }
}

.header-action-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: none;
    transition: all 0.3s;
    text-align: left;
    padding: 0.5rem 1rem;
    width: 100%;
    border-radius: 0.5rem;
}

.header-action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: translateX(5px);
}

.quick-actions {
    min-width: 200px;
}

@media (max-width: 768px) {
    .dashboard-header .d-flex {
        flex-direction: column;
    }
    
    .quick-actions {
        width: 100%;
        margin-top: 1rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of your account",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
    return false;
}

function cancelAppointment(appointmentId) {
    const reason = prompt("Please enter the reason for cancellation:");
    if (reason === null) return; // User clicked Cancel

    $.ajax({
        url: 'pages/cancel_appointment.php',
        type: 'POST',
        data: {
            appointment_id: appointmentId,
            cancel_reason: reason
        },
        success: function(response) {
            if (response.success) {
                alert('Appointment cancelled successfully');
                // Update progress bar or status display
                updateAppointmentStatus(appointmentId, 'Cancelled');
                // Refresh the page or update UI
                location.reload();
            } else {
                alert('Failed to cancel appointment: ' + response.message);
            }
        },
        error: function() {
            alert('Error processing request');
        }
    });
}

function updateAppointmentStatus(appointmentId, status) {
    const statusElement = document.querySelector(`[data-appointment-id="${appointmentId}"] .status`);
    if (statusElement) {
        statusElement.textContent = status;
    }
}

function confirmCancelAppointment() {
    const appointmentId = <?php echo $appointment['appointment_id'] ?? 'null'; ?>;
    if (!appointmentId) {
        Swal.fire('Error', 'No appointment found', 'error');
        return;
    }

    Swal.fire({
        title: 'Cancel Appointment',
        text: 'Please provide a reason for cancellation:',
        input: 'textarea',
        inputPlaceholder: 'Enter your reason here...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Cancel Appointment',
        cancelButtonText: 'Keep Appointment',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'cancel_appointment.php',
                type: 'POST',
                data: { 
                    appointment_id: appointmentId,
                    cancel_reason: result.value
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled!',
                            text: 'Your appointment has been cancelled and a confirmation email has been sent.',
                            confirmButtonText: 'OK'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error!', response.message || 'Could not cancel appointment.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Could not connect to server.', 'error');
                }
            });
        }
    });
}
</script>

<?php include_once("../includes/footer.php"); ?>