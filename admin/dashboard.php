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

// Get popular services (modified for current month)
$services_query = "SELECT 
    s.service_name,
    COUNT(*) as total_bookings
FROM appointments a
JOIN services s ON a.service_id = s.service_id
WHERE MONTH(a.appointment_date) = MONTH(CURRENT_DATE())
    AND YEAR(a.appointment_date) = YEAR(CURRENT_DATE())
GROUP BY s.service_id
ORDER BY total_bookings DESC
LIMIT 5";
$services_result = $conn->query($services_query);

// Update the status query to filter for current month
$status_query = "SELECT 
    status,
    COUNT(*) as count
FROM appointments 
WHERE MONTH(appointment_date) = MONTH(CURRENT_DATE())
    AND YEAR(appointment_date) = YEAR(CURRENT_DATE())
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

// At the top with other PHP queries
$selected_period = isset($_GET['period']) ? $_GET['period'] : 'monthly';

$period_condition = "";
switch($selected_period) {
    case 'monthly':
        $period_condition = "AND MONTH(appointment_date) = MONTH(CURRENT_DATE()) 
                           AND YEAR(appointment_date) = YEAR(CURRENT_DATE())";
        break;
    case 'annual':
        $period_condition = "AND YEAR(appointment_date) = YEAR(CURRENT_DATE())";
        break;
    case 'yearly':
        $period_condition = "AND YEAR(appointment_date) = YEAR(CURRENT_DATE())";
        break;
}

// Get appointment stats
$stats_query = "SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN status IN ('pending','booked') THEN 1 ELSE 0 END) as upcoming
FROM appointments
WHERE 1=1 " . $period_condition;

// Get monthly appointments (for chart)
$monthly_query = "SELECT 
    DATE_FORMAT(appointment_date, '%Y-%m') as month,
    COUNT(*) as total
FROM appointments 
WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    " . $period_condition . "
GROUP BY month 
ORDER BY month";

// Get popular services
$services_query = "SELECT 
    s.service_name,
    COUNT(*) as total_bookings
FROM appointments a
JOIN services s ON a.service_id = s.service_id
WHERE 1=1 " . $period_condition . "
GROUP BY s.service_id
ORDER BY total_bookings DESC
LIMIT 5";

// Get status distribution
$status_query = "SELECT 
    status,
    COUNT(*) as count
