<?php
date_default_timezone_set('Asia/Manila');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

// Update the query to include all necessary fields
$query = "SELECT 
    ar.*,
    u.fullname,
    u.email,
    u.contact_number,
    u.address,
    s.service_name,
    DATE_FORMAT(ar.appointment_date, '%M %d, %Y') as formatted_date,
    DATE_FORMAT(ar.appointment_time, '%h:%i %p') as formatted_time,
    DATE_FORMAT(ar.completion_date, '%M %d, %Y %h:%i %p') as completed_at
FROM appointment_records ar
JOIN users u ON ar.user_id = u.user_id
JOIN services s ON ar.service_id = s.service_id
ORDER BY ar.completion_date DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables Bundle -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.13.7/r-2.5.0/datatables.min.css"/>
    <!-- SB Admin 2 Template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/record_list.css">
    <title>Appointments Records</title>
</head>
<body>
    
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Appointments Records</h6>
            <div>
                <button class="btn btn-success" data-toggle="modal" data-target="#addAppointmentModal">
                    <i class="fas fa-plus"></i> Add Previous Records
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Add Search and Alphabetical Filter -->
            <div class="filter-controls mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Search input on the left -->
                    <div class="search-wrapper" style="width: 250px;">
                        <div class="input-group input-group-sm">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search records...">
                            <div class="input-group-append">
                                <button class="btn btn-primary btn-sm" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Alpha filter and sort controls on the right -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm letter-navigation">
                            <button class="btn btn-sm btn-outline-primary nav-prev" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="alpha-pages">
                                <?php
                                $letters = range('A', 'Z');
                                $groups = array_chunk($letters, 9);
                                foreach ($groups as $pageIndex => $group) {
                                    $isActive = $pageIndex === 0 ? 'active' : '';
                                    echo '<div class="alpha-page ' . $isActive . '" data-page="' . ($pageIndex + 1) . '" ' . 
                                         ($pageIndex === 0 ? '' : 'style="display: none;"') . '>';
                                    foreach ($group as $letter) {
                                        echo '<button type="button" class="btn btn-outline-primary btn-sm" data-letter="' . $letter . '">' . $letter . '</button>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <button class="btn btn-sm btn-outline-primary nav-next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary sort-btn" data-letter="all">All</button>
                            <button class="btn btn-outline-primary sort-btn sort-asc active" data-sort="asc">
                                <i class="fas fa-sort-alpha-down"></i>
                            </button>
                            <button class="btn btn-outline-primary sort-btn sort-desc" data-sort="desc">
                                <i class="fas fa-sort-alpha-up"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Existing Table Code -->
            <div class="table-responsive">
                <table class="table table-bordered" id="recordTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Appointment Date</th>
                            <th>Appointment Time</th>
                            <th>Service</th>
                            <th>Status</th>
                            <!-- Removed Actions column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                                    <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                                    <td><?= htmlspecialchars($row['formatted_time']) ?></td>
                                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> 
                                            Completed on <?= htmlspecialchars($row['completed_at']) ?>
                                        </span>
                                    </td>
                                    <!-- Removed Actions column -->
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <!-- Updated colspan from 6 to 5 -->
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle"></i> No completed appointments found
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery Easing (use CDN instead of local file) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables Bundle -->
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.13.7/r-2.5.0/datatables.min.js"></script>
<!-- Custom JS -->
<script src="js/record_list.js"></script>
<script>
// Replace the existing DataTables initialization with this:
$(document).ready(function() {
    var table = $('#recordTable').DataTable({
        "responsive": true,
        "processing": true,
        "pageLength": 10,
        "order": [[1, "desc"]], // Sort by appointment date
        "columnDefs": [
            {
                "targets": [4], // Status column
                "orderable": false
            }
            // Removed Actions column configuration
        ],
        "language": {
            "emptyTable": "No records available",
            "zeroRecords": "No matching records found"
        },
        "dom": '<"top"lf>rt<"bottom"ip><"clear">'
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Alphabetical filter
    $('.btn[data-letter]').on('click', function() {
        var letter = $(this).data('letter');
        if (letter === 'all') {
            table.search('').draw();
        } else {
            table.search('^' + letter, true, false).draw();
        }
    });
});
</script>
</body>
</html>