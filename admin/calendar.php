<?php
include_once('../database/db_connection.php');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');

// Remove or comment out the old clinic_settings query
/*
$settingsQuery = "SELECT max_daily_appointments FROM clinic_settings LIMIT 1";
$result = mysqli_query($conn, $settingsQuery);
$max_daily = ($result && $row = mysqli_fetch_assoc($result)) ? $row['max_daily_appointments'] : 20;
*/

// Fetch available dates and their max_daily_appointments from availability_tb
$availabilityData = [];
$maxAppointments = [];
$resultAvail = mysqli_query($conn, "SELECT available_date, max_daily_appointments FROM availability_tb");
while ($rowAvail = mysqli_fetch_assoc($resultAvail)) {
    $availabilityData[] = $rowAvail['available_date'];
    $maxAppointments[$rowAvail['available_date']] = $rowAvail['max_daily_appointments'];
}

// Remove duplicate query and use the data we already fetched
$availabilityDates = $availabilityData; // We already have this data

// For debugging purposes
if (empty($availabilityDates)) {
    error_log('No available dates found in availability_tb');
}

// Update the appointment counts query to exclude completed appointments
$appointmentCounts = [];
$result = mysqli_query($conn, 
    "SELECT appointment_date, COUNT(*) AS count 
     FROM appointments 
     WHERE status IN ('confirmed', 'pending', 'cancelled') 
     AND status NOT IN ('completed', 'deleted')  
     GROUP BY appointment_date");
while ($row = mysqli_fetch_assoc($result)) {
    $appointmentCounts[$row['appointment_date']] = $row['count'];
}

// Modify the appointment fetch query to exclude completed appointments
$sched_arr = [];
$query = "SELECT 
    a.appointment_id AS id, 
    u.fullname AS patient_name, 
    a.appointment_date, 
    a.appointment_time, 
    s.service_name AS service,
    TIME_FORMAT(a.appointment_time, '%h:%i %p') AS formatted_time,
    a.status
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.user_id
    INNER JOIN services s ON a.service_id = s.service_id
    WHERE a.status NOT IN ('completed', 'deleted')
    ORDER BY a.appointment_date, a.appointment_time";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sched_arr[] = $row;
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SB Admin 2 Template -->
<link href="css/sb-admin-2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">


    <style>
        body {
            overflow: auto;
            background: #f8f9fc;
            margin: 0;
            padding: 0;
        }
        
        .container-fluid {
            padding: 5px;
            margin-left: 10px; /* Keep sidebar width */
            width: calc(100% - 10px);
        }

        /* Remove unnecessary container */
        .container {
            padding: 0;
            margin: 0;
            max-width: none;
            width: 100%;
        }

        .card {
            border: none;
            margin: 0;
            height: calc(100vh - 10px); /* Reduced top space */
        }

        .card-body {
            padding: 5px; /* Reduced padding */
            height: 100%;
        }

        #calendar-container {
            height: 100%;
            padding: 5px; /* Reduced padding */
            background: #ffffff;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
        }

        #calendar {
            height: 100% !important;
            width: 100% !important;
            margin-left: 0 !important; /* Remove left margin */
        }

        /* Calendar Header Styling */
        .fc-header-toolbar {
            padding: 5px !important;
            margin: 0 !important;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
        }

        /* Make day cells larger */
        .fc-daygrid-day {
            min-height: 150px !important;
        }

        .fc-daygrid-day-frame {
            min-height: 150px !important;
        }

        /* Keep sidebar visible */
        #accordionSidebar {
            display: block;
            width: 224px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            z-index: 1000;
        }

        /* Adjust main content area */
        #content-wrapper {
            margin-left: 224px;
            width: calc(100% - 224px);
            padding-left: 0; /* Remove left padding */
        }

        /* Adjust the page title spacing */
        .d-sm-flex {
            padding: 5px;
            margin-bottom: 5px !important;
        }

        /* Calendar Button Styling */
        .fc-button {
            background-color: #4e73df !important;
            border-color: #4e73df !important;
            border-radius: 6px !important;
            padding: 8px 16px !important;
            transition: all 0.3s ease !important;
        }

        .fc-button:hover {
            background-color: #375abd !important;
            box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3) !important;
        }

        .fc-button-active {
            background-color: #375abd !important;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125) !important;
        }

        /* Calendar Grid Styling */
        .fc-daygrid-day {
            transition: all 0.2s ease;
        }

        .fc-daygrid-day:hover {
            background-color: #f8f9ff !important;
        }

        .fc-daygrid-day-number {
            font-size: 1.1rem !important;
            font-weight: 600 !important;
            color: #2c3e50;
            padding: 5px !important;
            margin: 2px 0 !important;
        }

        /* Event Styling */
        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            margin: 2px 0 !important;
            font-size: 0.85rem !important;
            transition: all 0.3s ease;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Slot Info Styling */
        .slot-info {
            color: #636e72;
            text-align: center;
            width: 100%;
            padding: 6px;
            font-size: 0.85rem;
            margin-top: 5px;
            background: #f8f9fa;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .fc-day-today {
            background-color: #f8f9ff !important;
        }

        .fc-day-today .slot-info {
            background-color: #e3e8ff;
            color: #4e73df;
            font-weight: 500;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5em 1.2em;
            font-size: 0.875em;
            font-weight: 500;
            text-transform: capitalize;
            border-radius: 20px;
        }

        .badge-warning {
            background-color: #ffa500;
            color: #fff;
        }

        .badge-success {
            background-color: #2ecc71;
            color: #fff;
        }

        .badge-danger {
            background-color: #e74c3c;
            color: #fff;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background-color: #4e73df;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fc;
        }

        /* Add these new styles */
        .fc-event-custom {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .fc-event-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .slot-info {
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
            border-radius: 8px;
            padding: 8px;
            margin: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .slot-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .slot-count {
            font-size: 1.2rem;
            font-weight: 600;
            color: #4e73df;
        }

        .slot-text {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .closed-indicator {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
        }

        .appointment-details {
            position: relative;
        }

        .status-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            border-radius: 4px 4px 0 0;
        }

        .detail-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-item i {
            color: #4e73df;
            width: 20px;
            text-align: center;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
        }

        .btn-close-modal {
            background: #4e73df;
            color: white;
            border-radius: 6px;
            padding: 8px 20px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-close-modal:hover {
            background: #375abd;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Appointment Calendar</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <div id="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<div id="uniModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Replace the old maxDaily constant with maxAppointments object
        const maxAppointments = <?php echo json_encode($maxAppointments); ?>;
        const appointmentCounts = <?php echo json_encode($appointmentCounts); ?>;
        const availabilityDates = <?php echo json_encode($availabilityData); ?>;

        const calendarEl = document.getElementById('calendar');
        const scheds = <?php echo json_encode($sched_arr); ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridDay'
            },
            themeSystem: 'bootstrap',
            themeName: 'custom',
            customButtons: {
                custom1: {
                    text: 'Add Appointment',
                    click: function() {
                        // Add your custom button functionality here
                    }
                }
            },
            initialView: 'dayGridMonth',
            timeZone: 'Asia/Manila',  // Set Philippine timezone
            events: scheds.map(sched => ({
                id: sched.id,
                title: `${sched.patient_name} (${sched.formatted_time})`,
                start: `${sched.appointment_date}T${sched.appointment_time}`,
                backgroundColor: sched.status === "pending" ? "#FFD700" : 
                                sched.status === "cancelled" ? "#FF6B6B" : "#6BCB77",
                borderColor: sched.status === "pending" ? "#FFC107" : 
                            sched.status === "cancelled" ? "#FF4757" : "#4CAF50",
                textColor: '#fff',
                className: 'fc-event-custom',
                extendedProps: {
                    status: sched.status,
                    service: sched.service
                }
            })),
            validRange: {
                start: moment().format("YYYY-MM-DD")
            },
            dayCellContent: function(args) {
                const dateStr = args.date.toISOString().split('T')[0];
                const isPast = args.date < new Date().setHours(0,0,0,0);
                const isOpen = availabilityDates.includes(dateStr);
                
                // Get max appointments for this specific date
                const maxDaily = maxAppointments[dateStr] || 0;
                const count = appointmentCounts[dateStr] || 0;
                const available = isOpen ? Math.max(maxDaily - count, 0) : 0;

                return {
                    html: `
                        <div class="fc-daygrid-day-number">${args.dayNumberText}</div>
                        <div class="slot-info ${isPast ? 'past-day' : ''}">
                            ${isPast ? '' : (isOpen ? `
                                <div class="slot-indicator">
                                    <span class="slot-count">${available}</span>
                                    <span class="slot-text">slot${available !== 1 ? 's' : ''} available</span>
                                </div>
                            ` : '<div class="closed-indicator">Closed</div>')}
                        </div>
                    `
                };
            },
            eventClick: function(info) {
                const event = info.event;
                const statusColor = event.extendedProps.status === 'pending' ? '#FFD700' : 
                                 event.extendedProps.status === 'cancelled' ? '#FF6B6B' : '#6BCB77';
                
                const details = `
                    <div class="p-3 appointment-details">
                        <div class="status-bar" style="background: ${statusColor}"></div>
                        <h5 class="text-primary mb-3">Appointment Details</h5>
                        <div class="detail-card">
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span>${event.title.split('(')[0]}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>${moment(event.start).format('MMMM D, YYYY')}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>${moment(event.start).format('h:mm A')}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-stethoscope"></i>
                                <span>${event.extendedProps.service}</span>
                            </div>
                            <div class="detail-item status">
                                <i class="fas fa-info-circle"></i>
                                <span class="status-badge" style="background: ${statusColor}">
                                    ${event.extendedProps.status}
                                </span>
                            </div>
                        </div>
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-close-modal" onclick="closeModal()">
                                Close
                            </button>
                        </div>
                    </div>
                `;
                
                $('#uniModal .modal-title').html('Appointment Information');
                $('#uniModal .modal-body').html(details);
                $('#uniModal').modal('show');
            },
        });
        calendar.render();
    });

    function uni_modal(title, url) {
        $('#uniModal .modal-title').text(title);
        $('#uniModal .modal-body').load(url, function() {
            $('#uniModal').modal('show');
        });
    }

    function closeModal() {
        $('#uniModal').modal('hide');
    }
</script>


<?php include_once('includes/footer.php'); ?>
</body>
</html>