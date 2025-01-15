<!-- Sidebar -->
<style>
  #accordionSidebar {
    background-color: #4e73df; /* Blue background */
    color: white;
  }

  #accordionSidebar .nav-link {
    padding: 1rem;
    color: white;
    border-radius: 0;
  }
  #accordionSidebar .nav-link:hover,
  #accordionSidebar .nav-link.active {
    background-color: #3658b8; /* Darker blue for hover and active state */
  }


  #accordionSidebar .nav-link.active,
  #accordionSidebar .nav-link[aria-expanded="true"] {
    background-color: #3658b8; /* Highlight active link */
    color: white;
  }
  #accordionSidebar .nav-link.active,
  #accordionSidebar .collapse-inner{
    background-color: #3658b8; /* Highlight active link */
    color: white;
  }
  #accordionSidebar .sidebar-heading {
    padding: 0.5rem 1rem;
    text-transform: uppercase;
    font-size: 0.7rem;
  }

  #accordionSidebar .nav-item {
    margin-bottom: 5px; /* Spacing between items */
  }

  #accordionSidebar .nav-link i {
    margin-right: 10px;
    font-size: 1.2rem;
  }

  #accordionSidebar .sidebar-brand {
    color: white;
    font-weight: bold;
  }

  #accordionSidebar .sidebar-brand .sidebar-brand-icon {
    font-size: 2rem;
  }
</style>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-2">Tooth Repair</div>
      </a>
      <hr class="sidebar-divider my-0">

      <!-- Dashboard -->
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <hr class="sidebar-divider">

      <!-- Interface -->
      <div class="sidebar-heading">Interface</div>
      <li class="nav-item">
        <a class="nav-link" href="calendar.php">
          <i class="fas fa-fw fa-calendar"></i>
          <span>Appointment List</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="tables.php">
          <i class="fas fa-fw fa-users"></i>
          <span>Manage Users</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="reports.php">
          <i class="fas fa-fw fa-chart-area"></i>
          <span>Reports & Analytics</span>
        </a>
      </li>
      <hr class="sidebar-divider">

      <!-- Settings -->
      <div class="sidebar-heading">Settings</div>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
          <i class="fas fa-fw fa-cog"></i>
          <span>Configurations</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Custom Pages:</h6>
            <a class="collapse-item" href="#">404 Page</a>
            <a class="collapse-item" href="#">Blank Page</a>
          </div>
        </div>
      </li>

      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggle -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
    </ul>
    <!-- End of Sidebar -->
  </div>

  <!-- JavaScript -->
  <script>

document.querySelectorAll('#accordionSidebar .nav-link').forEach(link => {
  if (link.href === window.location.href) {
    link.classList.add('active');
  }
});
    // Get all sidebar links
    const navLinks = document.querySelectorAll('#accordionSidebar .nav-link');

    // Add click event to each link
    navLinks.forEach(link => {
      link.addEventListener('click', function () {
        // Remove 'active' class from all links
        navLinks.forEach(nav => nav.classList.remove('active'));

        // Add 'active' class to the clicked link
        this.classList.add('active');
      });
    });

    // Handle collapsible sections
    const collapsibleLinks = document.querySelectorAll('#accordionSidebar .nav-link.collapsed');
    collapsibleLinks.forEach(collapsible => {
      collapsible.addEventListener('click', function () {
        // Remove active class from non-collapsible links
        navLinks.forEach(nav => {
          if (!nav.classList.contains('collapsed')) {
            nav.classList.remove('active');
          }
        });

        // Ensure proper active state for collapsibles
        this.classList.add('active');
      });
    });
  </script>
</body>