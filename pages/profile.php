<?php
require "../database/db_connection.php";
include_once "../includes/header.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch existing user data
try {
    $query = $conn->prepare("SELECT fullname, email, contact_number FROM users WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching user data: " . $e->getMessage();
    header("Location: profile.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $password = trim($_POST['password']);

    if (empty($fullname) || empty($email) || empty($contact_number)) {
        $_SESSION['error'] = "All fields except password are required.";
        header("Location: profile.php");
        exit;
    }

    // Check if email is being changed
    if ($email !== $user['email']) {
        try {
            $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_email->bind_param("si", $email, $user_id);
            $check_email->execute();
            if ($check_email->get_result()->num_rows > 0) {
                $_SESSION['error'] = "This email is already registered by another user.";
                header("Location: profile.php");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error checking email availability: " . $e->getMessage();
            header("Location: profile.php");
            exit;
        }
    }

    try {
        $conn->begin_transaction();

        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = $conn->prepare("UPDATE users SET fullname=?, email=?, contact_number=?, password=? WHERE user_id=?");
            $update_query->bind_param("ssssi", $fullname, $email, $contact_number, $hashed_password, $user_id);
        } else {
            // Update without changing password
            $update_query = $conn->prepare("UPDATE users SET fullname=?, email=?, contact_number=? WHERE user_id=?");
            $update_query->bind_param("sssi", $fullname, $email, $contact_number, $user_id);
        }

        $update_query->execute();
        $conn->commit();

        $_SESSION['fullname'] = $fullname; // Update session
        $_SESSION['success'] = "Profile updated successfully!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
    }

    header("Location: profile.php");
    exit;
}
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

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
                    <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <span>Home</span>
                        </a>
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

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to update your profile?')) {
        this.submit();
    }
});

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}
</script>
