<?php
include_once('../database/db_connection.php');
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');

// Get current settings
$settingsQuery = "SELECT max_daily_appointments FROM clinic_settings LIMIT 1";
$result = mysqli_query($conn, $settingsQuery);
$max_daily = ($result && $row = mysqli_fetch_assoc($result)) ? $row['max_daily_appointments'] : 20;

// Get closure dates
$closures = [];
$result = mysqli_query($conn, "SELECT closure_date FROM closures");
while ($row = mysqli_fetch_assoc($result)) {
    $closures[] = $row['closure_date'];
}

// Update appointment counts query - exclude deleted status
$appointmentCounts = [];
$result = mysqli_query($conn, 
    "SELECT appointment_date, COUNT(*) AS count 
     FROM appointments 
     WHERE status IN ('confirmed', 'pending') 
     AND status != 'deleted'  
     GROUP BY appointment_date");
while ($row = mysqli_fetch_assoc($result)) {
    $appointmentCounts[$row['appointment_date']] = $row['count'];
}

// Update main appointment fetch query
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
    WHERE a.status != 'deleted'
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

<style>
    #calendar-container {
        height: 600px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        background: #f8f9fc;
    }

    .fc-event {
        cursor: pointer;
        margin: 2px 0;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .fc-event:hover {
        background-color: #4e73df !important;
        color: white !important;
        opacity: 0.9;
    }

    .slot-info {
        color: #4a5568;
        text-align: center;
        width: 100%;
        padding: 5px;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .fc-daygrid-day-frame {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .fc-day-past .slot-info {
        display: none;
    }

    .text-warning {
        color: #ffa500 !important;
    }

    .fc-daygrid-day-number {
        font-size: 1.2rem;
        font-weight: bold;
        margin: 5px 0;
    }

    .badge {
        padding: 0.5em 1em;
        font-size: 0.875em;
        text-transform: capitalize;
    }
    .badge-warning {
        background-color: #ffa500;
        color: #fff;
    }
    .badge-success {
        background-color: #28a745;
        color: #fff;
    }
</style>

<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const maxDaily = <?php echo $max_daily; ?>;
        const closures = <?php echo json_encode($closures); ?>;
        const appointmentCounts = <?php echo json_encode($appointmentCounts); ?>;
        
        const calendarEl = document.getElementById('calendar');
        const scheds = <?php echo json_encode($sched_arr); ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridDay'
            },
            themeSystem: 'bootstrap',
            initialView: 'dayGridMonth',
            timeZone: 'Asia/Manila',  // Set Philippine timezone
            events: scheds.map(sched => ({
                id: sched.id,
                title: `${sched.patient_name} (${sched.formatted_time})
                        ${sched.status === "pending" ? "⏳" : "✓"}`,
                start: `${sched.appointment_date}T${sched.appointment_time}`,
                backgroundColor: sched.status === "pending" ? "#ffa500" : "#28a745",
                borderColor: sched.status === "pending" ? "#ff8c00" : "#218838",
                textColor: '#fff',
                extendedProps: {
                    status: sched.status,
                    service: sched.service
                }
            })),
            eventDidMount: function(info) {
                info.el.style.fontSize = '0.85em';
                info.el.style.padding = '4px';
                info.el.style.margin = '2px';
            },
            validRange: {
                start: moment().format("YYYY-MM-DD")
            },
            dayCellContent: function(args) {
                const dateStr = args.date.toISOString().split('T')[0];
                const isPast = args.date < new Date().setHours(0,0,0,0);
                const isClosed = closures.includes(dateStr);
                
                // Get appointment counts for the day
                const count = appointmentCounts[dateStr] || 0;
                const available = isClosed ? 0 : Math.max(maxDaily - count, 0);

                return {
                    html: `
                        <div class="fc-daygrid-day-number">${args.dayNumberText}</div>
                        <div class="slot-info">
                            ${isPast ? '' : (isClosed ? 'Closed' : 
                                `${available} slot${available !== 1 ? 's' : ''} available`)}
                        </div>
                    `
                };
            },
            eventClick: function(info) {
                const event = info.event;
                const details = `
                    <div class="p-3">
                        <h5 class="text-primary mb-3">Appointment Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Patient Name</th>
                                <td>${event.title.split('(')[0]}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>${moment(event.start).format('MMMM D, YYYY')}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>${moment(event.start).format('h:mm A')}</td>
                            </tr>
                            <tr>
                                <th>Service</th>
                                <td>${event.extendedProps.service}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-${event.extendedProps.status === 'pending' ? 'warning' : 'success'}">
                                        ${event.extendedProps.status}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
</script>

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

<?php include_once('includes/footer.php'); ?>