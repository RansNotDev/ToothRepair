<?php
require_once "./database/db_connection.php"; 

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE email = ? AND password = PASSWORD(?)"; 
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_username'] = $user['username']; 
            $_SESSION['user_email'] = $user['email'];

            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful!',
                'redirect' => 'users/userdashboard.php' 
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database query error. Please try again.']);
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <title>Tooth Repair Clinic | Landing Page</title>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="logo">
        <h4>Tooth Repair Clinic</h4>
    </div>
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#testimonials">Testimonials</a></li>
        <li><a href="#contact">Contact</a></li>
        <!-- Use an <a> tag styled as a button -->
        <li><a href="loginpage.php" class="button-style">Log In</a></li>
    </ul>
</nav>


<!-- Home Section -->
<section id="home" class="home" role="banner" aria-label="Tooth Repair Clinic Welcome Section">
    <div class="content">
        <h2>Welcome to Tooth Repair Clinic</h2>
        <p>Get your teeth fixed by the best dentists in town.</p>
        <a href="#contact" class="btn">Book an Appointment</a>
    </div>
</section> 

    <!-- Services Section -->
    <section id="services" class="services">
        <h2>Our Services</h2>
        <p>Here are some of the services we offer:</p>

        <div class="row">
            <div class="service">
                <h3>Teeth Whitening</h3>
                <p>Get your teeth whitened by our experts.</p>
            </div>
            <div class="service">
                <h3>Teeth Cleaning</h3>
                <p>Get your teeth cleaned by our experts.</p>
            </div>
            <div class="service">
                <h3>Teeth Filling</h3>
                <p>Get your teeth filled by our experts.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <h2>About Us</h2>
        <p>We are a team of experienced dentists who are dedicated to providing the best dental care to our patients.</p>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <h2>What Our Patients Say</h2>
        <div class="testimonial-carousel">
            <div class="testimonial-card">
                <p>"The best dental service I have ever experienced! Highly recommend."</p>
                <h4>- Jane Doe</h4>
            </div>
            <div class="testimonial-card">
                <p>"Professional and friendly staff. My teeth look amazing now!"</p>
                <h4>- John Smith</h4>
            </div>
            <div class="testimonial-card">
                <p>"The clinic is clean, and the service is top-notch. Thank you!"</p>
                <h4>- Mary Johnson</h4>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <h2>Contact Us</h2>
        <p>Feel free to contact us for any queries or to book an appointment.</p>
        <a href="#" class="btn">Contact Us</a>
    </section>

    <script>
        // Simple carousel functionality (optional for testimonial cards)
        const carousel = document.querySelector('.testimonial-carousel');
        let scrollAmount = 0;

        setInterval(() => {
            scrollAmount += 1;
            if (scrollAmount >= carousel.scrollWidth) {
                scrollAmount = 0;
            }
            carousel.scroll({ left: scrollAmount, behavior: 'smooth' });
        }, 3000);
    </script>
</body>
</html>