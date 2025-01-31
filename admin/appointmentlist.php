<?php
session_start();
require_once('../includes/auth.php');
checkAdminRole();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once('../includes/header.php');
include_once('../includes/sidebar.php');
include_once('../includes/topbar.php');
include_once('../database/db_connection.php');

// Database connection check
if (!isset($conn)) die("Database connection error");

// Success messages
if (isset($_GET['success'])) {
    $messages = [
        1 => 'Appointment added successfully!',
        2 => 'Appointment updated successfully!',
        3 => 'Appointment deleted successfully!'
    ];
    echo '<div class="alert alert-success">'.$messages[$_GET['success']].'</div>';
}

// Fetch appointments
$sql = "SELECT a.*, u.fullname, u.contact_number, u.email, u.address 
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id";
$result = $conn->query($sql);
?>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Appointments</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="add_appointment.php" class="btn btn-success">Add Appointment</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Appointment Date</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($row['appointment_date'])) ?></td>
                            <td><?= htmlspecialchars($row['service']) ?></td>
                            <td>
                                <span class="badge badge-<?= getStatusBadge($row['status']) ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_appointment.php?id=<?= $row['appointment_id'] ?>" class="btn btn-info btn-sm">View</a>
                                <a href="edit_appointment.php?id=<?= $row['appointment_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <form method="POST" action="delete_appointment.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['appointment_id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php 
function getStatusBadge($status) {
    switch($status) {
        case 'confirmed': return 'success';
        case 'completed': return 'info';
        case 'cancelled': return 'danger';
        default: return 'warning';
    }
}
include_once('../includes/footer.php');
?>