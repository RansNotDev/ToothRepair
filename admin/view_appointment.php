<?php
session_start();
require_once('../includes/auth.php');
checkAdminRole();

if (!isset($_GET['id'])) die("Appointment ID missing");
$appointmentId = (int)$_GET['id'];

include('../database/db_connection.php');
$sql = "SELECT a.*, u.fullname, u.email, u.contact_number, u.address 
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        WHERE a.appointment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

include_once('../includes/header.php');
?>
<div class="container">
    <h2>Appointment Details</h2>
    
    <dl class="row">
        <dt class="col-sm-3">Patient Name</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($appointment['fullname']) ?></dd>

        <dt class="col-sm-3">Date & Time</dt>
        <dd class="col-sm-9"><?= date('M j, Y g:i A', strtotime($appointment['appointment_date'])) ?></dd>

        <dt class="col-sm-3">Service</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($appointment['service']) ?></dd>

        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
            <span class="badge badge-<?= getStatusBadge($appointment['status']) ?>">
                <?= ucfirst($appointment['status']) ?>
            </span>
        </dd>

        <dt class="col-sm-3">Contact Info</dt>
        <dd class="col-sm-9">
            <?= htmlspecialchars($appointment['contact_number']) ?><br>
            <?= htmlspecialchars($appointment['email']) ?>
        </dd>

        <dt class="col-sm-3">Address</dt>
        <dd class="col-sm-9"><?= nl2br(htmlspecialchars($appointment['address'])) ?></dd>

        <dt class="col-sm-3">Message</dt>
        <dd class="col-sm-9"><?= nl2br(htmlspecialchars($appointment['message'])) ?></dd>
    </dl>

    <a href="appointmentlist.php" class="btn btn-secondary">Back to List</a>
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