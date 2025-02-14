<?php
require '../database/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    //validate Inputs
    if (empty($email) || empty($password)) {
        echo "Please fill in all fields"; 
        die();
    }

    $stmt = $conn->prepare("SELECT user_id, password, fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $fullname);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['fullname'] = $fullname; // Add this line where you set other session variables
            header('Location: userdashboard.php');
            exit();
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Invalid Credentials!',
            });
        </script>";
        }
    } else {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid Credentials!',
        });
        </script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Login to your account to access all the features and services we provide.">
    <meta name="keywords" content="tooth repair clinic, login, account, services">
    <meta name="author" content="Tooth Repair Clinic">

    <title>Login Page | Tooth Repair Clinic</title>
    <link rel="stylesheet" href="../assets/css/loginpage.css">
    <link href="../plugins/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/Assetscalendar/fullcalendar/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../admin/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: 'Nunito', sans-serif;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .col {
            flex: 1;
            padding: 20px;
        }
        .hero-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        .form-outline {
            margin-bottom: 15px;
        }
        .form-outline input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-outline label {
            display: block;
            margin-top: 5px;
            font-weight: bold;
        }
        .forgot-password {
            margin-top: 10px;
        }
        .forgot-password a {
            color: #2575fc;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        button {
            background: #2575fc;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #6a11cb;
        }
        .btn-primary {
            background: #2575fc;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background: #6a11cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Left Content -->
        <div class="col">
            <h1 class="animate__animated animate__fadeInLeft text-white">Welcome Back</h1>
            <img src="../img/loginpage.png" alt="Login Image" class="hero-image animate__animated animate__fadeInLeft">
            <p class="animate__animated animate__fadeInLeft text-white">Log in to your account to see your appointment details</p>
        </div>

        <!-- Login Form -->
        <div class="col">
            <div class="card animate__animated animate__fadeInRight">
                <h3>Login</h3>
                <form method="POST" action="">
                    <div class="form-outline">
                        <input type="email" id="email" name="email" required />
                        <label for="email">Email Address</label>
                    </div>
                    <div class="form-outline">
                        <input type="password" id="password" name="password" required />
                        <label for="password">Password</label>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="../index.php" class="btn btn-primary" style="flex: 1; margin-right: 10px;">Back To Website</a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Login</button>
                    </div>
                    <br>
                    <div class="forgot-password">
                        <a href="forgot_password.php">Forgot Password?</a>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<?php include_once("../includes/footer.php"); ?>