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
            <p>Log in to your account to access all the features and services we provide.</p>
        </div>

        <!-- Login Form -->
        <div class="col">
            <div class="card">
                <h3>Login</h3>
                <form>
                    <div class="form-outline">
                        <input type="email" id="loginEmail" required />
                        <label for="loginEmail">Email Address</label>
                    </div>
                    <div class="form-outline">
                        <input type="password" id="loginPassword" required />
                        <label for="loginPassword">Password</label>
                    </div>
                    <button type="submit">Login</button>
                    <p class="text-center">Don’t have an account? <a href="registerpage.php">Register here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
