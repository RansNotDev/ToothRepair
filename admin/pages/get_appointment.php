<?php
session_start();
include('../database/db_connection.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid appointment ID");
}

$action = $_GET['action'] ?? 'view';
$appointmentId = $_GET['id'];

// Fetch appointment data
$stmt = $conn->prepare("
    SELECT a.*, u.* 
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    WHERE a.appointment_id = ?
");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($action === 'view') {
    // View Modal Content
    ?>
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">View Appointment</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Patient Information</h5>
                <p><strong>Name:</strong> <?= htmlspecialchars($row['fullname']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
            </div>
            <div class="col-md-6">
                <h5>Appointment Details</h5>
                <p><strong>Date:</strong> <?= date('M j, Y', strtotime($row['appointment_date'])) ?></p>
                <p><strong>Service:</strong> <?= htmlspecialchars($row['service']) ?></p>
                <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>
    <?php
} elseif ($action === 'edit') {
    // Edit Form Content
    ?>
    <form action="edit_appointment.php" method="POST">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Edit Appointment</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-control" 
                       value="<?= htmlspecialchars($row['fullname']) ?>" required>
            </div>
            
            <!-- Add other form fields here -->
            
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
    </form>
    <?php
}