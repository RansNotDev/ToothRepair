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
    .fc .fc-daygrid-day-frame {
        min-height: 120px !important;
        height: 120px !important;
        border: 1px solid #ddd !important;
        background: white;
        position: relative;
        padding: 5% !important;
        display: flex;
        flex-direction: column;
    }

    /* Remove default FullCalendar padding */
    .fc .fc-daygrid-day-top {
        flex-direction: row !important;
        justify-content: flex-end;
        height: 30% !important;
    }

    /* Large date number styling */
    .fc .fc-daygrid-day-number {
        font-size: 1.5em;
        font-weight: bold;
        color: #333;
        padding: 0 !important;
        position: relative;
        z-index: 1;
    }

    /* Status box styling */
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

    /* Status text styling */
    .status-text {
        position: relative;
        z-index: 1;
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

    /* Notification badge - now inside the color box */
    .notification-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    /* Prevent cell content shifting */
    .fc-daygrid-day-events {
        display: none !important;
    }

    .fc .fc-daygrid-body-balanced .fc-daygrid-day-events {
        position: absolute;
    }
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
            editable: false,
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

                // Add different status based on conditions
                if (day === 12) {
                    statusBox.classList.add('status-holiday');
                    
                    // Create container for notification and status text
                    const contentWrapper = document.createElement('div');
                    contentWrapper.style.position = 'relative';
                    contentWrapper.style.width = '100%';
                    contentWrapper.style.height = '100%';
                    
                    // Add notification inside the color box
                    const badge = document.createElement('div');
                    badge.className = 'notification-badge';
                    badge.innerHTML = '2';
                    contentWrapper.appendChild(badge);
                    
                    // Add status text
                    const statusText = document.createElement('div');
                    statusText.className = 'status-text';
                    statusText.innerHTML = 'Holiday';
                    statusText.style.position = 'absolute';
                    statusText.style.left = '50%';
                    statusText.style.top = '50%';
                    statusText.style.transform = 'translate(-50%, -50%)';
                    contentWrapper.appendChild(statusText);
                    
                    statusBox.appendChild(contentWrapper);
                } else if (day === 13 || day === 14) {
                    statusBox.classList.add('status-available');
                    
                    // Create container for notification and status text
                    const contentWrapper = document.createElement('div');
                    contentWrapper.style.position = 'relative';
                    contentWrapper.style.width = '100%';
                    contentWrapper.style.height = '100%';
                    
                    if (day === 13) {
                        const badge = document.createElement('div');
                        badge.className = 'notification-badge';
                        badge.innerHTML = '2';
                        contentWrapper.appendChild(badge);
                    }
                    
                    const statusText = document.createElement('div');
                    statusText.className = 'status-text';
                    statusText.innerHTML = 'Available';
                    statusText.style.position = 'absolute';
                    statusText.style.left = '50%';
                    statusText.style.top = '50%';
                    statusText.style.transform = 'translate(-50%, -50%)';
                    contentWrapper.appendChild(statusText);
                    
                    statusBox.appendChild(contentWrapper);
                } else if (day === 15 || day === 16) {
                    statusBox.classList.add('status-no-slots');
                    
                    const statusText = document.createElement('div');
                    statusText.className = 'status-text';
                    statusText.innerHTML = 'No Slots';
                    statusBox.appendChild(statusText);
                }

                cellContent.appendChild(statusBox);
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