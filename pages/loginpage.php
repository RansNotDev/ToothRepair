<?php
require '../database/db_connection.php';
include_once("../includes/header.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    //validate Inputs
    if (empty($email) || empty($password)) {
        echo "Please fill in all fields"; 
        die();
    }

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
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

</head>

<body>
    <div class="container">
        <!-- Left Content -->
        <div class="col">
            <h1>Welcome Back</h1>
            <img src="../images/loginpage.png" alt="Login Image" class="hero-image">
            <p>Log in to your account to see your appointment details</p>
        </div>

        <!-- Login Form -->
        <div class="col">
            <div class="card">
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
                    <button type="submit">Login</button>
                    
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<?php include_once("../includes/footer.php"); ?>