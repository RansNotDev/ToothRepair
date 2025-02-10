<?php
require("../database/db_connection.php");
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
        .active {
        background-color: #007bff !important;
        color: white !important;
    }
    </style>
</head>
<body>
<div class="container-fluid py-4 bg-light">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary p-4 rounded shadow-sm">
                <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h1>
                <p class="text-white-50">Here's your appointment overview</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointment History Card -->
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

        <!-- Quick Actions Card -->
<div class="col-lg-4 col-md-12 mb-4">
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body p-4">
            <h5 class="card-title text-primary mb-4">Quick Actions</h5>
            <div class="d-grid gap-3">
                <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                    <i class="text-primary me-3"></i>
                    <span>Home</span>
                </a>
                <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                    <i class="text-primary me-3"></i>
                    <span>Book New Appointment</span>
                </a>
                <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                    <i class="text-primary me-3"></i>
                    <span>View History</span>
                </a>
                <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                    <i class="text-primary me-3"></i>
                    <span>Update Profile</span>
                </a>
                <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center">
                    <i class="text-primary me-3"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
<script>
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
</body>
</html>