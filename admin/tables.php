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
<<<<<<< HEAD
                        <?php endif; ?>
                    </tbody>
                </table>
=======
                        </thead>
                        <tbody>
                        <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                        ?>
                            <tr>
                                <td><input type="checkbox" name="appointments[]" value="<?php echo $row['appointment_id']; ?>" class="checkboxItem"></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['register_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['services']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <div class='btn-group'>
                                        <!-- View Button -->
                                        <button class='btn btn-info btn-sm' style='margin-right: 5px;' data-toggle='modal' data-target='#viewModal<?php echo $row['appointment_id']; ?>'>View</button>
                                        <!-- Edit Button -->
                                        <button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#editModal<?php echo $row['appointment_id']; ?>'>Edit</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class='modal fade' id='viewModal<?php echo $row['appointment_id']; ?>' tabindex='-1' role='dialog'>
                                <div class='modal-dialog' role='document'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>View Appointment</h5>
                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        </div>
                                        <div class='modal-body'>
                                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['fullname']); ?></p>
                                            <p><strong>Register Date:</strong> <?php echo htmlspecialchars($row['register_date']); ?></p>
                                            <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars($row['appointment_date']); ?></p>
                                            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($row['contact_number']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                            <p><strong>Service:</strong> <?php echo htmlspecialchars($row['services']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                                            <p><strong>Status:</strong> <?php echo htmlspecialchars($row['status']); ?></p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Modal -->
                            <div class='modal fade' id='editModal<?php echo $row['appointment_id']; ?>' tabindex='-1' role='dialog'>
                                <div class='modal-dialog' role='document'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Edit Appointment</h5>
                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        </div>
                                        <form action='tableconfig/edit_appointment.php' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='appointment_id' value='<?php echo $row['appointment_id']; ?>'>
                                                <div class='form-group'>
                                                    <label>Full Name :</label>
                                                    <input type='text' name='fullname' class='form-control' value='<?php echo htmlspecialchars($row['fullname']); ?>'>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Appointment Date</label>
                                                    <input type='date' name='appointment_date' class='form-control' value='<?php echo htmlspecialchars($row['appointment_date']); ?>'>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Services</label>
                                                    <input type='text' name='services' class='form-control' value='<?php echo htmlspecialchars($row['services']); ?>'>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Status</label>
                                                    <select name='status' id='status' class='form-control' required>
                                                        <option value='pending' <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                        <option value='confirmed' <?php if ($row['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                                                        <option value='completed' <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                                        <option value='cancelled' <?php if ($row['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='submit' class='btn btn-primary'>Update</button>
                                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class='modal fade' id='deleteModal<?php echo $row['appointment_id']; ?>' tabindex='-1' role='dialog'>
                                <div class='modal-dialog' role='document'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Remove Appointment</h5>
                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        </div>
                                        <div class='modal-body'>
                                            Are you sure you want to remove this appointment?
                                        </div>
                                        <div class='modal-footer'>
                                            <a href='tableconfig/delete_appointment.php?id=<?php echo $row['appointment_id']; ?>' class='btn btn-danger'>Remove</a>
                                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                                }
                            } else {
                                echo "<tr><td colspan='10'>No appointments found.</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </form>
>>>>>>> a7b1f08c2342e52e88bef234c038b286dd85eeed
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<<<<<<< HEAD
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
=======
<div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Add New Appointment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <form action="tableconfig/add_appointment.php" method="POST">
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Phone</label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="Enter your phone number" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="appointment_date">Appointment Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="form-group">
                                <label for="appointment_time">Appointment Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                            </div>
                            <div class="form-group">
                                <label for="service">Select Service</label>
                                <select class="form-control" id="service" name="service" required>
                                    <option>Teeth Whitening</option>
                                    <option>Teeth Cleaning</option>
                                    <option>Teeth Filling</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                            <div class="form-group d-flex justify-content-left">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms">
                                    <label class="form-check-label" for="terms">I agree to the <b class="text-primary">terms and conditions</b> of Tooth Repair Dental Clinic</label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
>>>>>>> a7b1f08c2342e52e88bef234c038b286dd85eeed
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
=======
<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
>>>>>>> a7b1f08c2342e52e88bef234c038b286dd85eeed
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