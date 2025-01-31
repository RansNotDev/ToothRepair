<?php
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
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tooth Repair Clinic - Dashboard</title>

    <link href="assets/Assetscalendar/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/Assetscalendar/fullcalendar/main.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="admin/css/sb-admin-2.min.css" rel="stylesheet">
    <!--cdn online bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>

<body>

</body>

</html>
<style>
    .has-slots {
        background-color: rgba(78, 115, 223, 0.1) !important;
    }

    .fc-day-disabled .slot-info {
        color: #718096 !important;
    }

    .has-slots .fc-daygrid-day-number {
        font-weight: bold;
        color: #2b6cb0;
    }

    .fc-daygrid-day-frame {
        cursor: pointer !important;
    }

    .fc-day-disabled {
        background-color: #f8f9fc;
        opacity: 0.6;
    }

    .slot-info {
        color: #4a5568;
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
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointment Calendar</h1>
    </div>
    <hr>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Appointment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bookingForm">
                    <div class="form-group">
                        <label>Selected Date:</label>
                        <input type="text" id="selectedDate" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Available Time Slots:</label>
                        <div id="timeSlots" class="d-flex flex-wrap"></div>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" disabled required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" disabled required>
                    </div>

                    <div class="form-group">
                        <label>Service</label>
                        <select name="service" class="form-control" disabled required>
                            <option value="">Select Service</option>
                            <?php
                            $services = mysqli_query($conn, "SELECT * FROM services");
                            while ($service = mysqli_fetch_assoc($services)) {
                                echo "<option value='{$service['service_id']}'>{$service['service_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <input type="hidden" name="selectedTime">
                    <button type="submit" class="btn btn-primary" disabled>Book Appointment</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const maxDaily = <?php echo $max_daily; ?>;
        const calendarEl = document.getElementById('calendar');
        const closures = <?php echo json_encode($closures); ?>;
        const availability = <?php echo json_encode($availability); ?>;
        const bookedSlots = <?php echo json_encode($bookedSlots); ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            validRange: { start: new Date() },
            dateClick: function (info) {
                const dateStr = info.dateStr;
                const isPast = info.date < new Date().setHours(0, 0, 0, 0);
                const isAvailable = availability[dateStr] && !closures.includes(dateStr);

                if (!isPast && isAvailable) {
                    $('#selectedDate').val(dateStr);
                    showTimeSlots(dateStr);
                    $('#bookingModal').modal('show');
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

        function showTimeSlots(dateStr) {


            const { time_start, time_end } = availability[dateStr];
            const slots = generateTimeSlots(time_start, time_end);
            const booked = bookedSlots[dateStr] || [];
            const bookedCount = booked.length;
            const remaining = Math.max(maxDaily - bookedCount, 0);

            const slotHtml = slots.map(time => {
                const isBooked = booked.includes(time) || remaining <= 0;
                return `
        <div class="time-slot ${isBooked ? 'booked' : 'available'}" 
             data-time="${time}"
             ${isBooked ? 'disabled' : ''}>
            ${time}
        </div>
            `;
            }).join('');

            $('#timeSlots').html(slotHtml);
        }

        function generateTimeSlots(startTime, endTime) {
            const slots = [];
            const start = new Date(`1970-01-01T${startTime}`);  // Added backticks
            const end = new Date(`1970-01-01T${endTime}`);

            let current = new Date(start);
            while (current <= end) {
                slots.push(current.toTimeString().substr(0, 5));
                current.setMinutes(current.getMinutes() + 30);
            }
            return slots;
        }

        // Handle time slot selection
        $('#timeSlots').on('click', '.time-slot.available', function () {
            $('.time-slot').removeClass('selected');
            $(this).addClass('selected');
            const selectedTime = $(this).data('time');
            $('input[name="selectedTime"]').val(selectedTime);
            $('#bookingForm input, #bookingForm select, #bookingForm button').prop('disabled', false);
        });

        // Handle form submission
        $('#bookingForm').submit(function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: 'book_appointment.php',
                method: 'POST',
                data: formData + '&date=' + $('#selectedDate').val(),
                success: function (response) {
                    $('#bookingModal').modal('hide');
                    calendar.refetchEvents(); // Remove this line
                    calendar.render();        // Add this instead
                    Swal.fire('Success!', 'Appointment booked successfully!', 'success');
                },
                error: function () {
                    alert('Error booking appointment. Please try again.');
                }
            });
        });
    });
</script>

<?php include_once('includes/footer.php'); ?>