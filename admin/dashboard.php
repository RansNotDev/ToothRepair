<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

// Get appointment stats
$stats_query = "SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN status IN ('pending','booked') THEN 1 ELSE 0 END) as upcoming
FROM appointments";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get monthly appointments
$monthly_query = "SELECT 
    DATE_FORMAT(appointment_date, '%Y-%m') as month,
    COUNT(*) as total
FROM appointments 
WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY month 
ORDER BY month";
$monthly_result = $conn->query($monthly_query);

// Get popular services
$services_query = "SELECT 
    s.service_name,
    COUNT(*) as total_bookings
FROM appointments a
JOIN services s ON a.service_id = s.service_id
GROUP BY s.service_id
ORDER BY total_bookings DESC
LIMIT 5";
$services_result = $conn->query($services_query);

// Add new query for appointment status distribution
$status_query = "SELECT 
    status,
    COUNT(*) as count
FROM appointments 
GROUP BY status";
$status_result = $conn->query($status_query);

// Add after existing queries
$today_appointments_query = "SELECT 
    a.appointment_id,
    u.fullname,
    s.service_name,
    TIME_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time,
    a.status
FROM appointments a
JOIN users u ON a.user_id = u.user_id
JOIN services s ON a.service_id = s.service_id
WHERE DATE(a.appointment_date) = CURDATE()
ORDER BY a.appointment_time";
$today_result = $conn->query($today_appointments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SB Admin 2 Template -->
<link href="css/sb-admin-2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Appointments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_appointments'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['completed'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['upcoming'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['cancelled'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Appointments & Analytics Row -->
<div class="row">
    <!-- Today's Appointments -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Today's Appointments</h6>
                <a href="calendar.php" class="btn btn-sm btn-primary shadow-sm">View Calendar</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $today_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['formatted_time'] ?></td>
                                <td><?= $row['fullname'] ?></td>
                                <td><?= $row['service_name'] ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $row['status'] == 'pending' ? 'warning' : 
                                        ($row['status'] == 'completed' ? 'success' : 'danger') 
                                    ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($today_result->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center">No appointments scheduled for today</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Appointments Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Appointments Overview</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Row -->
<div class="row">
    <!-- Popular Services Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Popular Services</h6>
            </div>
            <div class="card-body">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Appointment Status Distribution -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Appointments Chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: [<?php 
                $labels = [];
                $data = [];
                mysqli_data_seek($monthly_result, 0);
                while($row = $monthly_result->fetch_assoc()) {
                    $labels[] = date('M Y', strtotime($row['month']));
                    $data[] = $row['total'];
                }
                echo '"'.implode('","', $labels).'"';
            ?>],
            datasets: [{
                label: 'Appointments',
                data: [<?php echo implode(',', $data); ?>],
                borderColor: '#4e73df',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Popular Services Chart
    new Chart(document.getElementById('servicesChart'), {
        type: 'bar',
        data: {
            labels: [<?php 
                $labels = [];
                $data = [];
                mysqli_data_seek($services_result, 0);
                while($row = $services_result->fetch_assoc()) {
                    $labels[] = $row['service_name'];
                    $data[] = $row['total_bookings'];
                }
                echo '"'.implode('","', $labels).'"';
            ?>],
            datasets: [{
                label: 'Bookings',
                data: [<?php echo implode(',', $data); ?>],
                backgroundColor: '#36b9cc'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Status Distribution Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php 
                $labels = [];
                $data = [];
                mysqli_data_seek($status_result, 0);
                while($row = $status_result->fetch_assoc()) {
                    $labels[] = ucfirst($row['status']);
                    $data[] = $row['count'];
                }
                echo '"'.implode('","', $labels).'"';
            ?>],
            datasets: [{
                data: [<?php echo implode(',', $data); ?>],
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>

</body>
</html>
<?php include_once('includes/footer.php'); ?>