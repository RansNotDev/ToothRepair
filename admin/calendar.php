<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('../database/db_connection.php'); // Ensure this connects to your database
include_once('includes/topbar.php');

// Fetch appointment data from the database
$sched_arr = [];
$query = "
    SELECT 
        a.appointment_id AS id, 
        u.fullname AS patient_name, 
        a.appointment_date, 
        a.appointment_time, 
        a.service, 
        a.status
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.user_id
";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sched_arr[] = $row; // Append each row to the schedule array
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
                <!-- Add a scrollable container for the calendar -->
                <div id="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ensure only the calendar scrolls */
    #calendar-container {
        height: 600px; /* Adjust as needed */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ddd;
        padding: 10px;
        background: #f8f9fc; /* Optional styling */
    }

    .fc-event {
        cursor: pointer;
    }

    .fc-event:hover {
        background-color: #4e73df !important;
        color: white !important;
    }
</style>

<!-- Include FullCalendar and Moment.js libraries -->
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js'></script>
<!-- Include the sbadmin -->
<script src="js/sbadmin.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var scheds = <?php echo json_encode($sched_arr); ?>; // Convert PHP array to JavaScript

    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        customButtons: {
            prev: {
                text: '<',
                click: function() {
                    calendar.prev();
                }
            },
            next: {
                text: '>',
                click: function() {
                    calendar.next();
                }
            }
        },
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        events: scheds.map(function (sched) {
            var bgColor = sched.status === "pending" ? "orange" : "green";
            return {
                id: sched.id,
                title: sched.patient_name,
                start: sched.appointment_date + 'T' + sched.appointment_time,
                backgroundColor: bgColor,
                borderColor: bgColor,
            };
        }),
        eventClick: function(info) {
            uni_modal("Appointment Details", "view_details.php?id=" + info.event.id);
        },
        validRange: {
            start: moment().format("YYYY-MM-DD")
        }
    });

    calendar.render();
});

// Function to open a modal dynamically
function uni_modal(title, url) {
    // Dynamically load content into a modal
    $('#uniModal .modal-title').text(title);
    $('#uniModal .modal-body').load(url, function () {
        $('#uniModal').modal('show');
    });
}
</script>

<!-- Universal Modal -->
<div id="uniModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="uniModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uniModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<?php include_once('includes/footer.php'); ?>