<?php
date_default_timezone_set('Asia/Manila');
require("../database/db_connection.php"); // Add this line to include the database connection

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
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

// Replace the existing query with this updated version
// Place this after session_start() and before the HTML

// First, get the current appointment
$stmt = $conn->prepare("
    SELECT a.appointment_id, 
           a.appointment_date, 
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time, 
           a.status, 
           s.service_name,
           s.service_id
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ? AND a.status IN ('pending', 'confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

// Then get all upcoming appointments
$stmt = $conn->prepare("
    SELECT a.appointment_id, 
           a.appointment_date, 
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time, 
           a.status, 
           s.service_name,
           s.service_id
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ? AND a.status IN ('pending', 'confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Add helper function for status badge colors
function getStatusBadge($status) {
    return match(strtolower($status)) {
        'booked' => 'warning',
        'pending' => 'info',
        'confirmed' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!--cdn online bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/fullcalendar/main.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css"  type="text/css">
    <link rel="stylesheet" href="../admin/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" >

    <!-- Online cdn bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">



    <style>
body {
            background-color: #f8f9fc;
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
    .quick-actions-card {
        margin-top: 1rem;
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



.upcoming-appointment {
    transition: transform 0.2s;
    border-left: 4px solid #4e73df;
}

.upcoming-appointment:hover {
    transform: translateX(5px);
}

.badge {
    padding: 0.5em 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.active {
        background-color: #007bff !important;
        color: white !important;
    }
.sticky-top {
    position: sticky;
    top: 20px;
    z-index: 1000;
}

@media (max-width: 991px) {
    .sticky-top {
        position: relative;
        top: 0;
    }
}

.quick-actions .btn {
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateX(5px);
    background-color: #f8f9fa;
}

.btn i {
    width: 20px;
    text-align: center;
}
</style>
</head>
<body>
    

<div class="container-fluid py-4  min-vh-100">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary  p-4 rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></h1>
                        <p class="text-white">Here's your appointment overview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <div class="row">
    <!-- Quick Actions Card - Left Column -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm rounded-lg sticky-top" style="top: 20px;">
            <div class="card-body p-4">
                <h5 class="card-title text-primary mb-4">Quick Actions</h5>
                <div class="d-grid gap-3">
                    <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                        <i class="fas fa-home text-primary me-3"></i>
                        <span>Home</span>
                    </a>
                    <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                        <i class="fas fa-calendar-plus text-primary me-3"></i>
                        <span>Book New Appointment</span>
                    </a>
                    <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                        <i class="fas fa-history text-primary me-3"></i>
                        <span>View History</span>
                    </a>
                    <a href="user_feedback.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-comment-dots text-primary"></i>
                            <span>Feed Back</span>
                        </a>
                    <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                        <i class="fas fa-user text-primary me-3"></i>
                        <span>Update Profile</span>
                    </a>
                    <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center">
                        <i class="fas fa-sign-out-alt text-primary me-3"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Cards - Right Column -->
    <div class="col-lg-9">
        <!-- Current Appointment Card -->
        <div class="card border-0 shadow-sm rounded-lg mb-4">
            <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title text-primary mb-0">Current Appointment</h5>
                        <?php if ($appointment): ?>
                        <span class="badge bg-<?php echo getStatusBadge($appointment['status']); ?> px-3 py-2 rounded-pill">
                            <?php echo htmlspecialchars(ucfirst($appointment['status'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($appointment && $appointment['status'] === 'cancelled'): ?>
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
    <?php if ($appointment): ?>
        <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php 
            echo urlencode('Dental Appointment - ' . $appointment['service_name']); 
            ?>&dates=<?php 
            $date = date('Ymd', strtotime($appointment['appointment_date']));
            $time = date('His', strtotime($appointment['appointment_time']));
            echo $date . 'T' . $time . '/' . $date . 'T' . date('His', strtotime($appointment['appointment_time'] . '+30 minutes'));
            ?>&details=<?php 
            echo urlencode('Dental appointment at ToothRepair Clinic\nService: ' . $appointment['service_name']); 
            ?>" 
            target="_blank" 
            class="btn btn-primary px-4 d-flex align-items-center gap-2">
            <i class="fas fa-calendar-plus"></i> Add to Calendar
        </a>
        
        <form id="cancelForm" action="cancel_appointment.php" method="POST" class="d-inline">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
            <button type="button" 
                    onclick="confirmCancelAppointment(<?php echo $appointment['appointment_id']; ?>)" 
                    class="btn btn-outline-danger px-4 d-flex align-items-center gap-2">
                <i class="fas fa-times"></i> Cancel Appointment
            </button>
        </form>
    <?php endif; ?>
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

        <!-- Upcoming Appointments Card -->
        <?php if (count($appointments) > 1): ?>
        <div class="card border-0 shadow-sm rounded-lg mb-4">
            <div class="card-body p-4">
                        <h5 class="card-title text-primary mb-4">Upcoming Appointments</h5>
                        <?php 
                        // Skip the first appointment as it's already shown
                        array_shift($appointments);
                        foreach ($appointments as $apt): 
                        ?>
                            <div class="upcoming-appointment mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($apt['service_name']); ?></h6>
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> at 
                                            <?php echo $apt['appointment_time']; ?>
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-<?php echo getStatusBadge($apt['status']); ?>">
                                            <?php echo ucfirst($apt['status']); ?>
                                        </span>
                                        <button onclick="confirmCancelAppointment(<?php echo $apt['appointment_id']; ?>)" 
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>



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

// Update the confirmCancelAppointment function in your script section

function confirmCancelAppointment(appointmentId) {
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
            const form = document.getElementById('cancelForm');
            // Add the reason to the form
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'cancel_reason';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);
            
            // Submit the form
            form.submit();
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
        let links = document.querySelectorAll(".card-body a");
        let currentUrl = window.location.pathname.split("/").pop();

        links.forEach(link => {
            if (link.getAttribute("href") === currentUrl) {
                link.classList.add("active");
            }
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/Assetscalendar/fullcalendar/main.js"></script>
</body>
</html>