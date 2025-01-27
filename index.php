<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <title>Tooth Repair Clinic | Landing Page</title>

    <style>
        /* Add your custom styles here */
        .navbar {
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-right: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            color: black;
        }

        .button-style {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .home .btn {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
        }

        .home .btn:hover {
            background-color: rgb(31, 102, 179);
            color: white;
        }

        .home {
            background-image: url('assets/images/hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 5rem 0;
        }

        .content {
            max-width: 800px;
            margin: 0 auto;
        }

        .services {
            text-align: center;
            padding: 5rem 0;
        }

        .services h2 {
            margin-bottom: 2rem;
        }

        .service {
            margin-bottom: 2rem;
        }

        .about {
            text-align: center;
            padding: 5rem 0;
        }

        .testimonials {
            text-align: center;
            padding: 5rem 0;
        }

        .testimonial-carousel {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            gap: 1rem;
            padding: 1rem;
        }

        .testimonial-card {
            flex: 0 0 300px;
            scroll-snap-align: start;
            padding: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .contact {
            text-align: center;
            padding: 5rem 0;
        }
    </style>
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
            <li><a href="./pages/loginpage.php" class="button-style">Log In</a></li>
        </ul>
    </nav>
    <!-- Home Section -->
    <section id="home" class="home" role="banner" aria-label="Tooth Repair Clinic Welcome Section">
        <div class="content">
            <h2>Welcome to Tooth Repair Clinic</h2>
            <p>Get your teeth fixed by the best dentists in town.</p>
            <a href="#contact" class="btn" data-toggle="modal" data-target="#appointmentModal">Book an Appointment</a>
        </div>
    </section>


    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Book an Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Add your appointment booking form here -->
                            <form action="save_appointment.php" method="POST">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="fullname" placeholder="Enter your name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="contact_number" placeholder="Enter your phone number">
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea> 
                                </div>
                                <div class="form-group">
                                    <label for="date">Appointment Date</label>
                                    <input type="date" class="form-control" id="date" name="appointment_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="time">Appointment Time</label>
                                    <input type="time" class="form-control" id="time" name="appointment_time" required>
                                </div>
                                <div class="form-group">
                                    <label for="service">Select Service</label>
                                    <select class="form-control" id="service" name="service" required>
                                        <option>Teeth Whitening</option>
                                        <option>Teeth Cleaning</option>
                                        <option>Teeth Filling</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                                </div>
                                <div class="form-group d-flex justify-content-left">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms">
                                        <label class="form-check-label" for="terms">I agree to the <b class="text-primary">terms and conditions</b> of Tooth Repair Dental Clinic</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


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
        <p>We are a team of experienced dentists who are dedicated to providing the best dental care to our patients.
        </p>
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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