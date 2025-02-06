<?php
date_default_timezone_set('Asia/Manila');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

function getAvailableTimeSlots($date, $conn)
{
    $stmt = $conn->prepare("SELECT time_start, time_end FROM availability_tb WHERE available_date = ? AND is_active = 1");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $slots = [];
    if ($row = $result->fetch_assoc()) {
        $start = strtotime($row['time_start']);
        $end = strtotime($row['time_end']);

        for ($time = $start; $time <= $end; $time += (30 * 60)) {
            $slots[] = date('h:i A', $time); // 12-hour format
        }
    }

    return $slots;
}

// Add status badge function
function getStatusBadge($status) {
    return match(strtolower($status)) {
        'booked' => 'warning',
        'pending' => 'info',
        'confirmed' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}

// Check for success messages
if (isset($_GET['success'])) {
    $message = match ($_GET['success']) {
        'add' => 'Appointment added successfully!',
        'edit' => 'Appointment updated successfully!',
        'delete' => 'Appointment deleted successfully!',
        default => 'Operation completed successfully!'
    };
    echo '<div class="alert alert-success" id="successAlert">' . $message . '</div>';
}

// Fetch appointments with service information
$sql = "SELECT 
            appointments.appointment_id,
            users.fullname,
            users.created_at AS register_date,
            appointments.appointment_date,
            DATE_FORMAT(appointments.appointment_time, '%h:%i %p') as appointment_time,
            users.contact_number,
            users.email,
            services.service_name,
            appointments.service_id,
            users.address,
            appointments.status
        FROM appointments
        INNER JOIN users ON appointments.user_id = users.user_id
        INNER JOIN services ON appointments.service_id = services.service_id";
$result = $conn->query($sql);

// Fetch all services for dropdowns
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);
$services = $services_result->fetch_all(MYSQLI_ASSOC);


if (isset($_GET['date'])) {
    $date = $_GET['date'];

    // Get availability for the selected date
    $stmt = $conn->prepare("SELECT time_start, time_end FROM availability_tb WHERE available_date = ? AND is_active = 1");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $start_time = strtotime($row['time_start']);
        $end_time = strtotime($row['time_end']);

        $time_slots = array();

        // Generate 30-minute slots
        for ($time = $start_time; $time <= $end_time; $time += (30 * 60)) {
            $time_slots[] = date('h:i A', $time); // Changed to 12-hour format
        }

        // Get booked slots
        $stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status IN ('confirmed','pending')");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $booked = $stmt->get_result();
        $booked_slots = array();

        while ($book = $booked->fetch_assoc()) {
            $booked_slots[] = $book['appointment_time'];
        }

        echo json_encode([
            'available' => $time_slots,
            'booked' => $booked_slots
        ]);
    } else {
        echo json_encode(['error' => 'No availability found']);
    }
} 
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>