FROM appointments 
WHERE 1=1 " . $period_condition . "
GROUP BY status";
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
    <style>
        body {
            overflow: hidden;
        }
        .content-wrapper {
        height: calc(100vh - 4.375rem);
        overflow-y: auto;
        padding: 1.2rem;
        position: relative;  /* Change from absolute if it was */
        margin-left: 14rem;
        margin-top: 4.375rem; /* Add this to account for topbar height */
        z-index: 0;  /* Changed from 1 to 0 */
    }
        .content-wrapper::-webkit-scrollbar {
            width: 0.5rem;
        }
        .content-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .content-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 0.25rem;
        }
        .content-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        #wrapper {
            overflow: hidden;
        }
        #accordionSidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1060;
    }

    /* Target the correct navbar class from topbar.php */
    #content-wrapper .navbar {
        position: fixed;
        top: 0;
        right: 0;
        left: 14rem;
        z-index: 1050;
        padding: 0;
    }

    .navbar-nav {
        z-index: 1051;
    }

    .dropdown-menu {
        z-index: 1052;
    }

    /* Remove duplicate scrollbar styles and other redundant code */
    .content-wrapper::-webkit-scrollbar {
        width: 0.5rem;
    }
    .content-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .content-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 0.25rem;
    }
    .content-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    #wrapper {
        overflow: hidden;
    }
    #accordionSidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1060;
    }

    
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include_once('includes/sidebar.php'); ?>
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include_once('includes/topbar.php'); ?>
                
                <!-- Begin Page Content -->
                <div class="content-wrapper">
                    <!-- Page Heading with Statistics Toggle -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <div class="btn-group shadow-sm">
                            <a href="?period=monthly" class="btn <?= $selected_period == 'monthly' ? 'btn-primary' : 'btn-light' ?>">
                                <i class="fas fa-calendar-day fa-sm mr-2"></i>Monthly
                            </a>
                            <a href="?period=annual" class="btn <?= $selected_period == 'annual' ? 'btn-primary' : 'btn-light' ?>">
                                <i class="fas fa-calendar-week fa-sm mr-2"></i>Annual
                            </a>
                            <a href="?period=yearly" class="btn <?= $selected_period == 'yearly' ? 'btn-primary' : 'btn-light' ?>">
                                <i class="fas fa-calendar fa-sm mr-2"></i>Yearly
                            </a>
                        </div>
                    </div>

                    <!-- Period Indicator -->
                    <div class="text-right mb-3">
                        <span class="text-muted">
                            Showing statistics for: 
                            <strong>
                                <?php 
                                switch($selected_period) {
                                    case 'monthly':
                                        echo date('F Y'); // Current month and year
                                        break;
                                    case 'annual':
                                        echo 'Annual ' . date('Y'); // Current year
                                        break;
                                    case 'yearly':
                                        echo 'Year ' . date('Y'); // Current year
                                        break;
                                }
                                ?>
                            </strong>
                        </span>
                    </div>

                    <!-- Stats Cards Row -->
                    <div class="row mb-4">
                        <!-- Total Appointments Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Appointments</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_appointments'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Completed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['completed'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['upcoming'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cancelled Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Cancelled</div>
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

                    <!-- Charts Row -->
                    <div class="row">
                        <!-- Monthly Appointments Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Appointments Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="monthlyChart" style="height: 320px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Distribution Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie">
                                        <canvas id="statusChart" style="height: 320px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Today's Appointments -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Today's Appointments</h6>
                                    <a href="calendar.php" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-calendar fa-sm text-white-50"></i> View Calendar
                                    </a>
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
                                                <?php if($today_result->num_rows == 0): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No appointments scheduled for today</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php while($row = $today_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $row['formatted_time'] ?></td>
                                                        <td><?= $row['fullname'] ?></td>
                                                        <td><?= $row['service_name'] ?></td>
                                                        <td>
                                                            <span class="badge badge-<?= 
                                                                $row['status'] == 'pending' ? 'warning' : 
                                                                ($row['status'] == 'completed' ? 'success' : 
                                                                ($row['status'] == 'booked' ? 'info' : 'danger')) 
                                                            ?>">
                                                                <?= ucfirst($row['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Popular Services Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Popular Services</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="servicesChart" style="height: 320px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: '<?php echo date("F Y"); ?> Popular Services',
                    color: '#5a5c69',
                    font: {
                        size: 14,
                        family: "'Nunito', sans-serif"
                    }
                }
            }
        }
    });

    // Update the Status Distribution Chart configuration
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php 
                $labels = [];
                $data = [];
                $colors = [];
                mysqli_data_seek($status_result, 0);
                while($row = $status_result->fetch_assoc()) {
                    $status = strtolower($row['status']);
                    // Map status to proper label and color
                    switch($status) {
                        case 'completed':
                            $label = 'Completed';
                            $colors[] = '#1cc88a'; // Green
                            break;
                        case 'cancelled':
                            $label = 'Cancelled';
                            $colors[] = '#e74a3b'; // Red
                            break;
                        case 'pending':
                            $label = 'Pending';
                            $colors[] = '#f6c23e'; // Yellow
                            break;
                        case 'booked':
                            $label = 'Confirmed';
                            $colors[] = '#4e73df'; // Blue
                            break;
                        default:
                            $label = ucfirst($status);
                            $colors[] = '#858796'; // Default gray
                    }
                    $labels[] = $label;
                    $data[] = $row['count'];
                }
                echo '"'.implode('","', $labels).'"';
            ?>],
            datasets: [{
                data: [<?php echo implode(',', $data); ?>],
                backgroundColor: <?php echo json_encode($colors); ?>,
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#5a5c69',
                        font: {
                            size: 12,
                            family: "'Nunito', sans-serif"
                        },
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo date("F Y"); ?> Status Distribution',
                    color: '#5a5c69',
                    font: {
                        size: 14,
                        family: "'Nunito', sans-serif"
                    }
                }
            }
        }
    });
});
</script>

</body>
</html>
<?php include_once('includes/footer.php'); ?>