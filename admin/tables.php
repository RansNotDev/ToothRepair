<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('db_connection.php');

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
                                    echo "<tr>";
                                    echo "<td><input type='checkbox' name='appointments[]' value='" . $row['appointment_id'] . "' class='checkboxItem'></td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['register_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars( $row['appointment_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['services']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>
                                    <div class='btn-group'>
                                        <!-- View Button -->
                                        <button class='btn btn-info btn-sm' style='margin-right: 5px;' data-toggle='modal' data-target='#viewModal" . $row['appointment_id'] . "'>View</button>
                                        <!-- Edit Button -->
                                        <button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#editModal" . $row['appointment_id'] . "'>Edit</button>
                                    </div>
                                </td>";
                                    
                                    // View Modal
                                    echo "<div class='modal fade' id='viewModal" . $row['appointment_id'] . "' tabindex='-1' role='dialog'>
                                        <div class='modal-dialog' role='document'>
                                            <div class='modal-content'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title'>View Appointment</h5>
                                                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                </div>
                                                <div class='modal-body'>
                                                    <p><strong>Full Name:</strong> " . htmlspecialchars($row['fullname']) . "</p>
                                                    <p><strong>Register Date:</strong> " . htmlspecialchars($row['register_date']) . "</p>
                                                    <p><strong>Appointment Date:</strong> " . htmlspecialchars($row['appointment_date']) . "</p>
                                                    <p><strong>Contact Number:</strong> " . htmlspecialchars($row['contact_number']) . "</p>
                                                    <p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>
                                                    <p><strong>Service:</strong> " . htmlspecialchars($row['services']) . "</p>
                                                    <p><strong>Address:</strong> " . htmlspecialchars($row['address']) . "</p>
                                                    <p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>";

                                    // Edit Modal
                                    echo "<div class='modal fade' id='editModal" . $row['appointment_id'] . "' tabindex='-1' role='dialog'>
                                        <div class='modal-dialog' role='document'>
                                            <div class='modal-content'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title'>Edit Appointment</h5>
                                                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                </div>
                                                <form method='POST' action='tableconfig/edit_appointment.php'>
                                                    <div class='modal-body'>
                                                        <input type='hidden' name='appointment_id' value='" . $row['appointment_id'] . "'>
                                                        <div class='form-group'>
                                                            <label>Full Name</label>
                                                            <input type='text' name='fullname' class='form-control' value='" . htmlspecialchars($row['fullname']) . "'>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Appointment Date</label>
                                                            <input type='date' name='appointment_date' class='form-control' value='" . htmlspecialchars($row['appointment_date']) . "'>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Services</label>
                                                            <input type='text' name='services' class='form-control' value='" . htmlspecialchars($row['services']) . "'>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Status</label>
                                                            <select name='status' class='form-control'>
                                                                <option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                                                                <option value='Confirmed'" . ($row['status'] == 'Confirmed' ? ' selected' : '') . ">Confirmed</option>
                                                                <option value='Cancelled'" . ($row['status'] == 'Cancelled' ? ' selected' : '') . ">Cancelled</option>
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
                                    </div>";

                                    // Delete Modal
                                    echo "<div class='modal fade' id='deleteModal" . $row['appointment_id'] . "' tabindex='-1' role='dialog'>
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
                                                    <a href='tableconfig/delete_appointment.php?id=" . $row['appointment_id'] . "' class='btn btn-danger'>Remove</a>
                                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>";
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



<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Add New Appointment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="tableconfig/add_appointment.php" method="POST">
                <div class="modal-body">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" class="form-control" name="fullname" id="fullname" required>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>

                    <!-- Contact Number -->
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" id="contact_number" required>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" id="address" required>
                    </div>

                    <!-- Appointment Date -->
                    <div class="form-group">
                        <label for="appointment_date">Appointment Date</label>
                        <input type="date" class="form-control" name="appointment_date" id="appointment_date" required>
                    </div>

                    <!-- Appointment Time -->
                    <div class="form-group">
                        <label for="appointment_time">Appointment Time</label>
                        <input type="time" class="form-control" name="appointment_time" id="appointment_time" required>
                    </div>

                    <!-- Service -->
                    <div class="form-group">
                        <label for="service">Service</label>
                        <input type="text" class="form-control" name="service" id="service" required>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" id="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Appointment</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
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
</div>


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