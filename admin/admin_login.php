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
            $error_message = "Invalid credentials";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: 'Invalid credentials'
                    });
                });
            </script>";
        }
    } else {
        $error_message = "Invalid credentials";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Invalid credentials'
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
    <!-- FontAwesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- SB Admin 2 CSS CDN -->
    <link href="https://startbootstrap.github.io/startbootstrap-sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .container {
            max-width: 1140px;
            margin: auto;
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        @media (max-height: 800px) {
            body {
                align-items: flex-start;
            }
        }

        .bg-login-image {
            background: url('../images/logo/cliniclogo.png');
            background-position: center;
            background-size: cover;
            position: relative;
        }

        .bg-login-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(78, 115, 223, 0.1);
        }

        .floating-label-form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .floating-label-form-group input {
            font-size: 1.1rem;
            border: none;
            border-bottom: 2px solid #e3e6f0;
            border-radius: 0;
            padding: 1rem 0;
            background: transparent;
            transition: all 0.3s ease;
        }

        .floating-label-form-group label {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            padding: 1rem 0;
            transition: 0.2s ease all;
            color: #858796;
        }

        .floating-label-form-group input:focus {
            border-bottom: 2px solid #4e73df;
            box-shadow: none;
        }

        .floating-label-form-group input:focus ~ label,
        .floating-label-form-group input:not(:placeholder-shown) ~ label {
            top: -20px;
            font-size: 0.85rem;
            color: #4e73df;
            font-weight: 600;
        }

        .btn-login {
            padding: 0.75rem;
            font-size: 1rem;
            border-radius: 2rem;
            transition: all 0.3s;
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
            background: linear-gradient(to right, #224abe, #4e73df);
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .card-body {
            background: rgba(255, 255, 255, 0.95);
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header img {
            max-width: 120px;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            color: #2d2d2d;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #858796;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center login-header">
                                        <img src="../images/logo/cliniclogo.png" alt="Dental Clinic Logo" class="mb-4">
                                        <h1 class="text-gray-900">Welcome Back!</h1>
                                        <p>Please login to access the admin dashboard</p>
                                    </div>
                                    <form class="user" method="POST" action="">
                                        <div class="floating-label-form-group">
                                            <input type="text" class="form-control" name="username" id="exampleInputUsername" placeholder=" " required>
                                            <label for="exampleInputUsername">Username</label>
                                        </div>
                                        <div class="floating-label-form-group">
                                            <input type="password" class="form-control" name="password" id="exampleInputPassword" placeholder=" " required>
                                            <label for="exampleInputPassword">Password</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block btn-login">
                                            <i class="fas fa-sign-in-alt mr-2"></i>Login
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
   <!-- Scripts -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-easing@1.4.1/jquery.easing.min.js"></script>
    <script src="https://startbootstrap.github.io/startbootstrap-sb-admin-2/js/sb-admin-2.min.js"></script>
    <!-- SweetAlert2 JS -->
</html>