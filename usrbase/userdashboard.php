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
function getProgressBarWidth($status)
{
    switch ($status) {
        case 'Booked':
            return "25%";
        case 'Pending':
            return "50%";
        case 'Confirmed':
            return "75%";
        case 'Completed':
            return "100%";
        case 'Cancelled':
            return "0%"; // Updated to 'Cancelled'
        default:
            return "25%";
    }
}

// Update the getStatusClass function
function getStatusClass($currentStatus, $status) {
    $statuses = ['Booked', 'Pending', 'Confirmed', 'Completed'];
    $currentIndex = array_search($currentStatus, $statuses);
    $statusIndex = array_search($status, $statuses);
    
    if ($currentStatus === 'Cancelled') {
        return 'bg-danger';
    }
    
    // Define status-specific colors
    $statusColors = [
        'Booked' => 'bg-warning',     // Yellow
        'Pending' => 'bg-info',       // Blue
        'Confirmed' => 'bg-primary',  // Primary Blue
        'Completed' => 'bg-success'   // Green
    ];
    
    if ($statusIndex <= $currentIndex) {
        return $statusColors[$status];
    }
    
    return 'bg-light text-dark';
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
    return match (strtolower($status)) {
        'booked' => 'warning text-dark',    // Yellow with dark text
        'pending' => 'info text-white',     // Blue with white text
        'confirmed' => 'primary text-white', // Primary blue with white text
        'completed' => 'success text-white', // Green with white text
        'cancelled' => 'danger text-white',  // Red with white text
        default => 'secondary text-white'    // Gray with white text
    };
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!--cdn online bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/fullcalendar/main.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css" type="text/css">
    <link rel="stylesheet" href="../admin/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

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
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 0.5rem 1rem;
        }

        .dashboard-header .btn-light:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        ..active {
            background-color: rgb(19, 84, 153) !important;
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


        .quick-actions .btn:hover {
            background-color: #f8f9fa;
        }

        .btn i {
            width: 20px;
            text-align: center;
        }

        .bg-gradient-dark {
            background: rgb(152, 193, 233);
            position: relative;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.1));
            pointer-events: none;
        }

        /* Optional: Add a subtle glow effect to the logo */
        .dashboard-header img {
            filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.3));
            transition: filter 0.3s ease;
        }

        .dashboard-header img:hover {
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
        }

        .dashboard-header {
            position: relative;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.05);
            pointer-events: none;
        }

        .quick-action-btn {
            transition: none !important;
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 5px;
        }

        .quick-action-btn:hover {
            background-color: #f8f9fa !important;
            transform: none !important;
        }

        .quick-action-btn.active {
            background-color: rgba(98, 160, 223, 0.9) !important;
            color: white !important;
        }

        .quick-action-btn.active i {
            color: white !important;
        }

        .position-sticky {
            position: sticky !important;
            top: 20px !important;
            z-index: 1000;
        }

        .card {
            transition: none !important;
        }

        .card:hover {
            transform: none !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        /* Add these styles to your existing CSS */
        .progress-tracker .progress {
            background-color: #e9ecef;
            overflow: hidden;
            border-radius: 10px;
        }

        .progress-tracker .progress-bar {
            transition: width 0.6s ease;
            border-radius: 10px;
        }

        .progress-tracker .badge {
            font-size: 0.75rem;
            padding: 0.5em 1em;
            transition: all 0.3s ease;
        }

        /* Status-specific badge styles */
        .progress-tracker .bg-warning {
            background-color: #ffc107 !important;
            color: #000;
        }

        .progress-tracker .bg-info {
            background-color: #17a2b8 !important;
            color: #fff;
        }

        .progress-tracker .bg-primary {
            background-color: #0d6efd !important;
            color: #fff;
        }

        .progress-tracker .bg-success {
            background-color: #198754 !important;
            color: #fff;
        }

        .progress-tracker .bg-light {
            background-color: #e9ecef !important;
            color: #6c757d;
        }

        /* Status Badge Colors */
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #fff !important;
        }

        .badge.bg-primary {
            background-color: #0d6efd !important;
            color: #fff !important;
        }

        .badge.bg-success {
            background-color: #198754 !important;
            color: #fff !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .badge.bg-secondary {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        /* Progress Bar Styles */
        .progress {
            height: 8px !important;
            margin: 1rem 0;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(to right,
                #ffc107 0%,    /* Booked - Yellow */
                #0dcaf0 33%,   /* Pending - Info Blue */
                #0d6efd 66%,   /* Confirmed - Primary Blue */
                #198754 100%   /* Completed - Success Green */
            );
            transition: width .6s ease;
        }

        /* Badge Container Styles */
        .status-badges {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
        }

        .status-badges .badge {
            font-size: 0.75rem;
            padding: 0.5em 1em;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .status-badges .badge:hover {
            transform: translateY(-2px);
        }

        /* Add these styles for better responsiveness */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767.98px) {
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
            }
            
            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 1rem;
                font-weight: 600;
                text-align: left;
            }
        }

        @media (max-width: 767.98px) {
            .mobile-logo {
                height: 20px !important;
                width: auto !important;
            }
            .mobile-clinic-name {
                font-size: 0.9rem !important;
                margin-left: 0.5rem !important;
            }
        }

        /* Notification bell styles */
        .notification-icon {
            position: relative;
            display: inline-block;
        }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
            border: none;
            background: none;
            outline: none;
            transition: transform 0.2s ease;
        }
        
        .notification-bell:hover {
            transform: scale(1.1);
        }
        
        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            padding: 0.25em 0.4em;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .notification-bell.blink {
            animation: bell-ring 0.5s ease-in-out 3;
        }
        
        @keyframes bell-ring {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            50% { transform: rotate(-15deg); }
            75% { transform: rotate(10deg); }
            100% { transform: rotate(0deg); }
        }
    </style>
