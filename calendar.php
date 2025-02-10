<?php
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

include_once('database/db_connection.php');
include_once('includes/header.php');

// Get clinic settings
$max_daily = 20;
$settingsResult = mysqli_query($conn, "SELECT max_daily_appointments FROM clinic_settings LIMIT 1");
if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {
    $max_daily = mysqli_fetch_assoc($settingsResult)['max_daily_appointments'];
}

// Get closure dates
$closures = [];
$result = mysqli_query($conn, "SELECT closure_date FROM closures");
while ($row = mysqli_fetch_assoc($result)) {
    $closures[] = $row['closure_date'];
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

// Get booked time slots
$bookedSlots = [];
$result = mysqli_query(
    $conn,
    "SELECT appointment_date, appointment_time 
     FROM appointments 
     WHERE status IN ('confirmed','pending')"
);
while ($row = mysqli_fetch_assoc($result)) {
    $bookedSlots[$row['appointment_date']][] = $row['appointment_time'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tooth Repair Clinic - Dashboard</title>
    <link href="assets/Assetscalendar/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="admin/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<style>
    .has-slots {
        background-color: rgba(78, 115, 223, 0.1) !important;
        
    }
    .fc-day-disabled .slot-info {
        color: #718096 !important;
        
    }
    .has-slots {
        font-weight: bold;
        color: #2b6cb0;
    }
    .fc-daygrid-day-frame {
        min-height: 120px !important;  /* Increased height */
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        padding: 5px !important;
    }

    .fc-daygrid-day-number {
        font-size: 2rem !important;  /* Larger date size */
        font-weight: bold !important;
        margin: 5px 0 15px 0 !important;  /* Add bottom margin to separate from slot info */
        width: 100% !important;
        text-align: center !important;
        color:rgb(0, 0, 0);
    }
    
    .fc-day-disabled {
        background-color: #f8f9fc;
        opacity: 0.6;
    }
    .slot-info {
        color:rgb(49, 109, 212);
        text-align: center !important;
        width: 100% !important;
        padding: 5px !important;
        font-size: 1rem !important;
        border-top: 1px solid #edf2f7 !important;  /* Add separator line */
        margin-top: auto !important;
        font-weight: bold;
    }
    .time-slot {
        margin: 5px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
    }
    .time-slot.available {
        background-color: #e7f4ff;
        border-color: #4e73df;
        color: #2a4365;
    }
    .time-slot.booked {
        background-color: #f8d7da;
        border-color: #dc3545;
        cursor: not-allowed;
    }
    .time-slot.selected {
        background-color: #4e73df !important;
        color: white !important;
    }
    .btn-primary:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<div class="container-fluid">
    <div class="bg-primary text-white p-3 mb-4">
        <div class="container">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0">Appointment Calendar</h1>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="card ">
            <div class="card-body ">
                <div id="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="appointmentModalLabel">
                    <i class="fas fa-calendar-plus mr-2"></i>Book an Appointment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Top Row - Two Columns -->
                <div class="row mb-4">
                    <!-- Left Column - Personal Information -->
                    <div class="col-md-6 border-right">
                        <h5 class="text-primary mb-4"><i class="fas fa-user mr-2"></i>Personal Information</h5>
                        <form action="save_appointment.php" method="POST" id="appointmentForm">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="fullname" placeholder="Enter your name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="contact_number" placeholder="Enter your phone number">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                        </div>

                    <!-- Right Column - Appointment Details -->
                    <div class="col-md-6">
                        <h5 class="text-primary mb-4"><i class="fas fa-clock mr-2"></i>Appointment Details</h5>
                            <div class="form-group">
                                <label for="date">Appointment Date</label>
                                <input type="date" class="form-control" id="date" name="appointment_date" readonly required>
                            </div>
                            <div class="form-group">
                                <label for="time">Appointment Time</label>
                                <select class="form-control" id="time" name="appointment_time" required>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="service">Select Service</label>
                                <select class="form-control" id="service" name="service" required>
                                    <option value="" disabled selected>Select a service</option>
                                    <?php
                                    $services = mysqli_query($conn, "SELECT * FROM services");
                                    if ($services) {
                                        while ($service = mysqli_fetch_assoc($services)) {
                                            $service_id = htmlspecialchars($service['service_id']);
                                            $service_name = htmlspecialchars($service['service_name']);
                                            echo "<option value='{$service_id}'>{$service_name}</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>Error fetching services</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="text-center mt-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <b class="text-primary">terms and conditions</b>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg px-5" disabled>
    <i class="fas fa-check mr-2"></i>Confirm Booking
</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bottom Section - Guidelines (Merged Columns) -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body bg-light">
                                <h4 class="text-primary mb-4 text-center">
                                    <i class="fas fa-info-circle mr-2"></i>Appointment Guidelines
                                </h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                            <h5>Duration</h5>
                                            <p class="mb-0">30 minutes per session</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-hourglass-start fa-2x text-primary mb-2"></i>
                                            <h5>Arrival Time</h5>
                                            <p class="mb-0">Please arrive 10 minutes early</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-ban fa-2x text-primary mb-2"></i>
                                            <h5>Cancellation Policy</h5>
                                            <p class="mb-0">24-hour notice required</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-4 mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-medical fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="alert-heading mb-1">Important Notice</h5>
                                            <p class="mb-0">Please bring valid ID and any relevant medical records.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Define PHP variables for JavaScript use
   
    const closures = <?php echo json_encode($closures); ?>;
    const bookedSlots = <?php echo json_encode($bookedSlots); ?>;
    const maxDaily = <?php echo $max_daily; ?>;
    const availability = <?php echo json_encode($availability); ?>;
    // Get calendar element
    const calendarEl = document.getElementById('calendar');
    
    // Initialize calendar with defined variables
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        validRange: { start: new Date() },
        timeZone: 'Asia/Manila',
        dateClick: function (info) {
            const dateStr = info.dateStr;
            const isPast = info.date < new Date().setHours(0, 0, 0, 0);
            const isAvailable = availability[dateStr] && !closures.includes(dateStr);

            if (!isPast && isAvailable) {
                const { time_start, time_end } = availability[dateStr];
                const slots = generateTimeSlots(time_start, time_end);
                const booked = bookedSlots[dateStr] || [];
                const bookedCount = booked.length;
                const remaining = Math.max(maxDaily - bookedCount, 0);

                $('#date').val(dateStr);
                const timeSelect = $('#time');
                timeSelect.empty().append('<option value="">Select Time</option>');

                slots.forEach(slot => {
                    if (!booked.includes(slot.value) && remaining > 0) {
                        timeSelect.append(`<option value="${slot.value}">${slot.display}</option>`);
                    }
                });

                $('#appointmentModal').modal('show');
            }
        },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            dayCellContent: function (arg) {
                const dateStr = arg.date.toISOString().split('T')[0];
                const isPast = arg.date < new Date().setHours(0, 0, 0, 0);
                const isAvailable = availability[dateStr] && !closures.includes(dateStr);
                const bookedCount = bookedSlots[dateStr]?.length || 0;
                const remaining = Math.max(maxDaily - bookedCount, 0);

                let slotInfo = '';
                if (!isPast) {
                    if (!isAvailable) {
                        slotInfo = 'Closed';
                    } else {
                        slotInfo = `${remaining} slot${remaining !== 1 ? 's' : ''} available`;
                    }
                }

                return {
                    html: `
                    <div class="fc-daygrid-day-number">${arg.dayNumberText}</div>
                    <div class="slot-info small">${slotInfo}</div>
                `
                };
            },
            dayCellClassNames: function (arg) {
                const dateStr = arg.date.toISOString().split('T')[0];
                const isPast = arg.date < new Date().setHours(0, 0, 0, 0);
                const isAvailable = availability[dateStr] && !closures.includes(dateStr);
                const bookedCount = bookedSlots[dateStr]?.length || 0;
                const hasSlots = maxDaily - bookedCount > 0;

                let classes = [];
                if (isPast || !isAvailable || !hasSlots) classes.push('fc-day-disabled');
                if (isAvailable && hasSlots) classes.push('has-slots');
                return classes;
            }
        });

        calendar.render();

        function generateTimeSlots(startTime, endTime) {
    const slots = [];
    const start = new Date(`1970-01-01T${startTime}`);
    const end = new Date(`1970-01-01T${endTime}`);

    let current = new Date(start);
    while (current <= end) {
        // Convert to 12-hour format for display
        const hours = current.getHours();
        const minutes = current.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedTime = `${formattedHours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
        
        // Store both display format and 24-hour format
        slots.push({
            display: formattedTime,
            value: `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`
        });
        
        current.setMinutes(current.getMinutes() + 30);
    }
    return slots;
}

        $('#appointmentForm').on('submit', function(e) {
    e.preventDefault();
    
    if(!this.checkValidity()) {
        e.stopPropagation();
        return false;
    }
    
    const formData = new FormData(this);
    
    // Get appointment details for email
    const appointmentDate = $('#date').val();
    const appointmentTime = $('#time option:selected').text();
    const serviceName = $('#service option:selected').text();
    const fullName = $('#name').val();
    
    $.ajax({
        url: 'save_appointment.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                const email = $('#email').val();
                const plainPassword = response.password; // Get the plain password from response
                
                // Send confirmation email with enhanced design and appointment details
                $.ajax({
                    url: 'sendmail.php',
                    type: 'POST',
                    data: {
                        sendmail: true,
                        email: email,
                        subject: 'ToothRepair Appointment Confirmation',
                        message: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; border-radius: 10px;">
                                <div style="text-align: center; background-color: #4e73df; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                                    <h2 style="color: white; margin: 0;">Welcome to ToothRepair Dental Clinic!</h2>
                                </div>
                                
                                <div style="background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <h3 style="color: #4e73df; margin-bottom: 15px;">Your Appointment Details</h3>
                                    <p><strong>Patient Name:</strong> ${fullName}</p>
                                    <p><strong>Date:</strong> ${appointmentDate}</p>
                                    <p><strong>Time:</strong> ${appointmentTime}</p>
                                    <p><strong>Service:</strong> ${serviceName}</p>
                                </div>
                                
                                <div style="background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <h3 style="color: #4e73df; margin-bottom: 15px;">Your Login Credentials</h3>
                                    <p><strong>Email:</strong> ${email}</p>
                                    <p><strong>Password:</strong> ${plainPassword}</p>
                                    <p style="color: #dc3545;"><em>Please change your password upon first login.</em></p>
                                </div>
                                
                                <div style="text-align: center; margin-top: 20px;">
                                    <p style="color: #6c757d;">Thank you for choosing ToothRepair Dental Clinic!</p>
                                    <p style="font-size: 12px; color: #6c757d;">If you have any questions, please contact us.</p>
                                </div>
                            </div>
                        `
                    },
                    success: function() {
                        Swal.fire({
                            title: 'Appointment Booked Successfully!',
                            html: `
                                <div class="text-left">
                                    <p>An email has been sent with your:</p>
                                    <ul>
                                        <li>Appointment details</li>
                                        <li>Login credentials</li>
                                    </ul>
                                    <p>Please check your email inbox.</p>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Proceed to Login?',
                                    text: 'Click below to go to the login page',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Proceed to Login',
                                    cancelButtonText: 'Stay Here'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'pages/loginpage.php';
                                    }
                                });
                            }
                        });
                        $('#appointmentModal').modal('hide');
                        calendar.refetchEvents();
                    },
                    error: function() {
                        console.log('Error sending confirmation email');
                    }
                });
            } else {
                Swal.fire('Error!', response.message || 'Failed to book appointment.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            Swal.fire('Error!', 'Failed to book appointment.', 'error');
        }
    });
});
    });

$(document).ready(function() {
    // Initially disable the submit button
    $('.btn-primary[type="submit"]').prop('disabled', true);
    
    // Listen for changes on the terms checkbox
    $('#terms').change(function() {
        // Enable/disable submit button based on checkbox state
        $('.btn-primary[type="submit"]').prop('disabled', !this.checked);
    });
});
</script>
<?php include_once('includes/footer.php'); ?>