<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Appointments List</h6>
            <div>
                <button class="btn btn-success" data-toggle="modal" data-target="#addAppointmentModal">
                    <i class="fas fa-plus"></i> Add Appointment
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Appointment Date</th>
                            <th>Appointment Time</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Full Name"><?= htmlspecialchars($row['fullname']) ?></td>
                                    <td data-label="Appointment Date"><?= htmlspecialchars($row['appointment_date']) ?></td>
                                    <td data-label="Appointment Time"><?= htmlspecialchars($row['appointment_time']) ?></td>
                                    <td data-label="Service"><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td data-label="Status">
                                        <span class="badge badge-<?= getStatusBadge($row['status']) ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-sm view-btn"
                                                data-id="<?= $row['appointment_id'] ?>">
                                                View
                                            </button>
                                            <button class="btn btn-primary btn-sm edit-btn"
                                                data-id="<?= $row['appointment_id'] ?>" data-status="<?= $row['status'] ?>"
                                                data-service="<?= $row['service_id'] ?>">
                                                Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn"
                                                data-id="<?= $row['appointment_id'] ?>">
                                                Delete
                                            </button>
                                        </div>

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="tableconfig/add_appointment.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Appointment</h5>
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
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="contact_number" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Date</label>
                                <input type="date" name="appointment_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Appointment Time</label>
                                <select name="appointment_time" class="form-control" required>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Service</label>
                                <select name="service_id" class="form-control" required>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['service_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Appointment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="tableconfig/edit_appointment.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" id="editFullname" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="editEmail" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="contact_number" id="editContact" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="editAddress" class="form-control" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Date</label>
                                <input type="date" name="appointment_date" id="editDate" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Appointment Time</label>
                                <select class="form-control" id="time" name="appointment_time" required>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Service</label>
                                <select name="service_id" id="editService" class="form-control" required>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['service_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control" required>
                                    <option value="booked">Booked</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Appointment Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">View Appointment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" id="viewFullname" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="viewEmail" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="tel" id="viewContact" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="viewAddress" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Appointment Date</label>
                            <input type="date" id="viewDate" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Appointment Time</label>
                            <input type="text" id="viewTime" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Service</label>
                            <input type="text" id="viewService" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" id="viewStatus" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Replace or update existing edit button handler
        $('.edit-btn').on('click', function () {
            const appointmentId = $(this).data('id');

            // Show loading state
            $(this).prop('disabled', true);

            $.ajax({
                url: 'tableconfig/get_appointment.php',
                type: 'GET',
                data: { id: appointmentId },
                dataType: 'json', // Add this line
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    $('#editAppointmentId').val(data.appointment_id);
                    $('#editFullname').val(data.fullname);
                    $('#editEmail').val(data.email);
                    $('#editContact').val(data.contact_number);
                    $('#editAddress').val(data.address);
                    $('#editDate').val(data.appointment_date);
                    $('#time').val(data.appointment_time); // Updated selector
                    $('#editService').val(data.service_id);
                    $('#editStatus').val(data.status);

                    // Show modal
                    $('#editAppointmentModal').modal('show');
                },
                error: function (xhr, status, error) {
                    alert('Error fetching appointment: ' + error);
                },
                complete: function () {
                    // Re-enable button
                    $('.edit-btn').prop('disabled', false);
                }
            });
        });

        // Replace or update existing view button handler  
        $('.view-btn').on('click', function () {
            const appointmentId = $(this).data('id');
            const button = $(this);

            button.prop('disabled', true);

            $.ajax({
                url: 'tableconfig/get_appointment.php',
                type: 'GET',
                data: { id: appointmentId },
                dataType: 'json', // Add this line
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Populate view fields
                    $('#viewFullname').val(data.fullname);
                    $('#viewEmail').val(data.email);
                    $('#viewContact').val(data.contact_number);
                    $('#viewAddress').val(data.address);
                    $('#viewDate').val(data.appointment_date);
                    $('#viewTime').val(data.appointment_time);
                    $('#viewService').val(data.service_name);
                    $('#viewStatus').val(data.status);

                    // Show modal
                    $('#viewAppointmentModal')
                        .modal({
                            backdrop: 'static',
                            keyboard: true,
                            focus: true
                        })
                        .modal('show');
                },
                error: function (xhr, status, error) {
                    alert('Error fetching appointment: ' + error);
                },
                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });

        // Replace existing delete button handler
        $('.delete-btn').on('click', function () {
            const appointmentId = $(this).data('id');
            const button = $(this);

            if (!confirm('Are you sure you want to delete this appointment?')) {
                return;
            }

            button.prop('disabled', true);

            $.ajax({
                url: 'tableconfig/delete_appointment.php',
                type: 'POST',
                data: { id: appointmentId },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Reload page or remove row
                        window.location.href = 'appointmentlist.php?success=delete';
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error occurred'));
                    }
                },
                error: function (xhr) {
                    console.error('Server error:', xhr.responseText);
                    alert('Failed to delete appointment. Please try again.');
                },
                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });

        // Initialize Bootstrap modals explicitly
        $('#editAppointmentModal, #viewAppointmentModal').modal({
            show: false,
            backdrop: 'static',
            keyboard: true
        });

        // Add inside existing $(document).ready() function

        $('input[name="appointment_date"]').on('change', function () {
            const dateSelected = $(this).val();
            const timeSelect = $('#time');

            if (dateSelected) {
                $.ajax({
                    url: 'tableconfig/get_timeslots.php',
                    type: 'GET',
                    data: { date: dateSelected },
                    success: function (response) {
                        timeSelect.empty().append('<option value="">Select Time</option>');
                        if (response.slots && response.slots.length > 0) {
                            response.slots.forEach(time => {
                                if (!response.booked.includes(time)) {
                                    timeSelect.append(`<option value="${time}">${time}</option>`);
                                }
                            });
                        }
                    },
                    error: function () {
                        timeSelect.empty().append('<option value="">Error loading times</option>');
                    }
                });
            } else {
                timeSelect.empty().append('<option value="">Select Time</option>');
            }
        });



        $('#editDate').on('change', function () {
            const dateSelected = $(this).val();
            const timeSelect = $('#time');

            if (dateSelected) {
                $.ajax({
                    url: 'tableconfig/get_timeslots.php',
                    type: 'GET',
                    data: { date: dateSelected },
                    dataType: 'json',
                    success: function (response) {
                        timeSelect.empty().append('<option value="">Select Time</option>');
                        if (response.slots && response.slots.length > 0) {
                            response.slots.forEach(time => {
                                if (!response.booked.includes(time)) {
                                    timeSelect.append(`<option value="${time}">${time}</option>`);
                                }
                            });
                        }
                    },
                    error: function () {
                        timeSelect.empty().append('<option value="">Error loading times</option>');
                    }
                });
            } else {
                timeSelect.empty().append('<option value="">Select Time</option>');
            }
        });

        // Auto-hide success alerts
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);

        // Handle close button click
        $('.close').click(function () {
            $(this).closest('.modal').modal('hide');
        });

        // Handle clicking outside modal
        $('.modal').click(function (e) {
            if ($(e.target).hasClass('modal')) {
                $(this).modal('hide');
            }
        });

        // Handle ESC key press
        $(document).keyup(function (e) {
            if (e.key === "Escape") {
                $('.modal').modal('hide');
            }
        });

        // View button handler
        $('.view-btn').on('click', function () {
            const appointmentId = $(this).data('id');
            const button = $(this);

            button.prop('disabled', true);

            $.ajax({
                url: 'tableconfig/get_appointment.php',
                type: 'GET',
                data: { id: appointmentId },
                dataType: 'json',
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    $('#viewFullname').val(data.fullname);
                    $('#viewEmail').val(data.email);
                    $('#viewContact').val(data.contact_number);
                    $('#viewAddress').val(data.address);
                    $('#viewDate').val(data.appointment_date);
                    $('#viewTime').val(data.appointment_time);
                    $('#viewService').val(data.service_name);
                    $('#viewStatus').val(data.status);

                    $('#viewAppointmentModal')
                        .modal({
                            backdrop: 'static',
                            keyboard: true,
                            focus: true
                        })
                        .modal('show');
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('Error loading appointment details');
                },
                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });

        // Handle modal events for accessibility
        $('.modal').on('show.bs.modal', function () {
            $(this).removeAttr('aria-hidden');
        }).on('hidden.bs.modal', function () {
            $(this).find('input, select, textarea').val('');
            $(this).find('button').prop('disabled', false);
        });

        // Handle ESC key properly
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }
        });
    });

    // Update the time slot display in JavaScript
    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes);
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit', 
            hour12: true 
        });
    }
