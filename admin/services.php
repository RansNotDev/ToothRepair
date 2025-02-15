<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
include_once('../database/db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Boot Strap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Sweet Alert 2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- SB Admin 2 Template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        /* Reset default overflow */
        html, body {
            height: 100%;
            overflow: hidden;
        }

        /* Main content wrapper */
        .content-wrapper {
            height: 100vh;
            overflow: hidden;
            padding-top: 4.375rem; /* Adjust based on your topbar height */
        }

        /* Container for the services content */
        .container-fluid {
            height: calc(100vh - 4.375rem);
            overflow-y: auto;
            padding-bottom: 2rem;
        }

        /* Hide horizontal overflow */
        #wrapper #content-wrapper {
            overflow-x: hidden;
        }

        /* Table container styles */
        .card {
            margin-bottom: 1rem;
        }

        .table-responsive {
            overflow-y: visible;
        }

        /* Sticky header */
        thead {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 1;
        }

        /* Scrollbar styling */
        .container-fluid::-webkit-scrollbar {
            width: 8px;
        }

        .container-fluid::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .container-fluid::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .container-fluid::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body>
    <!-- Wrap the container-fluid in a content-wrapper div -->
    <div class="content-wrapper">
        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Services Management</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>
            <!-- Display Services Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Services List</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT service_id, service_name, description, price, image FROM services";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["service_name"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["price"]) . "</td>";
                                        // Display the image
                                        echo "<td>";
                                        echo '<img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="Service Image" style="width: 100px; height: auto;"/>';
                                        echo "</td>";
                                        echo "<td>
                                                <button class='btn btn-primary edit-btn' data-service-id='" . $row['service_id'] . "' data-bs-toggle='modal' data-bs-target='#editServiceModal'>
                                                    <i class='fas fa-edit'></i> Edit
                                                </button>
                                                <button class='btn btn-danger delete-btn' data-service-id='" . $row['service_id'] . "'>
                                                    <i class='fas fa-trash'></i> Delete
                                                </button>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No services found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <!-- Modal Add Service -->
    </div>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <!-- Modal edit Service -->
    </div>

    <!-- Add this before the footer include -->
    <!-- Modal Add Service -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addServiceForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Service Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Include Footer -->
    <?php include_once('includes/footer.php'); ?>
    <script>
    $(document).ready(function() {
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            var serviceId = $(this).data('service-id');
            console.log("Deleting service ID: " + serviceId); // Add this line

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                showClass: {
                    popup: 'swal2-show'
                },
                hideClass: {
                    popup: 'swal2-hide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the service
                    $.ajax({
                        url: 'services/delete_services.php', // Create this file
                        type: 'POST',
                        data: {
                            service_id: serviceId
                        },
                        success: function(response) {
                            console.log("AJAX response: " + response); // Add this line
                            if (response.trim() === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    'Your service has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an error deleting the service: ' + response, // Include the response
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) { // Improved error handling
                            console.error("AJAX error:", status, error); // Log the error
                            Swal.fire(
                                'Error!',
                                'There was an error communicating with the server: ' + error,
                                'error'
                            );
                        }
                    });
                }
            })
        });

        $('#addServiceForm').submit(function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);

            $.ajax({
                url: 'services/add_service.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Service added successfully',
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: result.message,
                                icon: 'error'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an error communicating with the server',
                        icon: 'error'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>