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

// Get appointment counts
$appointmentCounts = [];
$result = mysqli_query($conn, 
    "SELECT appointment_date, COUNT(*) AS count 
     FROM appointments 
     WHERE status IN ('confirmed','pending') 
     GROUP BY appointment_date");
while ($row = mysqli_fetch_assoc($result)) {
    $appointmentCounts[$row['appointment_date']] = $row['count'];
}

// Fetch appointment data
$sched_arr = [];
$query = "SELECT 
            a.appointment_id AS id, 
            u.fullname AS patient_name, 
            a.appointment_date, 
            a.appointment_time, 
            s.service_name AS service,
            a.status
          FROM appointments a
          INNER JOIN users u ON a.user_id = u.user_id
          INNER JOIN services s ON a.service_id = s.service_id";
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
    }

    .fc-event:hover {
        background-color: #4e73df !important;
        color: white !important;
    }

    .slot-info {
        color: #666;
        font-size: 0.8em;
        margin-top: 2px;
    }

    .fc-day-past .slot-info {
        display: none;
    }
</style>

<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js'></script>
<script src="js/sbadmin.js"></script>

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
                right: 'dayGridMonth'
            },
            themeSystem: 'bootstrap',
            initialView: 'dayGridMonth',
            events: scheds.map(sched => ({
                id: sched.id,
                title: sched.patient_name,
                start: `${sched.appointment_date}T${sched.appointment_time}`,
                backgroundColor: sched.status === "pending" ? "orange" : "green",
                borderColor: sched.status === "pending" ? "orange" : "green"
            })),
            eventClick: function(info) {
                uni_modal("Appointment Details", `viewdetails.php?id=${info.event.id}`);
            },
            validRange: {
                start: moment().format("YYYY-MM-DD")
            },
            dayCellContent: function(args) {
                const dateStr = args.date.toISOString().split('T')[0];
                const isPast = args.date < new Date().setHours(0,0,0,0);
                const isClosed = closures.includes(dateStr);
                const count = appointmentCounts[dateStr] || 0;
                const available = isClosed ? 0 : Math.max(maxDaily - count, 0);
                
                return {
                    html: `
                        <div class="fc-daygrid-day-number">${args.dayNumberText}</div>
                        <div class="slot-info small">
                            ${isPast ? '' : (isClosed ? 'Closed' : `${available} slots available`)}
                        </div>
                    `
                };
            }
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