</script>

<style>
    /* General Responsive Styles */
    .container-fluid {
        padding: 15px;
    }

    /* Table Responsiveness */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        .table thead {
            display: none;
        }

        .table tr {
            display: block;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .table td {
            display: block;
            text-align: left;
            position: relative;
            padding-left: 50%;
        }

        .table td:before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            width: 45%;
            font-weight: bold;
        }
    }

    /* Modal Responsiveness */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-content {
            padding: 10px;
        }

        .form-group {
            margin-bottom: 0.5rem;
        }
    }

    /* Button Responsiveness */
    @media (max-width: 576px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-sm {
            width: 100%;
            margin-bottom: 10px;
            margin-left: 10px;
        }
    }

    /* Badge Styles */
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
        white-space: normal;
        text-align: center;
        display: inline-block;
    }

    .badge-warning {
        background-color: #ffc107;
    }

    .badge-primary {
        background-color: #007bff;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    /* Card Header Responsiveness */
    .card-header {
        flex-wrap: wrap;
        gap: 10px;
    }

    @media (max-width: 576px) {
        .card-header {
            text-align: center;
        }

        .card-header button {
            width: 100%;
            margin-top: 10px;
        }
    }

    /* Form Responsiveness */
    @media (max-width: 768px) {
        .row {
            margin: 0;
        }

        .col-md-6 {
            padding: 0;
        }

        .form-group {
            margin-bottom: 1rem;
        }
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-button {
        min-width: 100px;
        padding: 6px 12px;
        margin: 2px;
    }

    @media (max-width: 576px) {
        .action-button {
            min-width: 100%;
            margin-bottom: 5px;
        }
    }

    .badge {
        padding: 8px 12px;
        font-size: 0.9em;
    }

    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-info { background-color:rgb(190, 148, 9); color: #fff; }
    .badge-primary { background-color: #007bff; color: #fff; }
    .badge-success { background-color: #28a745; color: #fff; }
    .badge-danger { background-color: #dc3545; color: #fff; }
    .badge-secondary { background-color: #6c757d; color: #fff; }
</style>