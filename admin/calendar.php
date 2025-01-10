<?php 
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
?>

<!-- Begin Page Content -->
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

</style>

<?php include_once('includes/footer.php'); ?>

<!-- Calendar specific scripts -->
<script src="../assets/Assetscalendar/moment/moment.min.js"></script>
<script src="../assets/Assetscalendar/fullcalendar/main.js"></script>

<script>
    $(function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'bootstrap',
            editable: true,
            droppable: false,
            aspectRatio: 1.8,
            contentHeight: 'auto',
            dayMaxEventRows: true,
            initialView: 'dayGridMonth',
            dayCellDidMount: function(arg) {
    const date = arg.date;
    const day = date.getDate();
    const cellContent = arg.el.querySelector('.fc-daygrid-day-frame');

    // Create status box
    const statusBox = document.createElement('div');
    statusBox.className = 'status-box';
    
    if (day === 12) {
        statusBox.style.backgroundColor = '#6f42c1'; // Holiday
        statusBox.innerHTML = `
            <div class="notification-badge">2</div>
            <span>Holiday</span>
        `;
    } else if (day === 13 || day === 14) {
        statusBox.style.backgroundColor = '#28a745'; // Available
        if (day === 13) {
            statusBox.innerHTML = `
                <div class="notification-badge">2</div>
                <span>Available</span>
            `;
        } else {
            statusBox.innerHTML = '<span>Available</span>';
        }
    } else if (day === 15 || day === 16) {
        statusBox.style.backgroundColor = '#007bff'; // No Slots
        statusBox.innerHTML = '<span>No Slots</span>';
    }

    if (statusBox.innerHTML) {
        cellContent.appendChild(statusBox);
    }
},
            eventClick: function(info) {
                alert('Event: ' + info.event.title);
            },
            dateClick: function(info) {
                alert('Clicked on: ' + info.dateStr);
            }
        });
        calendar.render();

        // Adjust calendar size when sidebar is toggled
        $("#sidebarToggle, #sidebarToggleTop").on('click', function() {
            setTimeout(function() {
                calendar.updateSize();
            }, 300);
        });
    });
</script>