<?php
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

include_once('../database/db_connection.php');

// Get clinic settings
$max_daily = 20;
$settingsResult = mysqli_query($conn, "SELECT max_daily_appointments FROM clinic_settings LIMIT 1");
if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {
    $max_daily = mysqli_fetch_assoc($settingsResult)['max_daily_appointments'];
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

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="">
        <meta name="author" content="">

        <title>Contact | Tooth Repair Dental Clinic</title>

        <!-- CSS FILES -->        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">
        <link href="css/calendar-styles.css" rel="stylesheet">
        <link href="css/tooplate-clean-work.css" rel="stylesheet">
        <link href="css/custom-colors.css" rel="stylesheet"> <!-- Add this line -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
    

</style>
    </head>
    <body>
        <header class="site-header bg-primary">
            <div class="container">
                <div class="row">
                    
                    <div class="col-lg-12 col-12 d-flex flex-wrap">
                        <p class="d-flex me-4 mb-0">
                            <i class="bi-emoji-smile-fill"></i>
                            Tooth Repair Dental Clinic
                        </p>

                        <p class="d-flex d-lg-block d-md-block d-none me-4 mb-0">
                            <i class="bi-clock-fill me-2"></i>
                            <strong class="me-2">Mon - Fri</strong> 8:00 AM - 4:00 PM
                        </p>

                        <p class="site-header-icon-wrap text-white d-flex mb-0 ms-auto">
                            <i class="site-header-icon bi-whatsapp me-2"></i>

                            <a href="tel:+639123456789" class="text-white">
                                +63 912 345 6789
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </header>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="images/bubbles.png" class="logo img-fluid" alt="">

                    <span class="ms-2">Tooth Repair Dental Clinic</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>


        <!-- Navigation Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="about.php">About Us</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="services.php">Our Services</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link active" href="appointmentcalendar.php">Book An Appointment</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact</a>
                        </li>

                        <li class="nav-item ms-3">
                            <a class="nav-link custom-btn custom-border-btn custom-btn-bg-white btn text-white" href="#">Get started</a>
                        </li>
                    </ul>
                </div>
    </div>
</nav>

        <main>

            <section class="banner-section d-flex justify-content-center align-items-end">
                <div class="section-overlay"></div>

                <div class="container">
                    <div class="row">

    

                        <div class="col-lg-7 col-12">
                            <h1 class="text-white mb-lg-0">Appointment Calendar</h1>
                        </div>

                        <div class="col-lg-4 col-12 d-flex justify-content-lg-end align-items-center ms-auto">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>

                                    <li class="breadcrumb-item active" aria-current="page">Appointment Calendar</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                </div>
            </section>
        
            <section class="contact-section section-padding">
                <div class="container">
                    <div class="row justify-content-center">
                        
            <div class="col-lg-10 col-12">
                <div class="section-title text-center mb-5">
                    <h2>Book Your Appointment</h2>
                    <p class="text-muted">Select your preferred date and time</p>
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
                </div>
-->
                <div class="calendar-wrapper bg-white rounded-lg shadow-sm p-4">
                    <div id="calendar"></div>
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



                            
                        </div>
                    </div>
                </div>
            </section>


            
        </main>

        
        <section class="partners-section">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-12 col-12">
                <h4 class="partners-section-title bg-white shadow-lg">Trusted Partners in Dental Care</h4>
            </div>

            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/partners/colgate.png" class="partners-image img-fluid" alt="Colgate">
            </div>

            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/partners/oral-b.png" class="partners-image img-fluid" alt="Oral-B">
            </div>

            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/partners/sensodyne.png" class="partners-image img-fluid" alt="Sensodyne">
            </div>

            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/partners/philips-sonicare.png" class="partners-image img-fluid" alt="Philips Sonicare">
            </div>

            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/partners/3m-dental.png" class="partners-image img-fluid" alt="3M Dental">
            </div>
        </div>
    </div>
</section>

        </main>

        <footer class="site-footer">
            <div class="container">
                <div class="row">

                    <div class="col-lg-12 col-12 d-flex align-items-center mb-4 pb-2">
                        <div>
                            <img src="images/bubbles.png" class="logo img-fluid" alt="">
                        </div>

                        <ul class="footer-menu d-flex flex-wrap ms-5">
                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">About Us</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Blog</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Reviews</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-5 col-12 mb-4 mb-lg-0">
                        <h5 class="site-footer-title mb-3">Our Services</h5>

                        <ul class="footer-menu">
                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                    Tooth Extraction
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                    Tooth Filling
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                    Dental Braces
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                    Dental Crowns
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Gum Treatment
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Teeth Whitening
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Dental Implants
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Root Canal
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Dental Veneers
                                </a>
                            </li>

                            <li class="footer-menu-item">
                                <a href="#" class="footer-menu-link">
                                    <i class="bi-chevron
                                    -double-right footer-menu-link-icon me-2"></i>
                                    Dental Bridges
                                </a>
                            </li>

                        </ul>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 mb-4 mb-lg-0 mb-md-0">
                        <h5 class="site-footer-title mb-3">Clinic Location</h5>

                        <p class="text-white d-flex mt-3 mb-2">
                            <i class="bi-geo-alt-fill me-2"></i>
                            Poblacion, Malasiqui, Philippines
                        </p>

                        <p class="text-white d-flex mb-2">
                            <i class="bi-telephone-fill me-2"></i>

                            <a href="tel: 110-220-9800" class="site-footer-link">
                                09123456789
                            </a>
                        </p>

                        <p class="text-white d-flex">
                            <i class="bi-envelope-fill me-2"></i>

                            <a href="mailto:info@company.com" class="site-footer-link">
                                Toothrepairclinic@gmail.com
                            </a>
                        </p>

                        <ul class="social-icon mt-4">
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link button button--skoll">
                                    <span></span>
                                    <span class="bi-twitter"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link button button--skoll">
                                    <span></span>
                                    <span class="bi-facebook"></span>
                                </a>
                            </li>

                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link button button--skoll">
                                    <span></span>
                                    <span class="bi-instagram"></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6 mt-3 mt-lg-0 mt-md-0">
                        <div class="featured-block">
                            <h5 class="text- mb-3">Service Hours</h5>

                            <strong class="d-block text-white mb-1">Mon - Fri</strong>

                            <p class="text-white mb-3">8:00 AM - 5:30 PM</p>

                            <strong class="d-block text-white mb-1">Sat</strong>

                            <p class="text-white mb-0">6:00 AM - 2:30 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="site-footer-bottom">
                <div class="container">
                    <div class="row">

                        <div class="col-lg-6 col-12">
                            <p class="copyright-text mb-0">Copyright © <?php echo date("Y") ?> Tooth Repair Dental Clinic</p>
                        </div>
                        
                        <div class="col-lg-6 col-12 text-end">
                            <p class="copyright-text mb-0">
                            // Designed by <a href="#" target="_parent">Not Just Rans</a> //</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
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
    const maxDaily = <?php echo $max_daily; ?>;
    const availability = <?php echo json_encode($availability); ?>;
    // Get calendar element
    const calendarEl = document.getElementById('calendar');
    
    // Add this function before the calendar initialization
    function checkDateAvailability(dateStr) {
        // Check if the date exists in availability array
        return availability.hasOwnProperty(dateStr);
    }

    // Initialize calendar with defined variables
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        validRange: { start: new Date() },
        timeZone: 'Asia/Manila',
        dateClick: function (info) {
            const dateStr = info.dateStr;
            const isPast = info.date < new Date().setHours(0, 0, 0, 0);
            const isAvailable = checkDateAvailability(dateStr);

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
                const isAvailable = checkDateAvailability(dateStr); // Add this line
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
                const isAvailable = checkDateAvailability(dateStr);
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
<?php include_once('../includes/footer.php'); ?>

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

    </body>
</html>
