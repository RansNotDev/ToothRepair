<?php
require("../database/db_connection.php");
include_once("../includes/header.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
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
<link href="../assets/Assetscalendar/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="../assets/Assetscalendar/fullcalendar/main.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="../admin/css/sb-admin-2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<div class="container-fluid py-4 bg-light">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-header mb-4 bg-primary p-4 rounded shadow-sm">
                <h1 class="h3 text-white fw-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h1>
                <p class="text-white-50">Here's your appointment overview</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Booking Form Card -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title mb-4">Book New Appointment</h4>
                    <form id="appointmentForm" method="POST">
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
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.querySelector('input[name="appointment_date"]').addEventListener('change', function() {
    const selectedDate = this.value;
    const timeSelect = document.getElementById('time');
    
    // Clear existing options
    timeSelect.innerHTML = '<option value="">Select Time</option>';
    
    // Fetch available times for selected date
    fetch(`get_available_times.php?date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                generateTimeSlots(data.time_start, data.time_end).forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.value;
                    option.textContent = slot.display;
                    timeSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
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
</script>