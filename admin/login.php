<?php
require_once 'db_connection.php';

<<<<<<< HEAD
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get email and password from the form
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Query to validate credentials
    $query = "SELECT * FROM admin WHERE email = ? AND password = PASSWORD(?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ss', $email, $password);
=======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
>>>>>>> 0003e1ed493a19b28403b75ca385eb70342caa11
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
<<<<<<< HEAD
            $admin = $result->fetch_assoc();

            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        $error_message = "Database query error. Please try again.";
=======
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            // Debugging: Log the hashed password and input password
            error_log("Hashed password from DB: " . $hashedPassword);
            error_log("Input password: " . $password);

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Start session and set session variables
                session_start();
                $_SESSION['email'] = $email;
                $_SESSION['admin_id'] = $row['id']; // Assuming there's an ID column for admins

                // Redirect to the dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Debugging: Log password verification failure
                error_log("Password verification failed for email: " . $email);
                echo "<script>alert('Incorrect email or password!');</script>";
            }
        } else {
            // Debugging: Log email not found
            error_log("Email not found: " . $email);
            echo "<script>alert('Incorrect email or password!');</script>";
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "<script>alert('An error occurred. Please try again later.');</script>";
>>>>>>> 0003e1ed493a19b28403b75ca385eb70342caa11
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
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
                                    <?php if (!empty($error_message)): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?= htmlspecialchars($error_message); ?>
                                        </div>
                                    <?php endif; ?>
                                    <form class="user" method="POST" action="login.php">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user" name="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password" id="exampleInputPassword" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