</head>

<body>


    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-12 mx-0 px-0">
                <div class="dashboard-header bg-gradient-dark p-4 rounded-0 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Left side - Logo and Title -->
                        <div class="d-flex align-items-center">
                            <img src="../images/logo/cliniclogo.png" alt="Tooth Repair Logo" class="mr-3"
                                style="height: 80px; width: auto;"
                                class="mobile-logo">
                            <h2 class="h4 text-primary mb-0 mobile-clinic-name">Tooth Repair Dental Clinic</h2>
                        </div>
                        <!-- Right side - Welcome message -->
                        <div class="text-right position-relative">
                            <h1 class="h3 text-primary fw-bold">Welcome Back,
                                <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></h1>
                            <p class="text-primary mb-0">Here's your appointment overview</p>
                            <!-- Notification bell with count -->
                            <div class="position-absolute top-0 end-0">
                                <div class="notification-icon">
                                    <button class="btn btn-link p-0 notification-bell" style="font-size: 1.5rem;">
                                        <i class="fas fa-bell text-warning"></i>
                                        <span class="notification-count badge bg-danger">1</span>
                                    </button>
                                </div>
                            </div>
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
                <div class="card border-0 shadow-sm rounded-lg position-sticky" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="d-grid gap-3">
                            <a href="userdashboard.php"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                                <i class="fas fa-home text-primary me-3"></i>
                                <span>Home</span>
                            </a>
                            <a href="book-appointment.php"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                                <i class="fas fa-calendar-plus text-primary me-3"></i>
                                <span>Book New Appointment</span>
                            </a>
                            <a href="appointment-history.php"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                                <i class="fas fa-history text-primary me-3"></i>
                                <span>View History</span>
                            </a>
                            <a href="user_feedback.php"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                                <i class="fas fa-comment-dots text-primary me-3"></i>
                                <span>Feed Back</span>
                            </a>
                            <a href="profile.php"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                                <i class="fas fa-user text-primary me-3"></i>
                                <span>Update Profile</span>
                            </a>
                            <a href="logout.php" onclick="return confirmLogout();"
                                class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
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
                                <p>Your appointment scheduled for
                                    <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                    at <?php echo $appointment['appointment_time']; ?> was cancelled.</p>
                                <a href="book-appointment.php" class="btn btn-primary mt-3">Book New Appointment</a>
                            </div>
                        <?php elseif ($appointment): ?>
                            <div class="appointment-details">
                                <div class="progress-tracker mb-4">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                            style="width: <?php echo getProgressBarWidth($appointment['status'] ?? 'Booked'); ?>">
                                        </div>
                                    </div>
                                    <div class="status-badges">
                                        <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Booked'); ?>">
                                            <i class="fas fa-calendar-check me-1"></i> Booked
                                        </span>
                                        <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Pending'); ?>">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                        <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Confirmed'); ?>">
                                            <i class="fas fa-check-circle me-1"></i> Confirmed
                                        </span>
                                        <span class="badge <?php echo getStatusClass($appointment['status'] ?? 'Booked', 'Completed'); ?>">
                                            <i class="fas fa-flag-checkered me-1"></i> Completed
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="detail-card p-3 bg-light rounded">
                                            <i class="fas fa-calendar-alt text-primary mb-2"></i>
                                            <h6 class="text-muted">Date</h6>
                                            <p class="mb-0 fw-bold">
                                                <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                            </p>
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
                                            <p class="mb-0 fw-bold">
                                                <?php echo htmlspecialchars($appointment['service_name']); ?></p>
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
                                        ?>" target="_blank" class="btn btn-primary px-4 d-flex align-items-center gap-2">
                                            <i class="fas fa-calendar-plus"></i> Add to Calendar
                                        </a>

                                        <form id="cancelForm" action="cancel_appointment.php" method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id"
                                                value="<?php echo $appointment['appointment_id']; ?>">
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
                                <img src="../assets/images/no-appointment.svg" alt="No Appointments" class="mb-3"
                                    style="width: 150px;">
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
                success: function (response) {
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
                error: function () {
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

        // Update the DOMContentLoaded event listener in your script section
        document.addEventListener("DOMContentLoaded", function () {
            let links = document.querySelectorAll(".quick-action-btn");
            let currentUrl = window.location.pathname.split("/").pop();

            links.forEach(link => {
                if (link.getAttribute("href") === currentUrl) {
                    link.classList.add("active");
                }
            });
        });

        // Add this script to make the bell blink and ring every minute
        document.addEventListener("DOMContentLoaded", function() {
            const notificationBell = document.querySelector('.notification-bell');
            
            function ringBell() {
                // Add the animation class
                notificationBell.classList.add('blink');
                
                // Remove the animation class after it completes
                setTimeout(() => {
                    notificationBell.classList.remove('blink');
                }, 1500); // 0.5s * 3 = 1.5s
            }
            
            // Initial ring
            ringBell();
            
            // Ring every minute
            setInterval(ringBell, 6000);
            
            // Optional: Add click handler for notifications
            notificationBell.addEventListener('click', function() {
                // Add your notification handling logic here
                console.log('Notification bell clicked');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/Assetscalendar/fullcalendar/main.js"></script>
</body>

</html>