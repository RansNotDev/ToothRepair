<?php
include_once('includes/header.php');
include_once('includes/sidebar.php');
include_once('includes/topbar.php');
?>
<style>
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
    }
    .btn-group .btn {
        border: 1px;
        border-radius: 1px;
    }
    .btn-group .btn:hover {
        border: 1px;
    }
</style>


            <!-- Begin Page Content -->
        <div class="container-fluid">

<!-- Page Heading -->
<!-- <h1 class="h3 mb-2 text-gray-800">Tables</h1>-->
<!-- <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below. -->
   <!--  For more information about DataTables, please visit the <a target="_blank"-->
       <!--  href="https://datatables.net">official DataTables documentation</a>.</p>-->

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">DataTables Example</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                                        <th>Full Name</th>
                                        <th>Register Date</th>
                                        <th>Appointment Date</th>
                                        <th>Contact Number</th>
                                        <th>Email</th>
                                        <th>Appointment Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Register Date</th>
                                        <th>Appointment Date</th>
                                        <th>Contact Number</th>
                                        <th>Email</th>
                                        <th>Appointment Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <tr>
                                        <td>Cedric Kelly</td>
                                        <td>2023/10/28</td>
                                        <td>2024/01/15</td>
                                        <td>123-456-7890</td>
                                        <td>cedric.kelly@email.com</td>
                                        <td>Cleaning</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button> 
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Airi Satou</td>
                                        <td>2023/11/12</td>
                                        <td>2024/02/05</td>
                                        <td>987-654-3210</td>
                                        <td>airi.satou@email.com</td>
                                        <td>Check-up</td>
                                        <td>Pending</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Brielle Williamson</td>
                                        <td>2023/09/05</td>
                                        <td>2024/01/22</td>
                                        <td>555-123-4567</td>
                                        <td>brielle.williamson@email.com</td>
                                        <td>Extraction</td>
                                        <td>Cancelled</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Herrod Chandler</td>
                                        <td>2023/08/18</td>
                                        <td>2024/03/10</td>
                                        <td>111-222-3333</td>
                                        <td>herrod.chandler@email.com</td>
                                        <td>Filling</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Rhona Davidson</td>
                                        <td>2023/12/02</td>
                                        <td>2024/02/18</td>
                                        <td>444-555-6666</td>
                                        <td>rhona.davidson@email.com</td>
                                        <td>Root Canal</td>
                                        <td>Pending</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Colleen Hurst</td>
                                        <td>2023/11/20</td>
                                        <td>2024/01/08</td>
                                        <td>777-888-9999</td>
                                        <td>colleen.hurst@email.com</td>
                                        <td>Cleaning</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sonya Frost</td>
                                        <td>2023/10/15</td>
                                        <td>2023/12/29</td>
                                        <td>222-333-4444</td>
                                        <td>sonya.frost@email.com</td>
                                        <td>Check-up</td>
                                        <td>Completed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jena Gaines</td>
                                        <td>2023/09/28</td>
                                        <td>2024/02/25</td>
                                        <td>555-666-7777</td>
                                        <td>jena.gaines@email.com</td>
                                        <td>Braces Adjustment</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Quinn Flynn</td>
                                        <td>2023/12/10</td>
                                        <td>2024/03/05</td>
                                        <td>888-999-0000</td>
                                        <td>quinn.flynn@email.com</td>
                                        <td>Consultation</td>
                                        <td>Pending</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Charde Marshall</td>
                                        <td>2023/11/05</td>
                                        <td>2024/01/12</td>
                                        <td>333-444-5555</td>
                                        <td>charde.marshall@email.com</td>
                                        <td>Whitening</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Haley Kennedy</td>
                                        <td>2023/08/22</td>
                                        <td>2024/02/28</td>
                                        <td>666-777-8888</td>
                                        <td>haley.kennedy@email.com</td>
                                        <td>Veneers</td>
                                        <td>Pending</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tatyana Fitzpatrick</td>
                                        <td>2023/10/01</td>
                                        <td>2024/01/20</td>
                                        <td>999-000-1111</td>
                                        <td>tatyana.fitzpatrick@email.com</td>
                                        <td>Cleaning</td>
                                        <td>Confirmed</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Michael Silva</td>
                                        <td>2023/12/18</td>
                                        <td>2024/03/15</td>
                                        <td>111-222-3333</td>
                                        <td>michael.silva@email.com</td>
                                        <td>Implant Consultation</td>
                                        <td>Pending</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm mr-2" style="border-radius: 1px;">Edit</button>
                                                <button class="btn btn-danger btn-sm mr-2" style="border-radius: 1px;">Delete</button>
                                                <button class="btn btn-info btn-sm" style="border-radius: 1px;">View</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
    </div>
    <!-- End of Content Wrapper -->
    <?php include_once('includes/footer.php'); ?>

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Include jQuery, Bootstrap, and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>


<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    
    // Ensure sidebar collapse functionality works
    $('.nav-link.collapsed').on('click', function() {
        var target = $(this).data('target');
        $(target).collapse('toggle');
    });

    // Scroll to top when sidebar item is clicked
    $('.nav-item').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });
});
</script>
</body>

</html>