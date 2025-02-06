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
?>

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

<!-- Add Status Distribution Chart -->
<div class="row">
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Appointment Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Trends Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Appointment Trends</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="appointmentTrends"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Distribution -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Popular Services</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="servicesPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Monthly Trends Chart
const monthlyData = <?= json_encode(array_column($monthly_result->fetch_all(MYSQLI_ASSOC), 'total', 'month')) ?>;

new Chart(document.getElementById("appointmentTrends"), {
    type: 'line',
    data: {
        labels: Object.keys(monthlyData),
        datasets: [{
            label: 'Appointments',
            data: Object.values(monthlyData),
            lineTension: 0.3,
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            borderColor: "rgba(78, 115, 223, 1)"
        }]
    }
});

// Services Pie Chart
const servicesData = <?= json_encode(array_column($services_result->fetch_all(MYSQLI_ASSOC), 'total_bookings', 'service_name')) ?>;

new Chart(document.getElementById("servicesPieChart"), {
    type: 'doughnut',
    data: {
        labels: Object.keys(servicesData),
        datasets: [{
            data: Object.values(servicesData),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    }
});

// Add status distribution chart
const statusData = <?= json_encode($status_result->fetch_all(MYSQLI_ASSOC)) ?>;

new Chart(document.getElementById("appointmentStatusChart"), {
    type: 'bar',
    data: {
        labels: statusData.map(item => item.status.toUpperCase()),
        datasets: [{
            label: 'Number of Appointments',
            data: statusData.map(item => item.count),
            backgroundColor: [
                '#4e73df', // Booked - Blue
                '#f6c23e', // Pending - Yellow
                '#36b9cc', // Confirmed - Cyan
                '#1cc88a', // Completed - Green
                '#e74a3b'  // Cancelled - Red
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include_once('includes/footer.php'); ?>