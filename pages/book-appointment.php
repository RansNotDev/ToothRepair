<?php
require("../database/db_connection.php");
include_once("../includes/header.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit;
}

// Fetch available services
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
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
                        <div class="mb-3">
                            <label>Preferred Time</label>
                            <input type="time" class="form-control" name="appointment_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>