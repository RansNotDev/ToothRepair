
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
            background-color: #4e73df;
            color: white;
        }

        #accordionSidebar .nav-link {
            padding: 1rem;
            color: white;
            border-radius: 0;
        }

        #accordionSidebar .nav-link:hover,
        #accordionSidebar .nav-link.active {
            background-color: #3658b8;
        }

        #accordionSidebar .nav-link.active,
        #accordionSidebar .collapse-inner {
            background-color: #3658b8;
            color: white;
        }

        #accordionSidebar .sidebar-heading {
            padding: 0.5rem 1rem;
            text-transform: uppercase;
            font-size: 0.7rem;
        }

        #accordionSidebar .nav-item {
            margin-bottom: 5px;
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
        .sidebar-divider{
            background-color: white;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-tooth"></i>
                </div>
                <div class="sidebar-brand-text mx-2">Tooth Repair</div>
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
                    <span>Calendar</span>
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