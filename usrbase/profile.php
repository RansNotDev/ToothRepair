<?php
require "../database/db_connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!--cdn online bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/fullcalendar/main.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css"  type="text/css">
    <link rel="stylesheet" href="../admin/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" >


    <style>
       body 
{
    background-color: #f8f9fc;
}
        .card {
            transition: none !important;
        }
        .card:hover {
            transform: none !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
.dashboard-header .btn-light {
    transition: all 0.3s;
    background: rgba(255,255,255,0.9);
    border: none;
    padding: 0.5rem 1rem;
}

.dashboard-header .btn-light:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .dashboard-header .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .dashboard-header .btn-light {
        margin-bottom: 0.5rem;
    }
    .quick-actions-card {
        margin-top: 1rem;
    }
}
.detail-card {
    transition: transform 0.2s;
}

.detail-card:hover {
    transform: translateY(-5px);
}

.action-buttons .btn {
    transition: all 0.3s;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
}

.progress-tracker .badge {
    font-size: 0.8rem;
}

.header-action-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: none;
    transition: all 0.3s;
    text-align: left;
    padding: 0.5rem 1rem;
    width: 100%;
    border-radius: 0.5rem;
}

.header-action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: translateX(5px);
}

.upcoming-appointment {
    transition: transform 0.2s;
    border-left: 4px solid #4e73df;
}

.upcoming-appointment:hover {
    transform: translateX(5px);
}

.badge {
    padding: 0.5em 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.active {
        background-color:rgb(51, 94, 139) !important;
        color: white !important;
    }
.sticky-top {
    position: sticky;
    top: 20px;
    z-index: 1000;
}

@media (max-width: 991px) {
    .sticky-top {
        position: relative;
        top: 0;
    }
}

.quick-actions .btn {
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    transform: translateX(5px);
    background-color: #f8f9fa;
}

.btn i {
    width: 20px;
    text-align: center;
}

.bg-gradient-dark {
    background: rgb(152, 193, 233);
    position: relative;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.1));
    pointer-events: none;
}

/* Optional: Add a subtle glow effect to the logo */
.dashboard-header img {
    filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.3));
    transition: filter 0.3s ease;
}

.dashboard-header img:hover {
    filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
}

.dashboard-header {
    position: relative;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.05);
    pointer-events: none;
}
.quick-action-btn {
    transition: none !important;
    border: none !important;
    box-shadow: none !important;
    margin-bottom: 5px;
    position: relative;
}

.quick-action-btn:hover {
    background-color: #f8f9fa !important;
    transform: none !important;
}

.quick-action-btn.active {
    background-color: rgba(98, 160, 223, 0.9) !important;
    color: white !important;
}

.quick-action-btn.active i {
    color: white !important;
}

.position-sticky {
    position: sticky !important;
    top: 20px !important;
    z-index: 1000;
}
    </style>
</head>
<body>
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12 mx-0 px-0">
            <div class="dashboard-header bg-gradient-dark p-4 rounded-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Left side - Logo and Title -->
                    <div class="d-flex align-items-center">
                        <img src="../images/logo/cliniclogo.png" alt="Tooth Repair Logo" class="mr-3" style="height: 80px; width: auto;">
                        <h2 class="h4 text-primary mb-0">Tooth Repair Dental Clinic</h2>
                    </div>
                    <!-- Right side - Welcome message -->
                    <div class="text-right">
                        <h1 class="h3 text-primary fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></h1>
                        <p class="text-primary mb-0">Update your profile information</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Quick Actions Card - Left Column -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-lg position-sticky" style="top: 20px;">
                <div class="card-body p-4">
                    
                    <div class="d-grid gap-3">
                        <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-home text-primary me-3"></i>
                            <span>Home</span>
                        </a>
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-calendar-plus text-primary me-3"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-history text-primary me-3"></i>
                            <span>View History</span>
                        </a>
                        <a href="user_feedback.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-comment-dots text-primary me-3"></i>
                            <span>Feed Back</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-user text-primary me-3"></i>
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <i class="fas fa-sign-out-alt text-primary me-3"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form Card - Right Column -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">Update Profile</h4>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact Number</label>
                            <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Password (leave blank to keep current password)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="userdashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Return to Home</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to update your profile?')) {
        this.submit();
    }
});

function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of your account",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
    return false;
}

document.addEventListener("DOMContentLoaded", function () {
    let links = document.querySelectorAll(".quick-action-btn");
    let currentUrl = window.location.pathname.split("/").pop();

    links.forEach(link => {
        if (link.getAttribute("href") === currentUrl) {
            link.classList.add("active");
            link.querySelector("i").classList.remove("text-primary");
        }
    });
});
</script>
</body>
</html>