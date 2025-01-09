<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tooth Repair</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/Assetscalendar/fontawesome-free/css/all.min.css">
  <!-- fullCalendar -->
  <link rel="stylesheet" href="../assets/Assetscalendar/fullcalendar/main.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/Assetscalendar/dist/css/adminlte.min.css">

  <style>
    /* Center the calendar on the screen */
    .center-screen {
      display: flex;
      justify-content: center;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: #f4f6f9; /* Optional: Add a background color */
      z-index: 9999;
    }

    /* Calendar container styles */
    #calendar {
      width: 90%; /* Adjust the width */
      max-width: 900px; /* Optional: Set a maximum width */
      height: 80%; /* Adjust the height */
      background-color: white; /* Optional: Add background color */
      border-radius: 8px; /* Optional: Rounded corners */
      overflow: hidden; /* Ensure content doesn't overflow */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Add shadow */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      #calendar {
        width: 100%; /* Full width for smaller screens */
        height: 100%; /* Full height for smaller screens */
      }
    }
  </style>
</head>
<body>
<div class="wrapper">
  <!-- Center the calendar -->
  <div class="center-screen">
    <div id="calendar"></div>
  </div>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../assets/Assetscalendar/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../assets/Assetscalendar/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- jQuery UI -->
<script src="../assets/Assetscalendar/jquery-ui/jquery-ui.min.js"></script>
<!-- AdminLTE App -->
<script src="../assets/Assetscalendar/dist/js/adminlte.min.js"></script>
<!-- fullCalendar -->
<script src="../assets/Assetscalendar/moment/moment.min.js"></script>
<script src="../assets/Assetscalendar/fullcalendar/main.js"></script>

<script>
  $(function () {
    // Initialize FullCalendar
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      themeSystem: 'bootstrap',
      editable: true, // Allow editing of events
      droppable: false, // Disable drag-and-drop
      clickable: true, // Allow clicking on events
      aspectRatio: 2.5, // Control cell size by aspect ratio
      contentHeight: 'auto', // Automatically adjust height
      dayMaxEventRows: true, // Allow multiple events in a single day
    });
    calendar.render();
  });
</script>
</body>
</html>
