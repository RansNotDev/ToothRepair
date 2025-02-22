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
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/bs4/dt-1.13.7/r-2.5.0/datatables.min.css" />
    <!-- SB Admin 2 Template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/record_list.css">
    <title>Appointments Records</title>
</head>

<body>

    <div class="container-fluid">
        <div class="card shadow mb-4 scrollable-card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Appointments Records</h6>
                <div>
                    <button class="btn btn-success" data-toggle="modal" data-target="#addAppointmentModal">
                        <i class="fas fa-plus"></i> Add Previous Records
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="completed-tab" data-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="true">Completed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cancelled-tab" data-toggle="tab" href="#cancelled" role="tab" aria-controls="cancelled" aria-selected="false">Cancelled</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content" id="myTabContent">
                    <!-- Completed Tab -->
                    <div class="tab-pane fade show active" id="completed" role="tabpanel">
                        <!-- Add Search and Alphabetical Filter for Completed -->
                        <div class="filter-controls mb-3">
                            <!-- All filters in one line -->
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Left side: Date Filter -->
                                <div class="d-flex gap-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">From</span>
                                        </div>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">To</span>
                                        </div>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                    <button class="btn btn-primary btn-sm" id="applyDateFilter">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                    <button class="btn btn-secondary btn-sm" id="clearDateFilter">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>

                                <!-- Center: Alpha filter -->
                                <div class="d-flex align-items-center">
                                    <div class="btn-group btn-group-sm letter-navigation">
                                        <button class="btn btn-sm btn-outline-primary nav-prev" disabled>
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <div class="alpha-pages">
                                            <?php
                                            $letterGroups = array(
                                                array('A', 'B', 'C', 'D'),
                                                array('E', 'F', 'G', 'H'),
                                                array('I', 'J', 'K', 'L'),
                                                array('M', 'N', 'O', 'P'),
                                                array('Q', 'R', 'S', 'T'),
                                                array('U', 'V', 'W', 'X'),
                                                array('Y', 'Z')
                                            );
                                            
                                            foreach ($letterGroups as $pageIndex => $group) {
                                                $isActive = $pageIndex === 0 ? 'active' : '';
                                                echo '<div class="alpha-page ' . $isActive . '" data-page="' . ($pageIndex + 1) . '" ' .
                                                    ($pageIndex === 0 ? '' : 'style="display: none;"') . '>';
                                                foreach ($group as $letter) {
                                                    echo '<button type="button" class="btn btn-outline-primary btn-sm alpha-btn" data-letter="' . $letter . '">' . $letter . '</button>';
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary nav-next">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                        <div class="btn-group btn-group-sm ms-2">
                                            <button type="button" class="btn btn-outline-primary alpha-btn" data-letter="all">All</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right side: DataTable Search -->
                                <div class="dataTables_filter">
                                    <label class="d-flex align-items-center gap-2">
                                        Search:
                                        <input type="search" class="form-control form-control-sm" placeholder="">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered" id="completedDataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Email</th>  <!-- Add this column -->
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                                <td>
                                                    <?php if (!empty($row['email'])): ?>
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">No email</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                                                <td><?= htmlspecialchars($row['formatted_time']) ?></td>
                                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i>
                                                        Completed on <?= htmlspecialchars($row['completed_at']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-record" 
                                                            data-id="<?= $row['user_id'] ?>"
                                                            data-fullname="<?= htmlspecialchars($row['fullname']) ?>"
                                                            data-email="<?= htmlspecialchars($row['email'] ?? '') ?>"
                                                            data-contact="<?= htmlspecialchars($row['contact_number']) ?>"
                                                            data-address="<?= htmlspecialchars($row['address']) ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
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

                    <!-- Cancelled Tab -->
                    <div class="tab-pane fade" id="cancelled" role="tabpanel">
                        <!-- Add Search and Alphabetical Filter for Cancelled -->
                        <div class="filter-controls mb-3">
                            <!-- All filters in one line -->
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Left side: Date Filter -->
                                <div class="d-flex gap-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">From</span>
                                        </div>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">To</span>
                                        </div>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                    <button class="btn btn-primary btn-sm" id="applyDateFilter">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                    <button class="btn btn-secondary btn-sm" id="clearDateFilter">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>

                                <!-- Center: Alpha filter -->
                                <div class="d-flex align-items-center">
                                    <div class="btn-group btn-group-sm letter-navigation">
                                        <button class="btn btn-sm btn-outline-primary nav-prev" disabled>
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <div class="alpha-pages">
                                            <?php
                                            $letterGroups = array(
                                                array('A', 'B', 'C', 'D'),
                                                array('E', 'F', 'G', 'H'),
                                                array('I', 'J', 'K', 'L'),
                                                array('M', 'N', 'O', 'P'),
                                                array('Q', 'R', 'S', 'T'),
                                                array('U', 'V', 'W', 'X'),
                                                array('Y', 'Z')
                                            );
                                            
                                            foreach ($letterGroups as $pageIndex => $group) {
                                                $isActive = $pageIndex === 0 ? 'active' : '';
                                                echo '<div class="alpha-page ' . $isActive . '" data-page="' . ($pageIndex + 1) . '" ' .
                                                    ($pageIndex === 0 ? '' : 'style="display: none;"') . '>';
                                                foreach ($group as $letter) {
                                                    echo '<button type="button" class="btn btn-outline-primary btn-sm alpha-btn" data-letter="' . $letter . '">' . $letter . '</button>';
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary nav-next">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                        <div class="btn-group btn-group-sm ms-2">
                                            <button type="button" class="btn btn-outline-primary alpha-btn" data-letter="all">All</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right side: DataTable Search -->
                                <div class="dataTables_filter">
                                    <label class="d-flex align-items-center gap-2">
                                        Search:
                                        <input type="search" class="form-control form-control-sm" placeholder="">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Cancelled Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered" id="cancelledDataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Address</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT 
                                        ar.*,
                                        u.fullname,
                                        u.email,
                                        u.contact_number,
                                        u.address,
                                        s.service_name,
                                        DATE_FORMAT(ar.appointment_date, '%M %d, %Y') as formatted_date,
                                        DATE_FORMAT(ar.appointment_time, '%h:%i %p') as formatted_time
                                    FROM appointment_records ar
                                    JOIN users u ON ar.user_id = u.user_id
                                    JOIN services s ON ar.service_id = s.service_id
                                    WHERE ar.status = 'cancelled'
                                    ORDER BY ar.appointment_date DESC";
                                    
                                    $result = mysqli_query($conn, $query);
                                    $i = 1;
                                    while ($row = mysqli_fetch_array($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo $row['fullname']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['contact_number']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['service_name']; ?></td>
                                        <td><?php echo $row['formatted_date']; ?></td>
                                        <td><?php echo $row['formatted_time']; ?></td>
                                        <td>
                                            <span class="badge badge-danger">Cancelled</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm viewbtn" data-toggle="modal" data-target="#viewModal<?php echo $row['record_id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm editbtn" data-toggle="modal" data-target="#editModal<?php echo $row['record_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm deletebtn" data-toggle="modal" data-target="#deleteModal<?php echo $row['record_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php include('view_modal.php'); ?>
                                    <?php include('edit_modal.php'); ?>
                                    <?php include('delete_modal.php'); ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Previous Record Modal -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="appointments/add_previous_record.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Previous Record</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="fullname" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Optional">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="tel" name="contact_number" class="form-control" maxlength="12"
                                        minlength="11" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Service</label>
                                    <select name="service_id" class="form-control" required>
                                        <option value="">Select Service</option>
                                        <?php
                                        $services_query = "SELECT * FROM services";
                                        $services_result = $conn->query($services_query);
                                        while ($service = $services_result->fetch_assoc()) {
                                            echo "<option value='" . $service['service_id'] . "'>" .
                                                htmlspecialchars($service['service_name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Appointment Date</label>
                                    <input type="date" name="appointment_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Appointment Time</label>
                                    <input type="time" name="appointment_time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Completion Date & Time</label>
                                    <input type="datetime-local" name="completion_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update the Edit Record Modal -->
    <div class="modal fade" id="editRecordModal" tabindex="-1" role="dialog" aria-labelledby="editRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="appointments/update_record.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRecordModalLabel">Edit Record Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" id="editFullname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-group">
                                <input type="email" name="email" id="editEmail" class="form-control" placeholder="Optional">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="clearEmailBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Leave blank to keep existing email (if any)</small>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="tel" name="contact_number" id="editContact" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="editAddress" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
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
    <!-- Update your script includes at the bottom of the file -->
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Then Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#completedDataTable').DataTable({
        "dom": "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "ordering": true,
        "searching": true
    });

    // Date filter functionality
    $('#applyDateFilter').click(function() {
        table.draw();
    });

    // Add custom date range filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();

        if (!dateFrom && !dateTo) {
            return true;
        }

        var dateField = data[5]; // Adjust this index to match your date column
        if (!dateField) {
            return false;
        }

        // Convert date strings to Date objects for comparison
        var rowDate = new Date(dateField);
        var fromDate = dateFrom ? new Date(dateFrom) : null;
        var toDate = dateTo ? new Date(dateTo) : null;

        // Reset time components for accurate date comparison
        if (rowDate) rowDate.setHours(0,0,0,0);
        if (fromDate) fromDate.setHours(0,0,0,0);
        if (toDate) toDate.setHours(0,0,0,0);

        if (fromDate && toDate) {
            return rowDate >= fromDate && rowDate <= toDate;
        } else if (fromDate) {
            return rowDate >= fromDate;
        } else if (toDate) {
            return rowDate <= toDate;
        }
        return true;
    });

    // Clear date filter
    $('#clearDateFilter').click(function() {
        $('#dateFrom, #dateTo').val('');
        table.draw();
    });

    // Alphabetical filter
    $('.alpha-btn').click(function() {
        var letter = $(this).data('letter');
        
        // Remove any existing search term
        table.search('').draw();
        
        if (letter === 'all') {
            table.column(1).search('').draw(); // Adjust column index for name column
        } else {
            table.column(1).search('^' + letter, true, false).draw();
        }
        
        // Update active state
        $('.alpha-btn').removeClass('active');
        $(this).addClass('active');
    });

    // Letter group navigation
    $('.nav-prev, .nav-next').click(function() {
        var container = $(this).closest('.letter-navigation');
        var pages = container.find('.alpha-page');
        var currentPage = container.find('.alpha-page.active');
        var currentIndex = parseInt(currentPage.data('page'));
        var totalPages = pages.length;
        
        var newIndex;
        if ($(this).hasClass('nav-prev')) {
            newIndex = currentIndex - 1;
        } else {
            newIndex = currentIndex + 1;
        }

        if (newIndex > 0 && newIndex <= totalPages) {
            pages.removeClass('active').hide();
            pages.filter(`[data-page="${newIndex}"]`).addClass('active').show();
            
            // Update navigation buttons
            container.find('.nav-prev').prop('disabled', newIndex === 1);
            container.find('.nav-next').prop('disabled', newIndex === totalPages);
        }
    });

    // Date input validation
    $('#dateFrom').change(function() {
        var fromDate = $(this).val();
        $('#dateTo').attr('min', fromDate);
        if ($('#dateTo').val() && $('#dateTo').val() < fromDate) {
            $('#dateTo').val(fromDate);
        }
        table.draw();
    });

    $('#dateTo').change(function() {
        var toDate = $(this).val();
        $('#dateFrom').attr('max', toDate);
        if ($('#dateFrom').val() && $('#dateFrom').val() > toDate) {
            $('#dateFrom').val(toDate);
        }
        table.draw();
    });

    // Set max date to today
    var today = new Date().toISOString().split('T')[0];
    $('#dateFrom, #dateTo').attr('max', today);

    // Handle DataTable search
    $('.dataTables_filter input').unbind().bind('keyup', function() {
        table.search(this.value).draw();
    });

    // Handle form submission
    $('#addRecordForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        // Redirect to record_list.php
                        window.location.href = 'record_list.php';
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request.'
                });
            }
        });
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/bs4/dt-1.13.7/r-2.5.0/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>