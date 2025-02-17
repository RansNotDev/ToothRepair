<?php
date_default_timezone_set('Asia/Manila');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

$availability = [];
$result = mysqli_query(
    $conn,
    "SELECT available_date, 
            TIME_FORMAT(time_start, '%H:%i') as time_start, 
            TIME_FORMAT(time_end, '%H:%i') as time_end,
            max_daily_appointments 
     FROM availability_tb"
);
while ($row = mysqli_fetch_assoc($result)) {
    $availability[$row['available_date']] = $row;
}

function getStatusBadge($status)
{
    return match (strtolower($status)) {
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
        INNER JOIN services ON appointments.service_id = services.service_id 
        WHERE appointments.status != 'completed'  /* Add this line */
        ORDER BY appointments.appointment_date DESC";
$result = $conn->query($sql);

// Fetch all services for dropdowns
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);
$services = $services_result->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    if (isset($availability[$date])) {
        $start_time = strtotime($availability[$date]['time_start']);
        $end_time = strtotime($availability[$date]['time_end']);
        $max_daily_appointments = $availability[$date]['max_daily_appointments'];

        $time_slots = array();

        // Generate 30-minute slots
        for ($time = $start_time; $time <= $end_time; $time += (30 * 60)) {
            $time_slots[] = date('h:i A', $time);
        }

        // Get booked slots
        $stmt = $conn->prepare("SELECT TIME_FORMAT(appointment_time, '%h:%i %p') as booked_time 
                               FROM appointments 
                               WHERE appointment_date = ? 
                               AND status IN ('confirmed', 'pending')");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $booked_result = $stmt->get_result();
        $booked_slots = array();

        while ($row = $booked_result->fetch_assoc()) {
            $booked_slots[] = $row['booked_time'];
        }

        // Get count of existing appointments for this date
        $stmt = $conn->prepare("SELECT COUNT(*) as count 
                               FROM appointments 
                               WHERE appointment_date = ? 
                               AND status IN ('confirmed', 'pending')");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $current_appointments = $count_row['count'];

        // Check if we've reached the maximum appointments for this date
        if ($current_appointments >= $max_daily_appointments) {
            echo json_encode(['error' => 'Maximum appointments reached for this date']);
            exit;
        }

        echo json_encode([
            'available' => $time_slots,
            'booked' => $booked_slots,
            'remaining_slots' => $max_daily_appointments - $current_appointments
        ]);
    } else {
        echo json_encode(['error' => 'No availability found for selected date']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        .notify-btn {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            transition: all 0.3s ease;
        }
        .notify-btn:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #000;
        }
        .notify-btn:disabled {
            background-color: #ffd754;
            border-color: #ffd754;
            cursor: not-allowed;
            opacity: 0.65;
        }
        .swal2-popup {
            font-size: 0.9rem !important;
        }
        .swal2-html-container {
            text-align: left !important;
        }
    </style>
</head>
<body>
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
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-primary btn-sm edit-btn"
                                                data-id="<?= $row['appointment_id'] ?>" 
                                                data-status="<?= $row['status'] ?>"
                                                data-service="<?= $row['service_id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-warning btn-sm notify-btn" 
                                                data-id="<?= $row['appointment_id'] ?>"
                                                data-email="<?= $row['email'] ?>"
                                                data-name="<?= $row['fullname'] ?>"
                                                data-date="<?= $row['appointment_date'] ?>"
                                                data-time="<?= $row['appointment_time'] ?>"
                                                data-service="<?= $row['service_name'] ?>">
                                                <i class="fas fa-bell"></i> Notify
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
            <form action="appointments/add_appointment.php" method="POST">
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
                                    <option value="07:30:00">7:30 AM</option>
                                    <option value="08:00:00">8:00 AM</option>
                                    <option value="08:30:00">8:30 AM</option>
                                    <option value="09:00:00">9:00 AM</option>
                                    <option value="09:30:00">9:30 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="10:30:00">10:30 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="11:30:00">11:30 AM</option>
                                    <option value="12:00:00">12:00 PM</option>
                                    <option value="12:30:00">12:30 PM</option>
                                    <option value="13:00:00">1:00 PM</option>
                                    <option value="13:30:00">1:30 PM</option>
                                    <option value="14:00:00">2:00 PM</option>
                                    <option value="14:30:00">2:30 PM</option>
                                    <option value="15:00:00">3:00 PM</option>
                                    <option value="15:30:00">3:30 PM</option>
                                    <option value="16:00:00">4:00 PM</option>
                                    <option value="16:30:00">4:30 PM</option>
                                    <option value="17:00:00">5:00 PM</option>
                                    <option value="17:30:00">5:30 PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Service</label>
                                <select name="service_id" class="form-control" required>
                                    <option value="">Select Service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['service_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
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
            <form action="appointments/edit_appointment.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" id="editFullname" class="form-control" readonly>
                                <input type="hidden" name="appointment_id" id="editAppointmentId">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="editEmail" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="contact_number" id="editContact" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="editAddress" class="form-control" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Date</label>
                                <input type="date" name="appointment_date" id="editDate" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label>Appointment Time</label>
                                <input type="text" class="form-control" id="editTime" name="appointment_time" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Service</label>
                                <input type="text" id="editService" name="service_name" class="form-control" readonly>
                                <input type="hidden" name="service_id" id="editServiceId">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="">Select Status</option>
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
                            <input type="text" id="viewService" class="form-control, readonly>
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
    $(document).ready(function() {
        // Enhanced notification button handler
        $('.notify-btn').on('click', function() {
            const btn = $(this);
            const data = {
                email: btn.data('email'),
                name: btn.data('name'),
                date: btn.data('date'),
                time: btn.data('time'),
                service: btn.data('service')
            };

            // Disable button while processing
            btn.prop('disabled', true);
            
            // Show loading state
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            // Send notification
            $.ajax({
                url: 'appointments/send_notification.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Notification sent successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error(result.message || 'Failed to send notification');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to send notification'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Server error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to send notification. Please try again.'
                    });
                },
                complete: function() {
                    // Reset button state
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            });
        });

        $('#addAppointmentModal form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#addAppointmentModal').modal('hide');
                        alert('Appointment added successfully!');
                        window.location.href = 'appointmentlist.php?success=add';
                    } else {
                        alert('Error: ' + (response.error || 'Failed to add appointment'));
                    }
                },
                error: function(xhr) {
                    console.error('Server error:', xhr.responseText);
                    alert('Failed to add appointment. Please try again.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // Replace or update existing edit button handler
        $('.edit-btn').on('click', function () {
            const appointmentId = $(this).data('id');
            const button = $(this);

            button.prop('disabled', true);

            $.ajax({
                url: 'appointments/get_appointment.php',
                type: 'GET',
                data: { id: appointmentId },
                dataType: 'json',
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Store original data in data attributes
                    const form = $('#editAppointmentModal form');
                    form.data('original', data);

                    // Populate edit form with existing data
                    $('#editAppointmentId').val(data.appointment_id);
                    $('#editFullname').val(data.fullname || '');
                    $('#editEmail').val(data.email || '');
                    $('#editContact').val(data.contact_number || '');
                    $('#editAddress').val(data.address || '');
                    $('#editDate').val(data.appointment_date || '');
                    $('#editTime').val(data.appointment_time || '');
                    $('#editService').val(data.service_name || ''); // Show service name
                    $('#editServiceId').val(data.service_id || ''); // Store service ID in hidden 
                    $('#editStatus').val(data.status || '');
                    // Format the time to be more readable
                    const formattedTime = data.appointment_time ? 
                        new Date('2000-01-01T' + data.appointment_time)
                            .toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            }) 
                        : '';

                    $('#editTime').val(formattedTime);

                    // Show modal
                    $('#editAppointmentModal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error fetching appointment details. Please try again.');
                },
                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });

        // Add form submission handler for edit form
        $('#editAppointmentModal form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const status = $('#editStatus').val();
            
            if (!status) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a status'
                });
                return;
            }
            
            submitBtn.prop('disabled', true);
            
            // Create the data object
            const formData = {
                appointment_id: $('#editAppointmentId').val(),
                status: status,
                service_id: $('#editServiceId').val()
            };
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // If status is completed, don't send confirmation email
                        if (status !== 'completed' && status !== 'cancelled') {
                            // Send confirmation email for other status changes
                            sendStatusUpdateEmail(formData.appointment_id, status);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Appointment status updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'appointmentlist.php?success=edit';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to update appointment status'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Server error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update appointment status. Please try again.'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // Function to send status update email
        function sendStatusUpdateEmail(appointmentId, status) {
            if (status === 'confirmed') {
                $.ajax({
                    url: 'appointments/send_status_notification.php',
                    type: 'POST',
                    data: {
                        appointment_id: appointmentId,
                        status: status
                    },
                    dataType: 'json'
                });
            }
        }

        // Replace or update existing view button handler  
        $('.view-btn').on('click', function () {
            const appointmentId = $(this).data('id');
            const button = $(this);

            button.prop('disabled', true);

            $.ajax({
                url: 'appointments/get_appointment.php',
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
                    url: 'appointments/get_timeslots.php',
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
                    url: 'appointments/get_timeslots.php',
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
                url: 'appointments/get_appointment.php',
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

        // Add this code inside your existing $(document).ready() function

        // Handle all close buttons in modals
        $('.modal .close, .modal .btn-secondary, .modal .btn-danger[data-dismiss="modal"]').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.modal').modal('hide');
        });

        // Handle modal closing when clicking outside
        $('.modal').on('click', function (e) {
            if ($(e.target).is('.modal')) {
                $(this).modal('hide');
            }
        });

        // Handle ESC key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('.modal:visible').modal('hide');
            }
        });

        // Cleanup form data when modal is hidden
        $('.modal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
            $(this).find('.alert').remove();
        });
    });

    // Replace the existing form submission handler
    $(document).ready(function() {
        $('#addAppointmentModal form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            
            // Disable submit button to prevent double submission
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('#addAppointmentModal').modal('hide');
                        
                        // Refresh the appointments table
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Failed to add appointment'));
                    }
                },
                error: function(xhr) {
                    console.error('Server error:', xhr.responseText);
                    alert('Failed to add appointment. Please try again.');
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false);
                }
            });
        });
    });

    // Add this to your existing script section
    $(document).ready(function () {
        // Handle date selection for both add and edit forms
        $('input[name="appointment_date"], #editDate').on('change', function () {
            const dateSelected = $(this).val();
            const timeSelect = $(this).closest('form').find('select[name="appointment_time"]');

            if (dateSelected) {
                timeSelect.prop('disabled', true); // Disable while loading

                $.ajax({
                    url: 'appointments/get_timeslots.php',
                    type: 'GET',
                    data: { date: dateSelected },
                    dataType: 'json',
                    success: function (response) {
                        timeSelect.empty().append('<option value="">Select Time</option>');

                        if (response.slots && response.slots.length > 0) {
                            response.slots.forEach(time => {
                                const isBooked = response.booked.includes(time);
                                if (!isBooked) {
                                    timeSelect.append(`<option value="${time}">${time}</option>`);
                                }
                            });
                        } else if (response.error) {
                            timeSelect.append(`<option value="" disabled>${response.error}</option>`);
                        }
                    },
                    error: function () {
                        timeSelect.empty().append('<option value="">Error loading times</option>');
                    },
                    complete: function () {
                        timeSelect.prop('disabled', false); // Re-enable after loading
                    }
                });
            } else {
                timeSelect.empty().append('<option value="">Select Time</option>');
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

    // Replace or add this code in your existing JavaScript section
    $(document).ready(function () {
        // Function to generate time slots
        function generateTimeSlots(startTime, endTime) {
            const slots = [];
            const [startHours, startMinutes] = startTime.split(':').map(Number);
            const [endHours, endMinutes] = endTime.split(':').map(Number);

            let currentDate = new Date();
            currentDate.setHours(startHours, startMinutes, 0);

            const endDate = new Date();
            endDate.setHours(endHours, endMinutes, 0);

            while (currentDate <= endDate) {
                slots.push(currentDate.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }));

                // Add 30 minutes
                currentDate = new Date(currentDate.getTime() + 30 * 60000);
            }

            return slots;
        }

        // Handle date selection change
        $('input[name="appointment_date"], #editDate').on('change', function () {
            const dateSelected = $(this).val();
            const timeSelect = $(this).closest('form').find('select[name="appointment_time"]');

            if (dateSelected) {
                $.ajax({
                    url: window.location.pathname,
                    type: 'GET',
                    data: { date: dateSelected },
                    dataType: 'json',
                    success: function (response) {
                        timeSelect.empty().append('<option value="">Select Time</option>');

                        if (response.available && response.booked) {
                            const availableSlots = response.available;
                            const bookedSlots = response.booked;

                            availableSlots.forEach(timeSlot => {
                                if (!bookedSlots.includes(timeSlot)) {
                                    timeSelect.append(`<option value="${timeSlot}">${timeSlot}</option>`);
                                }
                            });
                        } else if (response.error) {
                            timeSelect.append('<option value="" disabled>' + response.error + '</option>');
                        }
                    },
                    error: function () {
                        timeSelect.empty()
                            .append('<option value="">Error loading time slots</option>');
                    }
                });
            } else {
                timeSelect.empty().append('<option value="">Select Time</option>');
            }
        });
    });

    $(document).ready(function() {
    $('#addAppointmentModal form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide modal first
                    $('#addAppointmentModal').modal('hide');
                    
                    // Show success message using SweetAlert or standard alert
                    alert('Appointment added successfully!');
                    
                    // Redirect to appointment list with success parameter
                    window.location.href = 'appointmentlist.php?success=add';
                } 
            },
        });
    });
});

    function updateTimeSlots(dateInput, timeSelect, originalTime = null) {
        const dateSelected = dateInput.val();
        
        if (!dateSelected) {
            timeSelect.empty().append('<option value="">Select Time</option>');
            return;
        }
    
        timeSelect.prop('disabled', true);
        
        $.ajax({
            url: 'appointments/get_timeslots.php',
            type: 'GET',
            data: { 
                date: dateSelected,
                current_time: originalTime 
            },
            dataType: 'json',
            success: function(response) {
                timeSelect.empty().append('<option value="">Select Time</option>');
                
                if (response.available && response.available.length > 0) {
                    response.available.forEach(time => {
                        const isBooked = response.booked && response.booked.includes(time);
                        const isOriginalTime = time === originalTime;
                        
                        if (!isBooked || isOriginalTime) {
                            timeSelect.append(`<option value="${time}" ${isOriginalTime ? 'selected' : ''}>${time}</option>`);
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading timeslots:', xhr.responseText);
                timeSelect.empty().append('<option value="">Error loading times</option>');
            },
            complete: function() {
                timeSelect.prop('disabled', false);
            }
        });
    }
    
    // Update edit form handler
    $('.edit-btn').on('click', function() {
        const appointmentId = $(this).data('id');
        const button = $(this);
        
        button.prop('disabled', true);
        
        $.ajax({
            url: 'appointments/get_appointment.php',
            type: 'GET',
            data: { id: appointmentId },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }
    
                const form = $('#editAppointmentModal form');
                form.data('original', data);
    
                $('#editAppointmentId').val(data.appointment_id);
                $('#editFullname').val(data.fullname || '');
                $('#editEmail').val(data.email || '');
                $('#editContact').val(data.contact_number || '');
                $('#editAddress').val(data.address || '');
                $('#editDate').val(data.appointment_date || '');
                $('#editService').val(data.service_id || '');
                $('#editStatus').val(data.status || '');
    
                const timeSelect = $('#editTime');
                updateTimeSlots($('#editDate'), timeSelect, data.appointment_time);
    
                $('#editAppointmentModal').modal('show');
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                alert('Error fetching appointment details.');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
    
    // Handle date changes
    $('#editDate').on('change', function() {
        const timeSelect = $('#editTime');
        const originalData = $('#editAppointmentModal form').data('original');
        updateTimeSlots($(this), timeSelect, originalData?.appointment_time);
    });

    $(document).ready(function () {
        $('input[name="appointment_date"]').on('change', function () {
            const dateSelected = $(this).val();
            const timeSelect = $(this).closest('form').find('select[name="appointment_time"]');

            if (dateSelected) {
                timeSelect.prop('disabled', true);

                $.ajax({
                    url: 'appointments/get_timeslots.php',
                    type: 'GET',
                    data: { date: dateSelected },
                    dataType: 'json',
                    success: function (response) {
                        timeSelect.empty().append('<option value="">Select Time</option>');

                        if (response.available && response.available.length > 0) {
                            response.available.forEach(time => {
                                const isBooked = response.booked && response.booked.includes(time);
                                if (!isBooked) {
                                
                                    const timeValue = time.replace(' ', ':00 ');
                                    timeSelect.append(`<option value="${timeValue}">${time}</option>`);
                                }
                            });
                        } else if (response.error) {
                            timeSelect.append(`<option value="" disabled>${response.error}</option>`);
                        }
                    },
                    error: function () {
                        timeSelect.empty().append('<option value="">Error loading times</option>');
                    },
                    complete: function () {
                        timeSelect.prop('disabled', false);
                    }
                });
            } else {
                timeSelect.empty().append('<option value="">Select Time</option>');
            }
        });
    });

    // Add this inside your existing $(document).ready(function() {...})
    $('.notify-btn').on('click', function() {
        const btn = $(this);
        const data = {
            email: btn.data('email'),
            name: btn.data('name'),
            date: btn.data('date'),
            time: btn.data('time'),
            service: btn.data('service')
        };

        // First show confirmation dialog
        Swal.fire({
            title: 'Send Reminder?',
            html: `
                <div class="text-left">
                    <p>Send appointment reminder to:</p>
                    <ul class="list-unstyled">
                        <li><strong>Patient:</strong> ${data.name}</li>
                        <li><strong>Email:</strong> ${data.email}</li>
                        <li><strong>Date:</strong> ${data.date}</li>
                        <li><strong>Time:</strong> ${data.time}</li>
                        <li><strong>Service:</strong> ${data.service}</li>
                    </ul>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, send reminder',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: 'appointments/send_notification.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to send notification');
                    }
                    return response;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sent!',
                    text: 'Appointment reminder has been sent successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Redirect back to appointment list after success message
                    window.location.href = 'appointmentlist.php';
                });
            }
        });
    });
</script>
<?php require 'includes/footer.php'; ?>
</body>
</html>