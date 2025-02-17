<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Title</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        #accordionSidebar {
            background-color: rgb(152, 193, 233);
            color: white;
        }

        #accordionSidebar .nav-link {
            padding: 1rem;
            color: white;
            border-radius: 0;
        }

        #accordionSidebar .nav-link:hover,
        #accordionSidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        #accordionSidebar .nav-link.active,
        #accordionSidebar .collapse-inner {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
        }

        #accordionSidebar .sidebar-brand {
            color: white;
            font-weight: bold;
            padding: 1rem;
        }

        #accordionSidebar .sidebar-brand-icon img {
            width: 40px;
            height: 40px;
            filter: brightness(1.2) contrast(1.2);  /* Increase brightness and contrast */
            background-color: white;  /* Add white background */
            padding: 5px;  /* Add padding around the logo */
            border-radius: 50%;  /* Make it circular */
            box-shadow: 0 0 10px rgba(16, 92, 192, 0.3);  /* Add subtle glow */
        }

        #accordionSidebar .sidebar-brand-text {
            font-size: 12px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);  /* Add subtle text shadow */
        }

        #accordionSidebar .sidebar-heading {
            color: rgba(205, 205, 235, 0.8);
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon">
                    <img src="../img/logo/cliniclogo.png" alt="Clinic Logo" style="width: 40px; height: 40px;">
                </div>
                <div class="sidebar-brand-text mx-2">Tooth Repair Dental Clinic</div>
            </a>
           

            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">

            <div class="sidebar-heading">Management</div>
            <li class="nav-item">
                <a class="nav-link" href="calendar.php">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Appointments Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="appointmentlist.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Appointment List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="record_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Appointment Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Reports & Analytics</span>
                </a>
            </li>
            <hr class="sidebar-divider">

            <div class="sidebar-heading">Settings</div>

            <li class="nav-item">
                <a class="nav-link" href="admin_availability.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Set Available Date</span>
                </a>
            </li>

            <!-- add edit update Services of the clinic here  -->
            <li class="nav-item">
                <a class="nav-link" href="services.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Services</span>
                </a>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            const currentPath = window.location.pathname;
            $('#accordionSidebar .nav-link').each(function() {
                const linkPath = $(this).attr('href');
                if (currentPath.includes(linkPath)) {
                    $(this).addClass('active');
                }
            });

            $('#accordionSidebar').on('shown.bs.collapse', function(e) {
                const target = $(e.target);
                if (target.hasClass('collapse-inner')) {
                    target.parent().find('.nav-link').addClass('active');
                }
                $(this).find('.nav-item .nav-link').not(target.parent().find('.nav-link')).removeClass('active');
            });

            $('#accordionSidebar').on('hidden.bs.collapse', function(e) {
                const target = $(e.target);
                if (target.hasClass('collapse-inner')) {
                    target.parent().find('.nav-link').removeClass('active');
                }
            });
        });
    </script>

</body>

</html>