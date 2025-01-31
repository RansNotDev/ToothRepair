<?php
require_once '../database/db_connection.php'; // Include the database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "<script>Swal.fire('Error', 'Please fill all fields', 'error');</script>";
        die();
    }

    $stmt = $conn->prepare("SELECT admin_id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        // Use password_verify to check the password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Invalid username or password.";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: '".addslashes($error_message)."',
                    });
                });
            </script>";
        }
    } else {
        $error_message = "Invalid username or password.";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: '".addslashes($error_message)."',
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tooth Repair Dental Clinic | Login</title>
    <!-- FontAwesome (outside admin) -->
    <link href="../plugins/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Local CSS (inside admin/css) -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Admin</h1>
                                    </div>
                                    <form class="user" method="POST" action="">
                                        <div class="form-group">
                                            <!-- Changed to username input -->
                                            <input type="text" class="form-control form-control-user" name="username" id="exampleInputUsername" placeholder="Enter Username..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"  name="password" id="exampleInputPassword" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <!-- Scripts (correct order and paths) -->
   <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-easing@1.4.1/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>