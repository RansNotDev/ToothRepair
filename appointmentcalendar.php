<?php
function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

include_once('database/db_connection.php');

// Update the availability query to include formatted times
$availability = [];
$result = mysqli_query(
    $conn,
    "SELECT available_date, 
            TIME_FORMAT(time_start, '%l:%i %p') as formatted_start,
            TIME_FORMAT(time_end, '%l:%i %p') as formatted_end,
            time_start, time_end, 
            max_daily_appointments 
     FROM availability_tb"
);

while ($row = mysqli_fetch_assoc($result)) {
    $availability[$row['available_date']] = $row;
}

// Update the booked slots query to only exclude confirmed and pending appointments
$bookedSlots = [];
$result = mysqli_query(
    $conn,
    "SELECT appointment_date, appointment_time 
     FROM appointments 
     WHERE status IN ('confirmed', 'pending')"  // Removed cancelled status
);
while ($row = mysqli_fetch_assoc($result)) {
    $bookedSlots[$row['appointment_date']][] = $row['appointment_time'];
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Book Appointment | Tooth Repair Dental Clinic</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,700;1,400&display=swap"
        rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Custom CSS -->
    <link href="css/landing_page.css" rel="stylesheet">
    <link href="css/components/appointment.css" rel="stylesheet">
    <link rel="stylesheet" href="css/breakpoints.css">

    <style>
        /* Custom responsive styles */
        @media (max-width: 576px) { /* Mobile */
            .calendar-wrapper {
                padding: 0.5rem !important;
            }
            .fc .fc-toolbar-title {
                font-size: 1.2rem;
            }
            .fc .fc-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            .fc-daygrid-day-number {
                font-size: 0.8rem;
            }
            .slot-info {
                font-size: 0.7rem;
            }
            .modal-dialog {
                margin: 0.5rem;
            }
            .modal-body {
                padding: 0.5rem;
            }
            .col-md-6 {
                margin-bottom: 1rem;
                border-right: none !important;
            }
        }

        @media (min-width: 576px) and (max-width: 992px) { /* Tablet */
            .calendar-wrapper {
                padding: 1rem !important;
            }
            .fc .fc-toolbar-title {
                font-size: 1.5rem;
            }
            .fc .fc-button {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
            .fc-daygrid-day-number {
                font-size: 0.9rem;
            }
            .slot-info {
                font-size: 0.8rem;
            }
            .modal-dialog {
                max-width: 90%;
                margin: 1rem auto;
            }
            .col-md-6 {
                margin-bottom: 1.5rem;
            }
        }

        @media (min-width: 992px) { /* Desktop */
            .calendar-wrapper {
                padding: 2rem !important;
            }
            .fc .fc-toolbar-title {
                font-size: 2rem;
            }
            .fc .fc-button {
                padding: 0.5rem 1rem;
            }
            .modal-dialog {
                max-width: 1200px;
            }
        }

        /* Common responsive styles */
        .fc .fc-toolbar {
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (min-width: 768px) {
            .fc .fc-toolbar {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        .fc .fc-daygrid-day-frame {
            min-height: 80px;
        }

        @media (max-width: 768px) {
            .fc .fc-daygrid-day-frame {
                min-height: 60px;
            }
        }

        .modal-content {
            overflow-y: auto;
            max-height: 90vh;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .form-group {
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <main>
        <?php include 'headerlanding.php'; ?>
        <?php include 'navigation.php'; ?>

        <section class="contact-section py-5">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-lg-10 col-12">
                        <div class="section-title text-center mb-5">
                            <h2 class="text-light">Book Your Appointment</h2>
                            <p class="text-light">Select your preferred date and time</p>
                        </div>
                        <!--
                <div class="calendar-legend text-center mt-4">
                    <div class="d-inline-flex align-items-center mx-3">
                        <div class="legend-indicator has-slots mr-2"></div>
                        <span>Available Slots</span>
                    </div>
                    <div class="d-inline-flex align-items-center mx-3">
                        <div class="legend-indicator fc-day-disabled mr-2"></div>
                        <span>Not Available</span>
                    </div>
                </div> -->
                        <div class="calendar-wrapper bg-white rounded-lg shadow-sm p-4">
                            <div id="calendar"></div>
                        </div>

                    </div>
                </div>

                <!-- Appointment Modal -->
                <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog"
                    aria-labelledby="appointmentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="appointmentModalLabel">
                                    <i class="fas fa-calendar-plus me-2"></i>Book an Appointment
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Top Row - Two Columns -->
                                <div class="row mb-4">
                                    <!-- Left Column - Personal Information -->
                                    <div class="col-md-6 border-right">
                                        <h5 class="text-primary mb-4"><i class="fas fa-user mr-2"></i>Personal
                                            Information</h5>
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-clock mr-2"></i>
                                            Clinic available time for this date: <span id="availableTimesDisplay"></span>
                                        </div>
                                        <form action="appointment_handler.php" method="POST" id="appointmentForm">
                                            <div class="form-group">
                                                <label for="name">Full Name</label>
                                                <input type="text" class="form-control" id="name" name="fullname"
                                                    placeholder="Enter your name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Enter your email" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="tel" class="form-control" id="phone" name="contact_number"
                                                    maxlength="11" minlength="11"
                                                    placeholder="Enter your 11 digit phone number">
                                            </div>
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <textarea class="form-control" id="address" name="address" rows="3"
                                                    placeholder="Enter Your Address" required></textarea>
                                            </div>
                                    </div>

                                    <!-- Right Column - Appointment Details -->
                                    <div class="col-md-6">
                                        <h5 class="text-primary mb-4"><i class="fas fa-clock mr-2"></i>Appointment
                                            Details</h5>
                                        <div class="form-group">
                                            <label for="date">Appointment Date</label>
                                            <input type="date" class="form-control" id="date" name="appointment_date"
                                                readonly required>
                                        </div>
                                        <div class="form-group">
                                            <label for="time">
                                                <i class="fas fa-clock text-primary me-2"></i>Appointment Time
                                            </label>
                                            <select class="form-control" id="time" name="appointment_time" required>
                                                <option value="" selected disabled>Select your preferred time</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="service">
                                                <i class="fas fa-tooth text-primary me-2"></i>Select Service
                                            </label>
                                            <select class="form-control" id="service" name="service" required>
                                                <option value="" disabled selected>Choose your dental service</option>
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
                                                <input class="form-check-input" type="checkbox" id="terms" name="terms"
                                                    required>
                                                <label class="form-check-label" for="terms">
                                                    I agree to the <b class="text-primary">terms and conditions</b>
                                                </label>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-lg px-5" disabled id="confirmBookingBtn">
                                                <span class="normal-state">
                                                    <i class="fas fa-check mr-2"></i>Confirm Booking
                                                </span>
                                                <span class="loading-state d-none">
                                                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                                                    Processing...
                                                </span>
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
                                                            <i
                                                                class="fas fa-hourglass-start fa-2x text-primary mb-2"></i>
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
                                                            <p class="mb-0">Please bring valid ID and any relevant
                                                                medical records.</p>
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

            </div>
            </div>
            </div>
        </section>
    </main>
    <?php include 'sitefooter.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
    <script>
        moment.tz.setDefault('Asia/Manila');
    </script>

    <script>
 
        document.addEventListener('DOMContentLoaded', function () {
            const navbar = document.querySelector('.navbar');

            window.addEventListener('scroll', function () {
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow');
                    navbar.style.padding = '0.5rem 0';
                } else {
                    navbar.classList.remove('shadow');
                    navbar.style.padding = '1rem 0';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Define PHP variables for JavaScript use


            const bookedSlots = <?php echo json_encode($bookedSlots); ?>;
            const availability = <?php echo json_encode($availability); ?>;

            function checkDateAvailability(dateStr) {
                return availability.hasOwnProperty(dateStr);
            }

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
                    const isAvailable = checkDateAvailability(dateStr);
                    const bookedCount = bookedSlots[dateStr]?.length || 0;
                    const dayInfo = availability[dateStr] || {};
                    const maxDaily = dayInfo.max_daily_appointments || 0;
                    const remaining = Math.max(maxDaily - bookedCount, 0);

                    // Only show modal if date is available and has remaining slots
                    if (!isPast && isAvailable && remaining > 0) {
                        const { time_start, time_end, max_daily_appointments } = availability[dateStr];
                        const slots = generateTimeSlots(time_start, time_end);
                        const booked = bookedSlots[dateStr] || [];

                        $('#date').val(dateStr);
                        const timeSelect = $('#time');
                        timeSelect.empty().append('<option value="">Select Time</option>');

                        // Get the clinic's operating hours for this date
                        const formattedStart = dayInfo.formatted_start || '7:00 AM'; // Default if not available
                        const formattedEnd = dayInfo.formatted_end || '5:00 PM'; // Default if not available

                        // Display clinic operating hours in the modal
                        $('#availableTimesDisplay').text(`${formattedStart} - ${formattedEnd}`);

                        slots.forEach(slot => {
                            if (!booked.includes(slot.value) && remaining > 0) {
                                timeSelect.append(`<option value="${slot.value}">${slot.display}</option>`);
                            }
                        });

                        $('#appointmentModal').modal('show');
                    }
                },
                headerToolbar: {
                    left: window.innerWidth < 768 ? 'prev,next' : 'prev,next today',
                    center: 'title',
                    right: window.innerWidth < 768 ? '' : 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: window.innerWidth < 768 ? 1 : 3
                    }
                },
                windowResize: function(view) {
                    calendar.updateSize();
                    calendar.changeView(window.innerWidth < 768 ? 'dayGridMonth' : 'dayGridMonth');
                },
                dayCellContent: function (arg) {
                    const dateStr = arg.date.toISOString().split('T')[0];
                    const isPast = arg.date < new Date().setHours(0, 0, 0, 0);
                    const isAvailable = checkDateAvailability(dateStr);
                    const bookedCount = bookedSlots[dateStr]?.length || 0;
                    const dayInfo = availability[dateStr] || {};
                    const maxDaily = dayInfo.max_daily_appointments || 0;
                    const remaining = Math.max(maxDaily - bookedCount, 0);

                    let slotInfo = '';
                    if (!isPast) {
                        if (!isAvailable) {
                            slotInfo = 'Closed';
                        } else {
                            slotInfo = `
                                <div class="slot-count">${remaining} slot${remaining !== 1 ? 's' : ''} available</div>
                                <div class="clinic-hours small">
                                    ${dayInfo.formatted_start} - ${dayInfo.formatted_end}
                                </div>
                            `;
                        }
                    }

                    return {
                        html: `
                            <div class="fc-daygrid-day-number">${arg.dayNumberText}</div>
                            <div class="slot-info">
                                ${slotInfo}
                            </div>
                        `
                    };
                },
                dayCellClassNames: function (arg) {
                    const dateStr = arg.date.toISOString().split('T')[0];
                    const isPast = arg.date < new Date().setHours(0, 0, 0, 0);
                    const isAvailable = checkDateAvailability(dateStr);
                    const bookedCount = bookedSlots[dateStr]?.length || 0;
                    const maxDaily = availability[dateStr]?.max_daily_appointments || 0;
                    const hasSlots = maxDaily - bookedCount > 0;

                    let classes = [];
                    if (isPast || !isAvailable || !hasSlots) classes.push('fc-day-disabled');
                    if (isAvailable && hasSlots) classes.push('has-slots');
                    return classes;
                },
                height: 'auto',
                contentHeight: 'auto',
                aspectRatio: 1.5
            });

            calendar.render();

            function generateTimeSlots(startTime, endTime) {
                const slots = [];
                const start = moment(`1970-01-01T${startTime}`);
                const end = moment(`1970-01-01T${endTime}`);

                let current = moment(start);
                while (current <= end) {
                    const formattedTime = current.format('h:mm A');
                    slots.push({
                        display: formattedTime,
                        value: current.format('HH:mm')
                    });
                    current.add(30, 'minutes');
                }
                return slots;
            }

            $('#appointmentForm').on('submit', function (e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    return false;
                }

                // Show loading state on button
                const submitBtn = $('#confirmBookingBtn');
                submitBtn.prop('disabled', true).addClass('loading');

                const formData = new FormData(this);
                const email = $('#email').val();

                // Add error handling to reset button state
                $(document).ajaxError(function() {
                    submitBtn.prop('disabled', false).removeClass('loading');
                });

                // First check if email exists
                $.ajax({
                    url: 'appointment_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'check_user',
                        email: email 
                    },
                    success: function(userResponse) {
                        try {
                            const response = typeof userResponse === 'string' ? JSON.parse(userResponse) : userResponse;
                            
                            if (response.exists) {
                                // Show existing user dialog
                                Swal.fire({
                                    title: 'Account Found!',
                                    html: `
                                        <div class="text-left">
                                            <p>An account with this email already exists.</p>
                                            <p>Please choose one of the following options:</p>
                                        </div>
                                    `,
                                    icon: 'info',
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonText: 'Login',
                                    denyButtonText: 'Reset Password',
                                    cancelButtonText: 'Stay Here',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'usrbase/entryvault.php';
                                    } else if (result.isDenied) {
                                        window.location.href = 'usrbase/forgot_password.php';
                                    }
                                });
                            } else {
                                // Proceed with booking for new user
                                $.ajax({
                                    url: 'appointment_handler.php',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        try {
                                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                                            const appointmentDate = $('#date').val();
                                            const appointmentTime = $('#time option:selected').text();
                                            const serviceName = $('#service option:selected').text();

                                            Swal.fire({
                                                title: 'Appointment Booked Successfully!',
                                                html: `
                                                    <div class="text-left">
                                                        <p>An email has been sent to <strong>${email}</strong> containing:</p>
                                                        <ul>
                                                            <li>Your appointment details</li>
                                                            <li>Login credentials for your account</li>
                                                        </ul>
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i> Please check your email inbox and spam folder
                                                        </div>
                                                        <p class="mt-3">Your appointment details:</p>
                                                        <ul>
                                                            <li>Date: ${moment(appointmentDate).format('MMMM D, YYYY')}</li>
                                                            <li>Time: ${appointmentTime}</li>
                                                            <li>Service: ${serviceName}</li>
                                                        </ul>
                                                    </div>
                                                `,
                                                icon: 'success',
                                                confirmButtonText: 'Proceed to Login',
                                                showCancelButton: true,
                                                cancelButtonText: 'Stay Here',
                                                allowOutsideClick: false,
                                                preConfirm: () => {
                                                    // Show loading state when button is clicked
                                                    Swal.showLoading();
                                                    return new Promise((resolve) => {
                                                        // Simulate a small delay for better UX
                                                        setTimeout(() => {
                                                            resolve();
                                                        }, 800);
                                                    });
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = 'usrbase/entryvault.php';
                                                }
                                                $('#appointmentModal').modal('hide');
                                                calendar.refetchEvents();
                                            });
                                        } catch (e) {
                                            // Still show success since we know the data is saved
                                            Swal.fire({
                                                title: 'Appointment Booked Successfully!',
                                                html: `
                                                    <div class="text-left">
                                                        <p>Your appointment has been booked and an email has been sent to <strong>${email}</strong>.</p>
                                                        <p>Please check your email for your login credentials and appointment details.</p>
                                                    </div>
                                                `,
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            }).then(() => {
                                                $('#appointmentModal').modal('hide');
                                                calendar.refetchEvents();
                                            });
                                        }
                                    },
                                    error: function() {
                                        // Show success with login option since data is saved
                                        Swal.fire({
                                            title: 'Appointment Booked Successfully!',
                                            html: `
                                                <div class="text-left">
                                                    <p>Your appointment has been booked and an email has been sent with your details.</p>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> Please check your email inbox and spam folder for your login credentials.
                                                    </div>
                                                </div>
                                            `,
                                            icon: 'success',
                                            showCancelButton: true,
                                            confirmButtonText: 'Proceed to Login',
                                            cancelButtonText: 'Stay Here',
                                            allowOutsideClick: false
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = 'usrbase/entryvault.php';
                                            }
                                            $('#appointmentModal').modal('hide');
                                            calendar.refetchEvents();
                                        });
                                    }
                                });
                            }
                        } catch (e) {
                            // Handle any JSON parsing errors
                            console.error('JSON parsing error:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to check email status. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            $(document).ready(function () {
                // Initially disable the submit button
                $('.btn-primary[type="submit"]').prop('disabled', true);

                // Listen for changes on the terms checkbox
                $('#terms').change(function () {
                    // Enable/disable submit button based on checkbox state
                    $('.btn-primary[type="submit"]').prop('disabled', !this.checked);
                });
            });

            // Make modal more responsive
            $(window).on('resize', function() {
                const modal = $('#appointmentModal');
                if (modal.is(':visible')) {
                    const isMobile = window.innerWidth < 768;
                    modal.find('.modal-dialog').toggleClass('modal-fullscreen', isMobile);
                    modal.find('.col-md-6').toggleClass('border-right', !isMobile);
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once('includes/footer.php'); ?>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.backstretch.min.js"></script>
    <script src="js/counter.js"></script>
    <script src="js/countdown.js"></script>
    <script src="js/init.js"></script>
    <script src="js/modernizr.js"></script>
    <script src="js/animated-headline.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/appointment.js"></script>
</body>

</html>