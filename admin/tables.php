<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

// Check for success messages
if (isset($_GET['success'])) {
    $message = match($_GET['success']) {
        'add' => 'Appointment added successfully!',
        'edit' => 'Appointment updated successfully!',
        'delete' => 'Appointment deleted successfully!',
        default => 'Operation completed successfully!'
    };
    echo '<div class="alert alert-success" id="successAlert">'.$message.'</div>';
}

// Fetch appointments with service information
$sql = "SELECT 
            appointments.appointment_id,
            users.fullname,
            users.created_at AS register_date,
            appointments.appointment_date,
            appointments.appointment_time,
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
?>
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
                                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                                    <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                    <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= getStatusBadge($row['status']) ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm view-btn" 
                                                data-id="<?= $row['appointment_id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-primary btn-sm edit-btn" 
                                                data-id="<?= $row['appointment_id'] ?>"
                                                data-status="<?= $row['status'] ?>"
                                                data-service="<?= $row['service_id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn" 
                                                data-id="<?= $row['appointment_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Appointment Time</label>
                                <input type="time" name="appointment_time" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
                        <div class="col-md-6">
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
<div class="modal fade" id="editAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="tableconfig/edit_appointment.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="editAppointmentId">
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
                                <label>Appointment Time</label>
                                <input type="time" name="appointment_time" id="editTime" class="form-control" required>
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

<?php include_once('includes/footer.php'); ?>

<script>
$(document).ready(function() {
    // Handle edit button click
    $('.edit-btn').click(function() {
        const appointmentId = $(this).data('id');
        
        $.ajax({
            url: 'tableconfig/get_appointment.php',
            type: 'GET',
            data: { id: appointmentId },
            success: function(response) {
                const data = JSON.parse(response);
                $('#editAppointmentId').val(data.appointment_id);
                $('#editFullname').val(data.fullname);
                $('#editEmail').val(data.email);
                $('#editContact').val(data.contact_number);
                $('#editAddress').val(data.address);
                $('#editDate').val(data.appointment_date);
                $('#editTime').val(data.appointment_time);
                $('#editService').val(data.service_id);
                $('#editStatus').val(data.status);
                $('#editAppointmentModal').modal('show');
            }
        });
    });

    // Handle delete button click
    $('.delete-btn').click(function() {
        const appointmentId = $(this).data('id');
        if (confirm('Are you sure you want to delete this appointment?')) {
            window.location = `tableconfig/delete_appointment.php?id=${appointmentId}`;
        }
    });

    // Auto-hide success alerts
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
});

<?php
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'primary';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>
</script>

<style>
.badge {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}
.badge-warning { background-color: #ffc107; }
.badge-primary { background-color: #007bff; }
.badge-success { background-color: #28a745; }
.badge-danger { background-color: #dc3545; }
.btn-group .btn { margin-right: 5px; }
</style>