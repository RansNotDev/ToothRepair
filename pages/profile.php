<?php
require("../database/db_connection.php");
include_once("../includes/header.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="container-fluid py-4 bg-light">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary p-4 rounded shadow-sm">
                <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h1>
                <p class="text-white-50">Here's your profile settings</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Form Card -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">Update Profile</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" class="form-control" name="fullname" 
                                value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact Number</label>
                            <input type="tel" class="form-control" name="contact_number" 
                                value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="userdashboard.php" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Return to Home
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary mb-4">Quick Actions</h5>
                    <div class="d-grid gap-3">
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-history text-primary me-3"></i>
                            <span>View History</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-user text-primary me-3"></i>
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-sign-out-alt text-primary me-3"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>