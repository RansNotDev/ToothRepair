<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Register a new account to access all the features and services we provide.">
    <meta name="keywords" content="tooth repair clinic, register, account, services">
    <meta name="author" content="Tooth Repair Clinic">
    
    <title>Register Page | Tooth Repair Clinic</title>
    <link rel="stylesheet" href="../assets/css/registerpage.css">
</head>
<body>
    <div class="container">
        <!-- Left Content -->
        <div class="col">
            <h1>Join Us</h1>
            <img src="../images/regimage.png" alt="Register Image" class="hero-image">
            <p>Create a new account to access all the features and services we provide.</p>
        </div>

        <!-- Register Form -->
        <div class="col">
            <div class="card">
                <h3>Register</h3>
                <form>
                    <div class="form-outline">
                        <input type="text" id="registerName" required />
                        <label for="registerName">Full Name</label>
                    </div>
                    <div class="form-outline">
                        <input type="email" id="registerEmail" required />
                        <label for="registerEmail">Email Address</label>
                    </div>
                    <div class="form-outline">
                        <input type="password" id="registerPassword" required />
                        <label for="registerPassword">Password</label>
                    </div>
                    <button type="submit">Register</button>
                    <p class="text-center">Already have an account? <a href="loginpage.php">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>