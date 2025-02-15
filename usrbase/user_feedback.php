<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

require("../database/db_connection.php");

// Add error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
    exit;
}

// Replace the existing POST handling section with this:
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST data: " . print_r($_POST, true));
    
    $user_id = $_SESSION['user_id'] ?? null;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $feedback = trim($_POST['feedback'] ?? '');
    $satisfaction = $_POST['satisfaction'] ?? '';
    $current_datetime = date('Y-m-d H:i:s');

    // Validate inputs
    if (!$user_id) {
        $error_message = "User ID is missing. Please login again.";
        error_log("Missing user_id in session");
    } elseif (!$rating || $rating < 1 || $rating > 5) {
        $error_message = "Please select a valid rating (1-5 stars)";
        error_log("Invalid rating: " . $rating);
    } elseif (empty($feedback)) {
        $error_message = "Please provide feedback";
        error_log("Empty feedback text");
    } elseif (empty($satisfaction)) {
        $error_message = "Please select satisfaction level";
        error_log("Empty satisfaction level");
    } else {
        try {
            // Prepare statement
            $sql = "INSERT INTO feedback (user_id, rating, feedback_text, satisfaction_level, created_at) 
                    VALUES (?, ?, ?, ?, ?)";
            
            if (!($stmt = $conn->prepare($sql))) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if (!$stmt->bind_param("iisss", $user_id, $rating, $feedback, $satisfaction, $current_datetime)) {
                throw new Exception("Binding parameters failed: " . $stmt->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $success_message = "Thank you for your feedback! Your response has been recorded.";
            $_POST = array(); // Clear form
            
            $stmt->close();
            
        } catch (Exception $e) {
            $error_message = "Error saving feedback: " . $e->getMessage();
            error_log("Database error in feedback submission: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback - ToothRepair</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }
        .rating input { display: none; }
        .rating label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .rating label.checked,
        .rating label:hover,
        .rating label:hover ~ label,
        .rating input:checked ~ label {
            color: #ffd700;
            transition: all 0.2s ease;
        }
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
                        <p class="text-primary mb-0">Here's your feedback overview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

        <!-- Feedback Form Card - Right Column -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">Share Your Experience</h4>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="feedbackForm">
                        <div class="mb-4">
                            <h5>Rate our clinic</h5>
                            <div class="rating mb-3">
                                <input type="radio" name="rating" value="5" id="star5" required>
                                <label for="star5" class="fas fa-star"></label>
                                <input type="radio" name="rating" value="4" id="star4">
                                <label for="star4" class="fas fa-star"></label>
                                <input type="radio" name="rating" value="3" id="star3">
                                <label for="star3" class="fas fa-star"></label>
                                <input type="radio" name="rating" value="2" id="star2">
                                <label for="star2" class="fas fa-star"></label>
                                <input type="radio" name="rating" value="1" id="star1">
                                <label for="star1" class="fas fa-star"></label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Satisfaction Level</h5>
                            <select class="form-control" name="satisfaction" required>
                                <option value="">Select your satisfaction level...</option>
                                <option value="Very Satisfied">Very Satisfied</option>
                                <option value="Satisfied">Satisfied</option>
                                <option value="Neutral">Neutral</option>
                                <option value="Dissatisfied">Dissatisfied</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <h5>Your Feedback</h5>
                            <textarea class="form-control" name="feedback" rows="5" 
                                placeholder="Please share your experience with us..." required></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            <a href="userdashboard.php" class="btn btn-secondary">
                                Return to Home
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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

function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

document.addEventListener("DOMContentLoaded", function () {
    let links = document.querySelectorAll(".card-body a");
    let currentUrl = window.location.pathname.split("/").pop();

    links.forEach(link => {
        if (link.getAttribute("href") === currentUrl) {
            link.classList.add("active");
        }
    });

    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const rating = document.querySelector('input[name="rating"]:checked');
        const satisfaction = document.querySelector('select[name="satisfaction"]').value;
        const feedback = document.querySelector('textarea[name="feedback"]').value;
        
        // Debug values
        console.log('Rating:', rating ? rating.value : 'not selected');
        console.log('Satisfaction:', satisfaction);
        console.log('Feedback:', feedback);
        
        // Validate form
        if (!rating || !satisfaction || !feedback.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Form',
                text: 'Please fill all required fields'
            });
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Submitting Feedback',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit the form
        this.submit();
    });

    // Add rating validation
    document.querySelectorAll('.rating input').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelectorAll('.rating label').forEach(label => {
                label.classList.remove('checked');
            });
            if (this.checked) {
                this.nextElementSibling.classList.add('checked');
            }
        });
    });
});

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}
</script>
</body>
</html>