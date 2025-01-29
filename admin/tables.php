<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');

// Check for success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="alert alert-success" id="successAlert">Appointment added successfully!</div>';
}

// Fetch data from the database
$sql = "SELECT 
            appointments.appointment_id,
            users.fullname,
            users.created_at AS register_date,
            appointments.appointment_date,
            appointments.appointment_time,
            users.contact_number,
            users.email,
            appointments.service AS services,
            users.address,
            appointments.status
        FROM appointments
        INNER JOIN users ON appointments.user_id = users.user_id";
$result = $conn->query($sql);
?>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Appointments</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <!-- Add Button -->
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addAppointmentModal">Add Appointment</button>
                <!-- Bulk Delete Button -->
                <button type="button" class="btn btn-danger" id="bulkDeleteButton" disabled data-toggle="modal" data-target="#bulkDeleteModal">
                    Bulk Delete
                </button>
            </div>
            <div class="table-responsive">
                <form method="POST" action="tableconfig/delete_appointment.php" id="bulkDeleteForm">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Full Name</th>
                                <th>Register Date</th>
                                <th>Appointment Date</th>
                                <th>Appointment Time</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Appointment Type</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
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
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
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
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Bulk Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected appointments?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<?php include_once('includes/footer.php'); ?>

<script>
$(document).ready(function () {
    // Automatically hide success alert after 15 seconds
    setTimeout(function() {
        $('#successAlert').fadeOut();
    }, 5000); // 5 seconds

    // Enable/Disable Bulk Delete Button
    $('#selectAll').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.checkboxItem').prop('checked', isChecked);
        toggleBulkDeleteButton();
    });

    $('.checkboxItem').on('change', function () {
        toggleBulkDeleteButton();
    });

    function toggleBulkDeleteButton() {
        const hasChecked = $('.checkboxItem:checked').length > 0;
        $('#bulkDeleteButton').prop('disabled', !hasChecked);
    }

    // Handle Bulk Delete Confirmation
    $('#confirmBulkDelete').on('click', function () {
        const selectedIds = $('.checkboxItem:checked').map(function () {
            return $(this).val();
        }).get();

        // Send AJAX request to delete appointments
        $.ajax({
            url: 'tableconfig/bulk_delete_appointment.php',
            type: 'POST',
            data: { appointment_ids: selectedIds },
            success: function (response) {
                // Reload the page after successful deletion
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('An error occurred while deleting appointments.');
            }
        });
    });
});
</script>