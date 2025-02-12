<?php 
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

// Date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Appointment Summary Query
$summary_query = "SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
FROM appointments 
WHERE appointment_date BETWEEN '$start_date' AND '$end_date'";
$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Service Statistics Query
$services_query = "SELECT 
    s.service_name,
    COUNT(*) as appointment_count
FROM appointments a
JOIN services s ON a.service_id = s.service_id
WHERE a.appointment_date BETWEEN '$start_date' AND '$end_date'
GROUP BY s.service_id
ORDER BY appointment_count DESC";
$services_result = $conn->query($services_query);

// Detailed Appointments Query
$appointments_query = "SELECT 
    a.appointment_id,
    u.fullname,
    s.service_name,
    a.appointment_date,
    TIME_FORMAT(a.appointment_time, '%h:%i %p') as appointment_time,
    a.status
FROM appointments a
JOIN users u ON a.user_id = u.user_id
JOIN services s ON a.service_id = s.service_id
WHERE a.appointment_date BETWEEN '$start_date' AND '$end_date'
ORDER BY a.appointment_date DESC, a.appointment_time ASC";
$appointments_result = $conn->query($appointments_query);
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
<!-- SB Admin 2 Template -->
<link href="css/sb-admin-2.min.css" rel="stylesheet">
<style>
    .content-wrapper {
        height: calc(100vh - 4.375rem);
        overflow-y: auto;
        position: relative;
    }
    
    .container-fluid {
        padding-bottom: 2rem;
    }

    #wrapper #content-wrapper {
        overflow-x: hidden;
        position: relative;
    }
    body {
        overflow: hidden;
    }
</style>
</head>


<body>
    <!-- Wrap the container-fluid in a content-wrapper div -->
    <div class="content-wrapper">
        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Reports</h1>
            </div>

            <!-- Date Filter Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Date Range Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline mb-3">
                        <div class="form-group mx-2">
                            <label class="mr-2">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="form-group mx-2">
                            <label class="mr-2">End Date:</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards Row -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Appointments</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['total_appointments'] ?></div>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['completed'] ?></div>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['pending'] ?></div>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['cancelled'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Statistics Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Service Statistics</h6>
                    <button onclick="printReport('services-table')" class="btn btn-sm btn-primary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="services-table">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Number of Appointments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $services_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['service_name'] ?></td>
                                    <td><?= $row['appointment_count'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detailed Appointments Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Detailed Appointment Report</h6>
                    <button onclick="printReport('appointments-table')" class="btn btn-sm btn-primary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="appointments-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $appointments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['fullname'] ?></td>
                                    <td><?= $row['service_name'] ?></td>
                                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><?= $row['appointment_time'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $row['status'] == 'completed' ? 'success' : 
                                            ($row['status'] == 'pending' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function printReport(tableId) {
    const printContent = document.getElementById(tableId).outerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div style="padding: 20px;">
            <h1 style="text-align: center; margin-bottom: 20px;">ToothRepair Dental Clinic</h1>
            <h2 style="text-align: center; margin-bottom: 20px;">
                Report for ${document.querySelector('input[name="start_date"]').value} 
                to ${document.querySelector('input[name="end_date"]').value}
            </h2>
            ${printContent}
        </div>`;
    
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Initialize DataTables
$(document).ready(function() {
    $('#services-table').DataTable();
    $('#appointments-table').DataTable();
});
</script>

<?php include_once('includes/footer.php'); ?>
</body>
</html>