<?php
include_once('../database/db_connection.php');

// Fetch new users (last 10 days)
$new_users_query = "SELECT COUNT(*) as count FROM users 
                    WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)"; 
$new_users_result = mysqli_query($conn, $new_users_query);
$new_users = mysqli_fetch_assoc($new_users_result)['count'] ?? 0;

// Fetch today's appointments
$today_appt_query = "SELECT COUNT(*) as count FROM appointments 
                     WHERE DATE(appointment_date) = CURDATE()
                     AND status != 'deleted'";
$today_appt_result = mysqli_query($conn, $today_appt_query);
$today_appointments = mysqli_fetch_assoc($today_appt_result)['count'] ?? 0;

// Fetch new appointments (last 24 hours)
$new_appt_query = "SELECT COUNT(*) as count FROM appointments 
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                   AND status != 'deleted'";
$new_appt_result = mysqli_query($conn, $new_appt_query);
$new_appointments = mysqli_fetch_assoc($new_appt_result)['count'] ?? 0;

// Calculate total notifications
$total_notifications = $new_users + $new_appointments + $today_appointments;

// Add error handling
if (!$new_users_result || !$today_appt_result || !$new_appt_result) {
    error_log("Database query error: " . mysqli_error($conn));
}
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content">
        <!-- Topbar -->
         
<nav class="navbar navbar-expand navbar-light bg-blue topbar mb-5 static-top shadow">
           
<!-- Update the notifications section -->
<ul class="navbar-nav ml-auto">
    <!-- Nav Item - Alerts -->
    <li class="nav-item dropdown no-arrow mx-1">
        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-bell fa-fw"></i>
            <!-- Counter - Alerts -->
            <?php if ($total_notifications > 0): ?>
            <span class="badge badge-danger badge-counter"><?= $total_notifications ?></span>
            <?php endif; ?>
        </a>
        <!-- Dropdown - Alerts -->
        <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
            aria-labelledby="alertsDropdown">
            <h6 class="dropdown-header">
                Notifications Center
            </h6>
            <?php if ($new_users > 0): ?>
            <a class="dropdown-item d-flex align-items-center" href="registered_users.php">
                <div class="mr-3">
                    <div class="icon-circle bg-primary">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">Last 10 days</div>
                    <span class="font-weight-bold"><?= $new_users ?> New registered user(s)</span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($new_appointments > 0): ?>
            <a class="dropdown-item d-flex align-items-center" href="appointments.php">
                <div class="mr-3">
                    <div class="icon-circle bg-success">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">Last 24 hours</div>
                    <span class="font-weight-bold"><?= $new_appointments ?> New appointment(s)</span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($today_appointments > 0): ?>
            <a class="dropdown-item d-flex align-items-center" href="calendar.php">
                <div class="mr-3">
                    <div class="icon-circle bg-warning">
                        <i class="fas fa-calendar-day text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500"><?= date('F d, Y') ?></div>
                    <span class="font-weight-bold"><?= $today_appointments ?> Appointment(s) today</span>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </li>

    <div class="topbar-divider d-none d-sm-block"></div>

    <!-- Nav Item - User Information -->
    <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                <?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin' ?>
            </span>
            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
        </a>
        <!-- Dropdown - User Information -->
        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
            aria-labelledby="userDropdown">
            <a class="dropdown-item" href="profile.php">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                Profile
            </a>
            <a class="dropdown-item" href="settings.php">
                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                Settings
            </a>
            <a class="dropdown-item" href="activity_logs.php">
                <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                Activity Log
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                Logout
            </a>
        </div>
    </li>
</ul>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="../logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>
        </nav>
        <!-- End of Topbar -->