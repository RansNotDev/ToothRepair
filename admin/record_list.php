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
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Search input on the left -->
                                <div class="search-wrapper" style="width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="completedSearchInput" class="form-control" placeholder="Search records...">
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
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Search input on the left -->
                                <div class="search-wrapper" style="width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="cancelledSearchInput" class="form-control" placeholder="Search records...">
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
    // Initialize DataTables for both tables
    $('#completedDataTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    });

    $('#cancelledDataTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    });

    // Preserve active tab after page reload
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });

    // Check if there's a stored active tab
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $('#myTab a[href="' + activeTab + '"]').tab('show');
    }

    // Keep your existing modal and form handling code here
    // Edit record button click
    $('.edit-record').on('click', function() {
        const btn = $(this);
        $('#editUserId').val(btn.data('id'));
        $('#editFullname').val(btn.data('fullname'));
        $('#editEmail').val(btn.data('email'));
        $('#editContact').val(btn.data('contact'));
        $('#editAddress').val(btn.data('address'));
        $('#editRecordModal').modal('show');
    });

    // Clear email button
    $('#clearEmailBtn').on('click', function() {
        $('#editEmail').val('');
    });

    // Reset form when modal is hidden
    $('#editRecordModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

    // Edit form submission
    $('#editRecordModal form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const emailInput = form.find('input[name="email"]');

        // Validate email if provided
        if (emailInput.val().trim() !== '' && !emailInput[0].checkValidity()) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address or leave it blank'
            });
            return;
        }

        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editRecordModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Record updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update record'
                    });
                }
            },
            error: function(xhr) {
                console.error('Server error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update record. Please try again.'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Set max date for appointment dates
    const today = new Date().toISOString().split('T')[0];
    $('input[name="appointment_date"]').attr('max', today);
    $('input[name="completion_date"]').attr('max', new Date().toISOString().slice(0, 16));

    // Function to handle letter navigation
    function setupLetterNavigation(tabId) {
        const container = $(`#${tabId} .letter-navigation`);
        const prevBtn = container.find('.nav-prev');
        const nextBtn = container.find('.nav-next');
        const pages = container.find('.alpha-page');
        
        let currentPage = 1;
        const totalPages = pages.length;

        function updateNavigation() {
            prevBtn.prop('disabled', currentPage === 1);
            nextBtn.prop('disabled', currentPage === totalPages);
            pages.hide().filter(`[data-page="${currentPage}"]`).show();
        }

        prevBtn.click(() => {
            if (currentPage > 1) {
                currentPage--;
                updateNavigation();
            }
        });

        nextBtn.click(() => {
            if (currentPage < totalPages) {
                currentPage++;
                updateNavigation();
            }
        });
    }

    // Initialize letter navigation for both tabs
    setupLetterNavigation('completed');
    setupLetterNavigation('cancelled');

    // Handle search functionality for both tabs
    $('#completedSearchInput, #cancelledSearchInput').on('keyup', function() {
        const tabId = $(this).closest('.tab-pane').attr('id');
        const table = $(`#${tabId}DataTable`).DataTable();
        table.search(this.value).draw();
    });

    // Handle letter filtering for both tabs
    $('.letter-navigation button[data-letter]').click(function() {
        const letter = $(this).data('letter');
        const tabId = $(this).closest('.tab-pane').attr('id');
        const table = $(`#${tabId}DataTable`).DataTable();
        
        if (letter === 'all') {
            table.search('').draw();
        } else {
            table.search('^' + letter, true, false).draw();
        }
    });

    // Handle sorting for both tabs
    $('.sort-btn').click(function() {
        const tabId = $(this).closest('.tab-pane').attr('id');
        const table = $(`#${tabId}DataTable`).DataTable();
        const sortDirection = $(this).data('sort');
        
        if (sortDirection) {
            table.order([1, sortDirection]).draw();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        }
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