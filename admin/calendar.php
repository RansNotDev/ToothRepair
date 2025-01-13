    <?php 
    include_once('includes/header.php');
    include_once('includes/sidebar.php');
    include_once('includes/topbar.php');
    ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Appointment Calendar</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Book for <span id="selectedDate"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Select Slot Time</h6>
                    <div id="timeSlots" class="row">
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="../assets/Assetscalendar/fullcalendar/main.css">

    <style>
        /* Calendar cell styling */
        .fc .fc-day-today {
            background-color: rgba(206, 212, 218, 0.3) !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background-color: #4e73df;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fc .fc-daygrid-day-frame {
            min-height: 120px !important;
            height: 120px !important;
            border: 0.2px solid #ddd !important;
            background: white;
            position: relative;
            padding: 5% !important;
        }

        /* Large date number styling */
        .fc .fc-daygrid-day-top {
            height: 30% !important;
            display: flex;
            justify-content: flex-end;
        }

        .fc .fc-daygrid-day-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            padding: 0 !important;
        }

        /* Status indicators container */
        .status-container {
            display: flex;
            justify-content: center;
            padding: 5px;
            margin-top: 5px;
        }
        .status-box {
            position: absolute;
            left: 5%;
            top: 35%;
            width: 90%;
            height: 60%;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 0.85em;
        }

        /* Status pill styling */
        .status-pill {
            padding: 4px 12px;
            border-radius: 15px;
            color: white;
            font-size: 0.85em;
            text-align: center;
            width: fit-content;
            margin: 0 auto;
        }

        /* Status colors */
        .status-available {
            background-color: #28a745;
        }

        .status-no-slots {
            background-color: #007bff;
        }

        .status-holiday {
            background-color: #6f42c1;
        }

        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 5px;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }


        .status-available { background-color: #28a745; }
        .status-no-slots { background-color: #007bff; }
        .status-holiday { background-color: #6f42c1; }

        .fc-daygrid-day-events { display: none !important; }

    /* Responsive Styles */
    @media (max-width: 768px) { 
        .fc .fc-daygrid-day-frame {
            min-height: 80px !important; 
            height: 80px !important;
        }
        .fc .fc-daygrid-day-number {
            font-size: 1em; 
        }
        .status-box {
            font-size: 0.7em;
        }
        .notification-badge {
            top: 5px; 
            right: 5px; 
            width: 18px; 
            height: 18px; 
            font-size: 10px; 
        }
    }

    @media (max-width: 576px) { 
        .fc .fc-daygrid-day-frame {
            min-height: 60px !important;
            height: 60px !important;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.8em;
        }
        .status-box {
            font-size: 0.6em; 
        }
        .notification-badge {
            width: 15px; 
            height: 15px;
            font-size: 8px; 
        }
        .fc-header-toolbar .fc-toolbar-chunk {
            font-size: 12px; /* Reduce font size of header buttons */
        }
    }

    @media (max-width: 360px) { 
        .fc .fc-daygrid-day-frame {
            min-height: 50px !important;
            height: 50px !important;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.7em;
        }
        .status-box {
            font-size: 0.5em; 
            top: 30%; /* Adjust position to avoid overlap */
        }
        .notification-badge {
            width: 12px; 
            height: 12px;
            font-size: 6px; 
        }
        .fc-header-toolbar {
            flex-wrap: wrap; /* Allow buttons to wrap */
            justify-content: center; /* Center the buttons */
            font-size: 10px; 
            flex-direction: column; 
            align-items: center;/* Reduce font size of header */
        }
        .fc-header-toolbar .fc-toolbar-chunk {
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            align-items: center;
            flex: 1 0 auto; /* Allow buttons to grow and shrink */
            margin: 5px 0;  /* Add spacing between button groups */
            
        }
        .fc-header-toolbar button {
            margin-bottom: 5px;
            padding: 5px 10px; /* Reduce button padding */
            font-size: 10px; / /* Add spacing between buttons */
        }
    }
    </style>


    <?php include_once('includes/footer.php'); ?>

    <script src="../assets/Assetscalendar/moment/moment.min.js"></script>
    <script src="../assets/Assetscalendar/fullcalendar/main.js"></script>
    <script src="../assets/Assetscalendar/sweetalert2/sweetalert2.all.min.js"></script> 

    <script>
    $(function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        themeSystem: 'bootstrap',
        editable: true,
        droppable: false,
        aspectRatio: 1.8,
        contentHeight: 'auto',
        dayMaxEventRows: true,
        initialView: 'dayGridMonth',

        datesSet: function (info) {
            // This function is called when the calendar view changes (month, week, day)
            calendar.refetchEvents(); // Refresh events when the view changes

            // Reset click counts for all navigation buttons
            $('.fc-prev-button, .fc-next-button, .fc-today-button, .fc-dayGridMonth-button, .fc-timeGridWeek-button, .fc-timeGridDay-button')
                .data('clickCount', 0)
                .prop('disabled', false);
        },

        dayCellDidMount: function (arg) {
            const date = arg.date;
            const day = date.getDate();
            const dayOfWeek = date.getDay(); // Get the day of the week (0-6)
            const cellContent = arg.el.querySelector('.fc-daygrid-day-frame');

            const statusBox = document.createElement('div');
            statusBox.className = 'status-box';

            // Determine the color based on the day of the week
            let color = '#28a745'; // Default to green
            if (dayOfWeek >= 4 && dayOfWeek <= 5) { // Thursday to Friday
                color = '#007bff'; // Blue
            } else if (dayOfWeek === 0 || dayOfWeek === 6) { // Saturday and Sunday
                color = '#6f42c1'; // Violet
            }

            statusBox.style.backgroundColor = color;

            // Add notification badge and text based on specific days of the week
            if (dayOfWeek === 2) { // If it's Wednesday (dayOfWeek 2)
                statusBox.innerHTML = `
                    <div class="notification-badge">2</div>
                    <span>Available</span>
                `;
            } else {
                statusBox.innerHTML = '<span>Available</span>';
            }

            if (statusBox.innerHTML) {
                cellContent.appendChild(statusBox);
            }
        },


        eventClick: function (info) {
            alert('Event: ' + info.event.title);
        },

        dateClick: function (info) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (info.date < today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'You cannot select a date in the past!',
                });
            } else {
                $('#selectedDate').text(info.dateStr);

                // Reset form fields and disable submit button
                $('#appointmentType').prop('disabled', true);
                $('#appointmentForm button[type="submit"]').prop('disabled', true);
                $('#selectedTimeSlot').val('');

                // Generate time slots based on day of the week
                var timeSlots = [];
                var dayOfWeek = info.date.getDay(); // 0 for Sunday, 1 for Monday, etc.

                if (dayOfWeek >= 1 && dayOfWeek <= 5) { // Monday to Friday
                    for (var hour = 8; hour <= 16; hour++) {
                        var startHour = hour;
                        var endHour = (hour + 1);
                        var startAMPM = (startHour < 12) ? 'AM' : 'PM';
                        var endAMPM = (endHour < 12) ? 'AM' : 'PM';
                        // Adjust hour display for 12 PM and beyond
                        startHour = (startHour > 12) ? startHour - 12 : startHour;
                        endHour = (endHour > 12) ? endHour - 12 : endHour;
                        timeSlots.push(startHour + ':00 ' + startAMPM + '-' + endHour + ':00 ' + endAMPM);
                    }
                } else { // Saturday and Sunday
                    for (var hour = 10; hour <= 15; hour++) {
                        var startHour = hour;
                        var endHour = (hour + 1);
                        var startAMPM = (startHour < 12) ? 'AM' : 'PM';
                        var endAMPM = (endHour < 12) ? 'AM' : 'PM';
                        // Adjust hour display for 12 PM and beyond
                        startHour = (startHour > 12) ? startHour - 12 : startHour;
                        endHour = (endHour > 12) ? endHour - 12 : endHour;
                        timeSlots.push(startHour + ':00 ' + startAMPM + '-' + endHour + ':00 ' + endAMPM);
                    }
                }

                var slotsHTML = '';
                timeSlots.forEach(function (slot) {
                    slotsHTML += `
                    <div class="col-md-4 col-6 mb-2">
                        <button class="btn btn-success btn-block time-slot-btn" data-time="${slot}">
                        ${slot}
                        </button>
                    </div>`;
                });

                // Populate time slots
                $('#timeSlots').html(slotsHTML);

                // Attach click handlers to time slot buttons
                $('.time-slot-btn').click(function () {
                    var selectedTime = $(this).data('time');
                    $('#selectedTimeSlot').val(selectedTime);

                    // Check time slot availability (You'll likely need to adjust this part)
                    $.ajax({
                        url: 'check_time_slot.php',
                        type: 'GET',
                        data: {
                            date: $('#selectedDate').text(),
                            time: selectedTime
                        },
                        success: function (response) {
                            if (response === 'available') {
                                // Enable appointment type selection and submit button
                                $('#appointmentType').prop('disabled', false);
                                $('#appointmentForm button[type="submit"]').prop('disabled', false);
                            } else {
                                // If not available, disable form fields and show message
                                $('#appointmentType').prop('disabled', true);
                                $('#appointmentForm button[type="submit"]').prop('disabled', true);
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Occupied',
                                    text: 'This time slot is already occupied.',
                                });
                            }
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while checking time slot availability.',
                            });
                        }
                    });
                });

                // Show the modal
                $('#appointmentModal').modal('show');
            }
        }
    });

    calendar.render();

    // Handle form submission
    $('#appointmentForm').submit(function (event) {
        event.preventDefault();

        var selectedDate = $('#selectedDate').text();
        var appointmentType = $('#appointmentType').val();
        var selectedTimeSlot = $('#selectedTimeSlot').val();

        $.ajax({
            url: 'book_appointment.php',
            type: 'POST',
            data: {
                date: selectedDate,
                type: appointmentType,
                time: selectedTimeSlot
            },
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                    });
                    calendar.refetchEvents(); // Refresh the calendar
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: result.message,
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while booking the appointment.',
                });
            }
        });

        $('#appointmentModal').modal('hide');
    });


        // Prevent excessive clicking on navigation buttons
        $('.fc-prev-button, .fc-next-button, .fc-today-button, .fc-dayGridMonth-button, .fc-timeGridWeek-button, .fc-timeGridDay-button').click(function() {
            var $this = $(this);
            var clickCount = $this.data('clickCount') || 0;
            clickCount++;
            $this.data('clickCount', clickCount);

            if (clickCount > 5) {
                $this.prop('disabled', true);
                setTimeout(function() {
                    $this.prop('disabled', false);
                    $this.data('clickCount', 0);
                }, 1000); // Enable after 1 second
            }
        });

        $("#sidebarToggle, #sidebarToggleTop").on('click', function () {
        setTimeout(function () {
            calendar.updateSize();
        }, 300);
    });
});
    </script>