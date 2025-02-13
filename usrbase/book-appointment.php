<?php
require("../database/db_connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: entryvault.php");
    exit;
}

// Get available dates and times
$availability = [];
$result = mysqli_query(
    $conn,
    "SELECT available_date, time_start, time_end 
     FROM availability_tb 
     WHERE is_active = 1"
);
while ($row = mysqli_fetch_assoc($result)) {
    $availability[$row['available_date']] = $row;
}

// Fetch available services
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);
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
        .active {
        background-color: #007bff !important;
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

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Add spacing between icon and text in quick action buttons */
    .btn i {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    </style>
</head>
<body>
<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary bg-gradient p-4 rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></h1>
                        <p class="text-white">Book New Appointment Here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions Card - Left Column -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary mb-4">Quick Actions</h5>
                    <div class="d-grid gap-3">
                        <a href="userdashboard.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-home text-primary"></i>
                            <span>Home</span>
                        </a>
                        <a href="book-appointment.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-calendar-plus text-primary"></i>
                            <span>Book New Appointment</span>
                        </a>
                        <a href="appointment-history.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-history text-primary"></i>
                            <span>View History</span>
                        </a>
                        <a href="user_feedback.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-comment-dots text-primary"></i>
                            <span>Feed Back</span>
                        </a>
                        <a href="profile.php" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-user text-primary"></i>
                            <span>Update Profile</span>
                        </a>
                        <a href="logout.php" onclick="return confirmLogout();" class="btn btn-light text-start p-3 d-flex align-items-center">
                            <i class="fas fa-sign-out-alt text-primary"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form Card - Right Column -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">Book New Appointment</h4>
                    <form id="appointmentForm" method="POST" action="process_booking.php">
                        <div class="mb-3">
                            <label>Select Service</label>
                            <select class="form-control" name="service_id" required>
                                <option value="">Choose a service...</option>
                                <?php while($service = $services_result->fetch_assoc()): ?>
                                    <option value="<?php echo $service['service_id']; ?>">
                                        <?php echo htmlspecialchars($service['service_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Preferred Date</label>
                            <input type="date" class="form-control" name="appointment_date" required>
                        </div>
                        <div class="form-group">
                                <label for="time">Preferred Time</label>
                                <select class="form-control" id="time" name="appointment_time" required>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Book Appointment</button>
                            <a href="userdashboard.php" class="btn btn-secondary">
                                <i class=""></i> Return to Home
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let links = document.querySelectorAll(".card-body a");
        let currentUrl = window.location.pathname.split("/").pop();

        links.forEach(link => {
            if (link.getAttribute("href") === currentUrl) {
                link.classList.add("active");
            }
        });
    });
// Update the existing time slot fetch event listener
document.querySelector('input[name="appointment_date"]').addEventListener('change', function() {
    const selectedDate = this.value;
    const today = new Date().toISOString().split('T')[0];
    
    // Check if selected date is not in the past
    if (selectedDate < today) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Date',
            text: 'Please select a future date'
        });
        this.value = '';
        return;
    }
    
    const timeSelect = document.getElementById('time');
    timeSelect.innerHTML = '<option value="">Select Time</option>';
    
    // Add loading indicator
    timeSelect.disabled = true;
    
    // Modified fetch call to check both availability and existing appointments
    fetch(`check_slot_availability.php?date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            timeSelect.disabled = false;
            if (data.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error
                });
                return;
            }
            
            if (data.available) {
                // Filter out booked slots
                const availableSlots = generateTimeSlots(data.time_start, data.time_end).filter(slot => 
                    !data.booked_slots.includes(slot.value)
                );
                
                if (availableSlots.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Fully Booked',
                        text: 'All time slots for this date are already booked.'
                    });
                } else {
                    availableSlots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.value;
                        option.textContent = slot.display;
                        timeSelect.appendChild(option);
                    });
                }
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'No Available Slots',
                    text: 'No appointments available for this date'
                });
            }
        })
        .catch(error => {
            timeSelect.disabled = false;
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch available times'
            });
        });
});

function generateTimeSlots(startTime, endTime) {
    const slots = [];
    const start = new Date(`1970-01-01T${startTime}`);
    const end = new Date(`1970-01-01T${endTime}`);

    let current = new Date(start);
    while (current <= end) {
        const hours = current.getHours();
        const minutes = current.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedTime = `${formattedHours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
        
        slots.push({
            display: formattedTime,
            value: `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`
        });
        
        current.setMinutes(current.getMinutes() + 30);
    }
    return slots;
}

// Replace the existing form submission handler with this updated version:

document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const service = this.querySelector('[name="service_id"]').value;
    const date = this.querySelector('[name="appointment_date"]').value;
    const time = this.querySelector('[name="appointment_time"]').value;
    
    // Basic validation
    if (!service || !date || !time) {
        Swal.fire({
            icon: 'error',
            title: 'Incomplete Form',
            text: 'Please fill all required fields'
        });
        return;
    }

    // Show initial loading state
    Swal.fire({
        title: 'Checking availability...',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Check if slot is occupied
    fetch('check_occupied_slot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.occupied) {
            Swal.fire({
                icon: 'error',
                title: 'Time Slot Not Available',
                text: 'Sorry, this time slot has already been booked. Please select a different time.',
                confirmButtonText: 'Choose Another Time'
            });
        } else {
            // Show booking confirmation
            Swal.fire({
                icon: 'question',
                title: 'Confirm Booking',
                html: `
                    <p>Please confirm your appointment details:</p>
                    <div class="text-left">
                        <p><strong>Date:</strong> ${date}</p>
                        <p><strong>Time:</strong> ${time}</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirm Booking',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show final loading state
                    Swal.fire({
                        title: 'Booking Appointment',
                        text: 'Processing your request...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // Submit the form
                    this.submit();
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while checking availability. Please try again.'
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>