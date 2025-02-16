<?php
require("../database/db_connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
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


    <style>
body 
{
    background-color: #f8f9fc;
}
        .card {
            transition: none !important;
        }
        .card:hover {
            transform: none !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
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
        background-color:rgb(51, 94, 139) !important;
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

        /* Improve table responsiveness */
        .table-responsive {
            margin: 0;
            padding: 0;
        }

        .table td, .table th {
            vertical-align: middle;
        }

/* Update/replace the CSS styles in appointment-history.php */
.quick-action-btn {
    transition: none !important;
    border: none !important;
    box-shadow: none !important;
    margin-bottom: 5px;
    position: relative;
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
                        <img src="../images/logo/cliniclogo.png" alt="Tooth Repair Logo" class="mr-3" style="height: 80px; width: auto;">
                        <h2 class="h4 text-primary mb-0">Tooth Repair Dental Clinic</h2>
                    </div>
                    <!-- Right side - Welcome message -->
                    <div class="text-right">
                        <h1 class="h3 text-primary fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></h1>
                        <p class="text-primary mb-0">Here's your appointment overview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Quick Actions Card - Left Column -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-lg position-sticky" style="top: 20px;">
                <div class="card-body p-4">
                    
                    <div class="d-grid gap-3">
                        <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-home text-primary me-3"></i>
                            <span>Home</span>
                        </a>
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-history text-primary me-3"></i>
                            <span>View History</span>
                        </a>
                        <a href="user_feedback.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-comment-dots text-primary me-3"></i>
                            <span>Feed Back</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-user text-primary me-3"></i>
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-sign-out-alt text-primary me-3"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment History Card - Right Column -->
        <div class="col-lg-8 col-md-12 mb-4">
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
                    <div class="mt-4">
                        <a href="userdashboard.php" class="btn btn-secondary">
                            <i class=""></i> Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let links = document.querySelectorAll(".quick-action-btn");
        let currentUrl = window.location.pathname.split("/").pop();

        links.forEach(link => {
            if (link.getAttribute("href") === currentUrl) {
                link.classList.add("active");
                link.querySelector("i").classList.remove("text-primary");
            }
        });
    });

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

</script>
</body>
</html>