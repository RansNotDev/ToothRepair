<?php
session_start();
require_once('../includes/auth.php');
checkAdminRole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Process form data
    $required = ['user_id', 'appointment_date', 'service', 'status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) die("Required field missing: $field");
    }

    include('../database/db_connection.php');
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, service, status, message) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_POST['user_id'], $_POST['appointment_date'], 
                     $_POST['service'], $_POST['status'], $_POST['message']);
    
    if ($stmt->execute()) {
        header('Location: appointmentlist.php?success=1');
        exit();
    } else {
        die("Error creating appointment: " . $conn->error);
    }
}

// Fetch users for dropdown
include('../database/db_connection.php');
$users = $conn->query("SELECT user_id, fullname FROM users");

include_once('../includes/header.php');
?>
<div class="container">
    <h2>Add New Appointment</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
            <label>Patient</label>
            <select name="user_id" class="form-control" required>
                <?php while($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['fullname']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Appointment Date & Time</label>
            <input type="datetime-local" name="appointment_date" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Service</label>
            <input type="text" name="service" class="form-control" required>
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

        <div class="form-group">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Appointment</button>
        <a href="appointmentlist.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include_once('../includes/footer.php'); ?>