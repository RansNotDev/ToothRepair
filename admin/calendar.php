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

<!-- Add only the necessary calendar-specific CSS -->
<link rel="stylesheet" href="../assets/Assetscalendar/fullcalendar/main.css">

<!-- Remove adminlte.min.css as it's causing the color conflict -->
<!-- <link rel="stylesheet" href="../assets/Assetscalendar/dist/css/adminlte.min.css"> -->

<style>
    /* Override any conflicting styles */
    .navbar-nav.bg-gradient-primary {
        background-color: #4e73df;
        background-image: linear-gradient(180deg,#4e73df 10%,#224abe 100%);
    }

    #calendar {
        width: 100%;
        height: auto;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .fc-header-toolbar {
        padding: 1rem;
    }

    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
        }
        .fc-header-toolbar > * {
            margin-bottom: 0.5rem;
        }
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
      editable: true,
      droppable: false,
      clickable: true,
      aspectRatio: 1.8,
      contentHeight: 'auto',
      dayMaxEventRows: true,
      initialView: 'dayGridMonth',
      events: [], // You can add your events data here
      eventClick: function(info) {
        // Handle event click
        alert('Event: ' + info.event.title);
      },
      dateClick: function(info) {
        // Handle date click
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