<?php
session_start();
require_once('../includes/auth.php');
checkAdminRole();

if (!isset($_GET['id'])) die("Appointment ID missing");
$appointmentId = (int)$_GET['id'];

include('../database/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Process update
    $stmt = $conn->prepare("UPDATE appointments SET 
                          user_id = ?, appointment_date = ?, service = ?, 
                          status = ?, message = ?
                          WHERE appointment_id = ?");
    $stmt->bind_param("issssi", $_POST['user_id'], $_POST['appointment_date'],
                     $_POST['service'], $_POST['status'], $_POST['message'], $appointmentId);
    
    if ($stmt->execute()) {
        header('Location: appointmentlist.php?success=2');
        exit();
    } else {
        die("Error updating appointment: " . $conn->error);
    }
}

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

// Fetch users
$users = $conn->query("SELECT user_id, fullname FROM users");

include_once('../includes/header.php');
?>
<div class="container">
    <h2>Edit Appointment</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
            <label>Patient</label>
            <select name="user_id" class="form-control" required>
                <?php while($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['user_id'] ?>" <?= $user['user_id'] == $appointment['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['fullname']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Appointment Date & Time</label>
            <input type="datetime-local" name="appointment_date" 
                   value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])) ?>" 
                   class="form-control" required>
        </div>

        <div class="form-group">
            <label>Service</label>
            <input type="text" name="service" value="<?= htmlspecialchars($appointment['service']) ?>" 
                   class="form-control" required>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="pending" <?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $appointment['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="form-group">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="3"><?= htmlspecialchars($appointment['message']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Appointment</button>
        <a href="appointmentlist.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include_once('../includes/footer.php'); ?>