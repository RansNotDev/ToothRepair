<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


error_log("Session data: " . print_r($_SESSION, true));

require("../database/db_connection.php");

// Add error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
    exit;
}

// Add this check to disable the form if feedback was already submitted today
$disable_form = false;
$today = date('Y-m-d');
if (isset($_SESSION['user_id'])) {
    $check_sql = "SELECT COUNT(*) as count FROM feedbacks 
                  WHERE user_id = ? 
                  AND DATE(created_at) = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $_SESSION['user_id'], $today);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $disable_form = ($row['count'] > 0);
    $check_stmt->close();
}

// Update the POST handling section
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user has already submitted feedback today
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    $check_sql = "SELECT COUNT(*) as count FROM feedbacks 
                  WHERE user_id = ? 
                  AND DATE(created_at) = ?";
                  
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $user_id, $today);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "You have already submitted feedback today. Please try again tomorrow.";
        error_log("User $user_id attempted multiple feedback submissions on $today");
    } else {
        error_log("Debug: Starting feedback submission process");
        error_log("POST data: " . print_r($_POST, true));
        error_log("Session data: " . print_r($_SESSION, true));
        
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
        } elseif (empty($satisfaction) || !in_array($satisfaction, ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied'])) {
            $error_message = "Please select a valid satisfaction level";
            error_log("Invalid satisfaction level: " . $satisfaction);
        } else {
            try {
                error_log("Debug: Starting database transaction");
                $conn->begin_transaction();

                // Add parameter debugging
                error_log("Parameters to be inserted:");
                error_log("user_id: " . $user_id);
                error_log("rating: " . $rating);
                error_log("feedback: " . $feedback);
                error_log("satisfaction: " . $satisfaction);
                error_log("current_datetime: " . $current_datetime);

                // Insert into feedbacks table
                $sql = "INSERT INTO feedbacks (user_id, rating, feedback_text, satisfaction_level, created_at) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                if (!$stmt->bind_param("iisss", $user_id, $rating, $feedback, $satisfaction, $current_datetime)) {
                    throw new Exception("Binding parameters failed: " . $stmt->error);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $affected_rows = $stmt->affected_rows;
                error_log("Affected rows: " . $affected_rows);

                if ($affected_rows === 0) {
                    throw new Exception("No rows were inserted");
                }

                $conn->commit();
                error_log("Transaction committed successfully");
                
                // Set success message
                $success_message = "Thank you for your feedback! Your response has been recorded.";
                
                // Clear form data
                $_POST = array();
                
                // Log successful submission
                error_log("Feedback successfully saved for user_id: $user_id");
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                
                $error_message = "Error saving feedback: " . $e->getMessage();
                error_log("Database error in feedback submission: " . $e->getMessage());
            } finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
            }
        }
    }
    $check_stmt->close();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            padding: 20px 0;
        }

        .rating input {
            display: none;
        }

        .rating label {
            cursor: pointer;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #ddd;
            transition: color 0.2s ease;
        }

        .rating label:before {
            content: '\f005';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }

        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffd700;
        }

        .rating-text {
            margin-top: 15px;
            text-align: center;
            font-size: 16px;
            color: #666;
            font-weight: 500;
        }

        .rating-container {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 100%;
            overflow-x: auto;
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
    background-color: rgb(51, 94, 139) !important;
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
    /* Add to your existing styles */
    form.disabled {
        opacity: 0.7;
        pointer-events: none;
    }
    
    form.disabled input,
    form.disabled select,
    form.disabled textarea,
    form.disabled button {
        cursor: not-allowed;
    }

    @media (max-width: 575.98px) {
        .rating label {
            width: 40px;
            height: 40px;
            font-size: 24px;
        }
        
        .dashboard-header .d-flex {
            flex-direction: column;
            text-align: center;
        }
        
        .dashboard-header img {
            margin: 0 auto 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-header img {
            height: 50px !important;
            margin-bottom: 0.5rem;
            min-width: 350px;
        }
        
        .dashboard-header h2 {
            font-size: 1.1rem;
        }
        
        .dashboard-header h1 {
            font-size: 1.3rem;
        }
        
        .dashboard-header .d-flex {
            flex-direction: column;
            align-items: center;
            min-width: 350px;
        }
        
        .rating label {
            width: 35px;
            height: 35px;
            font-size: 20px;
        }
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
                            <span>Home</span>
                        </a>
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <span>View History</span>
                        </a>
                        <a href="user_feedback.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn active">
                            <span>Feed Back</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center quick-action-btn">
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

                    <?php
                    // Add this before the form:
                    if (isset($_GET['test'])) {
                        echo "<div class='alert alert-info'>";
                        echo "Testing database connection...<br>";
                        try {
                            $test_sql = "SELECT 1 FROM feedbacks LIMIT 1";
                            $result = $conn->query($test_sql);
                            echo "Database connection successful. Table exists.<br>";
                            echo "MySQL version: " . $conn->server_info;
                        } catch (Exception $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        echo "</div>";
                    }
                    ?>

                    <!-- Modify the form tag to include the disabled state -->
                    <form method="POST" action="" id="feedbackForm" <?php echo $disable_form ? 'class="disabled"' : ''; ?>>
                        <?php if ($disable_form): ?>
                            <div class="alert alert-info">
                                You have already submitted feedback today. Please come back tomorrow to share more feedback.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add disabled attribute to form elements -->
                        <div class="mb-4">
                            <h5 class="text-center mb-3">Rate our clinic</h5>
                            <div class="rating-container">
                                <div class="rating">
                                    <input type="radio" id="star5" name="rating" value="5" required <?php echo $disable_form ? 'disabled' : ''; ?>/>
                                    <label for="star5" data-rating="5"></label>
                                    
                                    <input type="radio" id="star4" name="rating" value="4" <?php echo $disable_form ? 'disabled' : ''; ?>/>
                                    <label for="star4" data-rating="4"></label>
                                    
                                    <input type="radio" id="star3" name="rating" value="3" <?php echo $disable_form ? 'disabled' : ''; ?>/>
                                    <label for="star3" data-rating="3"></label>
                                    
                                    <input type="radio" id="star2" name="rating" value="2" <?php echo $disable_form ? 'disabled' : ''; ?>/>
                                    <label for="star2" data-rating="2"></label>
                                    
                                    <input type="radio" id="star1" name="rating" value="1" <?php echo $disable_form ? 'disabled' : ''; ?>/>
                                    <label for="star1" data-rating="1"></label>
                                </div>
                                <div class="rating-text" id="ratingText">Click a star to rate</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Satisfaction Level</h5>
                            <select class="form-control" name="satisfaction" required <?php echo $disable_form ? 'disabled' : ''; ?>>
                                <option value="">Select your satisfaction level...</option>
                                <option value="Very Satisfied">Very Satisfied</option>
                                <option value="Satisfied">Satisfied</option>
                                <option value="Neutral">Neutral</option>
                                <option value="Dissatisfied">Dissatisfied</option>
                                <option value="Very Dissatisfied">Very Dissatisfied</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <h5>Your Feedback</h5>
                            <textarea class="form-control" name="feedback" rows="5" 
                                placeholder="Please share your experience with us..." required <?php echo $disable_form ? 'disabled' : ''; ?>></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" <?php echo $disable_form ? 'disabled' : ''; ?>>Submit Feedback</button>
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

// Add this to your existing script section
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('.rating input');
    const ratingText = document.getElementById('ratingText');
    
    const ratingDescriptions = {
        5: 'Excellent - Very satisfied with the service ★★★★★',
        4: 'Very Good - Above average experience ★★★★',
        3: 'Good - Met expectations ★★★',
        2: 'Fair - Below average experience ★★',
        1: 'Poor - Unsatisfied with the service ★'
    };

    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            ratingText.textContent = ratingDescriptions[rating];
            
            // Update all stars
            document.querySelectorAll('.rating label').forEach(label => {
                const labelRating = label.getAttribute('data-rating');
                if (labelRating <= rating) {
                    label.style.color = '#ffd700'; // Selected stars
                } else {
                    label.style.color = '#ddd'; // Unselected stars
                }
            });
        });
    });

    // Add hover effect
    document.querySelectorAll('.rating label').forEach(label => {
        label.addEventListener('mouseover', function() {
            const rating = this.getAttribute('data-rating');
            document.querySelectorAll('.rating label').forEach(l => {
                const labelRating = l.getAttribute('data-rating');
                if (labelRating <= rating) {
                    l.style.color = '#ffd700';
                }
            });
        });

        label.addEventListener('mouseout', function() {
            const selectedRating = document.querySelector('.rating input:checked');
            document.querySelectorAll('.rating label').forEach(l => {
                const labelRating = l.getAttribute('data-rating');
                if (!selectedRating || labelRating > selectedRating.value) {
                    l.style.color = '#ddd';
                }
            });
        });
    });

    // Form validation enhancement
    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const rating = document.querySelector('input[name="rating"]:checked');
        const satisfaction = document.querySelector('select[name="satisfaction"]').value;
        const feedback = document.querySelector('textarea[name="feedback"]').value.trim();
        
        if (!rating) {
            Swal.fire({
                icon: 'error',
                title: 'Rating Required',
                text: 'Please select a star rating (1-5)'
            });
            return;
        }
        
        if (!satisfaction) {
            Swal.fire({
                icon: 'error',
                title: 'Satisfaction Level Required',
                text: 'Please select your satisfaction level'
            });
            return;
        }
        
        if (!feedback) {
            Swal.fire({
                icon: 'error',
                title: 'Feedback Required',
                text: 'Please provide your feedback'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Submission',
            html: `
                <p>Rating: ${rating.value} stars</p>
                <p>Satisfaction: ${satisfaction}</p>
                <p>Feedback: ${feedback}</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Review'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});

// Replace or update the existing form submission handler
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rating = document.querySelector('input[name="rating"]:checked');
    const satisfaction = document.querySelector('select[name="satisfaction"]').value;
    const feedback = document.querySelector('textarea[name="feedback"]').value.trim();
    
    // Validate all required fields
    if (!rating || !satisfaction || !feedback) {
        Swal.fire({
            icon: 'error',
            title: 'Required Fields Missing',
            text: 'Please fill in all required fields'
        });
        return;
    }

    // Show confirmation dialog
    Swal.fire({
        title: 'Confirm Feedback Submission',
        html: `
            <div class="text-left">
                <p><strong>Rating:</strong> ${rating.value} stars</p>
                <p><strong>Satisfaction:</strong> ${satisfaction}</p>
                <p><strong>Feedback:</strong> ${feedback}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Submit Feedback',
        cancelButtonText: 'Review',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
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
        }
    });
});

// Add to your existing DOMContentLoaded event listener
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    if (this.classList.contains('disabled')) {
        e.preventDefault();
        Swal.fire({
            icon: 'info',
            title: 'Feedback Limited',
            text: 'You can only submit one feedback per day. Please try again tomorrow.'
        });
        return false;
    }
    // ... rest of your existing form submission code ...
});
</script>
</body>
</html>