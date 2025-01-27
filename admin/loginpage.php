    <?php
    require_once '../database/db_connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];

        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $hashedPassword = $row['password'];

                if (password_verify($password, $hashedPassword) || md5($password) === $hashedPassword) {
                    // Authentication successful
                    session_start();
                    $_SESSION["email"] = $email;
                    echo "<script>
                            console.log('Login successful, redirecting...');
                            window.location.href = 'dashboard.php'; 
                        </script>"; 
                } else {
                    // Incorrect password
                    echo "<script>
                            console.log('Incorrect password');
                            alert('Incorrect email or password!'); 
                        </script>";
                }
            } else {
                // Email not found
                echo "<script>
                        console.log('Email not found');
                        alert('Incorrect email or password!'); 
                    </script>";
            }

        } catch(Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Page | Tooth Repair Clinic</title>
        <link rel="stylesheet" href="../assets/css/loginpage.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </head>
    <body>
        <div class="container">
            <div class="col">
                <h1>Welcome Back</h1>
                <img src="../images/loginpage.png" alt="Login Image" class="hero-image">
                <p>Log in to your account to access all the features and services we provide.</p>
            </div>

            <div class="col">
                <div class="card">
                    <h3>Login</h3>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-outline">
                            <input type="email" id="email" name="email" required />
                            <label for="email">Email Address</label>
                        </div>
                        <div class="form-outline">
                            <input type="password" id="password" name="password" required />
                            <label for="password">Password</label>
                        </div>
                        <button type="submit">Login</button>
                        <p class="text-center">Don’t have an account? <a href="registerpage.php">Register here</a></p>
                    </form>
                </div>
            </div>
        </div>
        <script src="../assets/Assetscalendar/sweetalert2/sweetalert2.min.js"></script>
    </body>
    </html>