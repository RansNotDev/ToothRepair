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
        body {
            height: 100%;
            overflow: hidden;
        }
        .content-wrapper{
            overflow: auto;
        }

        /* Container for the services content */
        .container-fluid {
            height: 100%;
        }

        /* Table styles */
        .card {
            margin-bottom: 1px;
        }

        .table-responsive {
            overflow: visible;
        }

        /* Sticky header */
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 1;
        }

        /* Custom scrollbar for content-wrapper */
        .content-wrapper::-webkit-scrollbar {
            width: 5px;
        }

       
    </style>
</head>

<body>
    <!-- Begin Page Content -->
    <div class="d-flex" id="wrapper">
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
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
                                        $sql = "SELECT service_id, service_name, description, price, image FROM services WHERE is_active = 1";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row["service_name"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                                                echo "<td>₱" . number_format($row["price"], 2) . "</td>";
                                                echo "<td>";
                                                if ($row['image']) {
                                                    echo '<img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="Service Image" style="width: 100px; height: auto;"/>';
                                                } else {
                                                    echo 'No image available';
                                                }
                                                echo "</td>";
                                                echo "<td>
                                                        <button class='btn btn-primary btn-sm edit-btn' data-service-id='" . $row['service_id'] . "' data-bs-toggle='modal' data-bs-target='#editServiceModal'>
                                                            <i class='fas fa-edit'></i> Edit
                                                        </button>
                                                        <button class='btn btn-danger btn-sm delete-btn' data-service-id='" . $row['service_id'] . "'>
                                                            <i class='fas fa-trash'></i> Delete
                                                        </button>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No services found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
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
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
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

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editServiceForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="edit_service_id" name="service_id">
                        <div class="mb-3">
                            <label for="edit_service_name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="edit_service_name" name="service_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Service Image</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Service</button>
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
        // Initialize DataTable
        $('#dataTable').DataTable({
            scrollY: '50vh',
            scrollCollapse: true,
            paging: true
        });

        // Form validation and submission
        $('#addServiceForm').submit(function(e) {
            e.preventDefault();
            
            // Validate form
            const serviceName = $('#service_name').val().trim();
            const description = $('#description').val().trim();
            const price = $('#price').val();
            const image = $('#image')[0].files[0];

            // Basic validation
            if (!serviceName || !description || !price || !image) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all required fields',
                    icon: 'error'
                });
                return;
            }

            // Price validation
            if (isNaN(price) || price <= 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter a valid price',
                    icon: 'error'
                });
                return;
            }

            // Image validation
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(image.type)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a valid image file (JPEG, PNG, or GIF)',
                    icon: 'error'
                });
                return;
            }

            const formData = new FormData(this);
            
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we add the service',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'services/add_service.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Service added successfully',
                            icon: 'success',
                            timer: 1500
                        }).then(() => {
                            $('#addServiceModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Unknown error occurred',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'An error occurred while processing your request.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        });

        // Improved delete functionality
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            const serviceId = $(this).data('service-id');
            
            Swal.fire({
                title: 'Deactivate Service?',
                text: "This service will be hidden but appointments will be preserved. This can be reversed by an administrator.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, deactivate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we deactivate the service',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'services/soft_delete_service.php',
                        type: 'POST',
                        data: { service_id: serviceId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Deactivated!',
                                    text: 'Service has been deactivated successfully.',
                                    icon: 'success',
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to deactivate service',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to deactivate service';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            } catch (e) {
                                console.error('Error parsing error response:', e);
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Edit button click handler
        $('.edit-btn').click(function() {
            const serviceId = $(this).data('service-id');
            
            // Show loading state
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch service details
            $.ajax({
                url: 'services/get_service.php',
                type: 'GET',
                data: { service_id: serviceId },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        $('#edit_service_id').val(response.service.service_id);
                        $('#edit_service_name').val(response.service.service_name);
                        $('#edit_description').val(response.service.description);
                        $('#edit_price').val(response.service.price);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to fetch service details', 'error');
                }
            });
        });

        // Edit form submission
        $('#editServiceForm').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we update the service',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'services/update_service.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Service updated successfully',
                            icon: 'success',
                            timer: 1500
                        }).then(() => {
                            $('#editServiceModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update service';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {}
                    
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        });
    });
    </script>
</body>
</html>