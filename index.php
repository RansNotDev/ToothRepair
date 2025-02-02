<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <title>Tooth Repair Clinic | Landing Page</title>
    <style>
        .navbar {
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            background-color: #007bff;

        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-right: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: 900px;
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
        .home .btn:active,  .home .btn:focus,  .home .btn:hover {
            background-color: rgb(31, 102, 179);
            color: white;
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

        /* Service cards hover effect */ 
        .services-carousel {
        position: relative;
        max-width: 1200px;
        margin: 2rem auto;
        overflow: hidden;
    }

    .service-slide {
        position: absolute;
        width: 100%;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }

    .service-slide.active-slide {
        opacity: 1;
        position: relative;
    }

    .service-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .service-image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .service-content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .carousel-control {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,123,255,0.8);
        color: white;
        border: none;
        padding: 1rem;
        cursor: pointer;
        z-index: 10;
    }

    .carousel-control.prev { left: 0; }
    .carousel-control.next { right: 0; }
        /* Add animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-fadeIn {
            animation: fadeIn 1s ease-in;
        }

        .animate-slideUp {
            animation: slideUp 0.8s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 1rem;
            }

            .nav-links li {
                margin: 0.5rem;
            }

            .testimonial-carousel {
                flex-direction: column;
                overflow-x: hidden;
            }

            .testimonial-card {
                flex: 0 0 auto;
                width: 90%;
                margin: 0 auto 1rem;
            }
        }

    @media (max-width: 768px) {
        .service-card {
            grid-template-columns: 1fr;
        }

        .service-image img {
            height: 250px;
        }

        .carousel-control {
            padding: 0.5rem;
        }
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
        position: relative;
        height: 300px;
        overflow: hidden;
    }

    .testimonial-slide {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        padding: 1rem;
    }

    .testimonial-slide.active-slide {
        opacity: 1;
    }

    .testimonial-card {
        background: white;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Add indicator dots */
    .carousel-indicators {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
    }

    .carousel-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ddd;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .carousel-indicator.active {
        background: #007bff;
    }

    @media (max-width: 768px) {
        .testimonial-carousel {
            height: 400px;
        }
        
        .testimonial-slide {
            padding: 0.5rem;
        }
    }

        .contact {
            text-align: center;
            padding: 5rem 0;
        }
        /* Contact section enhancements */
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .contact-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        body {
            padding-top: 56px; /* Height of navbar */
        }

        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #007bff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            background: rgb(31, 102, 179);
            transform: translateY(-3px);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar fixed-top">
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
            <a href="calendar.php" class="btn">Book an Appointment</a>
        </div>
    </section>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel"
        aria-hidden="true">
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
                                    <input type="text" class="form-control" id="name" name="fullname"
                                        placeholder="Enter your name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter your email" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="contact_number"
                                        placeholder="Enter your phone number">
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"
                                        required></textarea>
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
                                        <label class="form-check-label" for="terms">I agree to the <b
                                                class="text-primary">terms and conditions</b> of Tooth Repair Dental
                                            Clinic</label>
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


    <section id="services" class="services">
        <div class="container">
            <h2 class="animate-slideUp">Comprehensive Dental Care</h2>
            <p class="lead animate-fadeIn">Experience advanced dental treatments with cutting-edge technology.</p>

            <div class="row mt-5">
                <div class="col-md-4 mb-4">
                    <div class="service-card animate-slideUp" style="animation-delay: 0.2s">
                        <i class="fas fa-tooth service-icon"></i>
                        <h3>Professional Whitening</h3>
                        <p>Advanced laser whitening treatment that removes years of stains in just one 60-minute
                            session.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card animate-slideUp" style="animation-delay: 0.4s">
                        <i class="fas fa-teeth-open service-icon"></i>
                        <h3>Dental Implants</h3>
                        <p>Permanent tooth replacement solutions that look, feel, and function like natural teeth.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card animate-slideUp" style="animation-delay: 0.6s">
                        <i class="fas fa-toothbrush service-icon"></i>
                        <h3>Preventive Care</h3>
                        <p>Complete preventive package including cleaning, fluoride treatment, and oral cancer
                            screening.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Enhanced About Section -->
    <section id="about" class="about bg-light">
        <div class="container">
            <h2 class="animate-slideUp">Why Choose Us?</h2>
            <div class="row mt-4">
                <div class="col-md-6 animate-fadeIn">
                    <img src="assets/images/clinic-interior.jpg" alt="Clinic Interior"
                        class="img-fluid rounded-lg mb-4">
                </div>
                <div class="col-md-6 animate-slideUp">
                    <h3>25 Years of Excellence</h3>
                    <p class="lead">Combining advanced technology with compassionate care</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-primary mr-2"></i>ADA-certified dental
                            professionals</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-primary mr-2"></i>100% sterilization
                            guarantee</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-primary mr-2"></i>Emergency dental services
                            available</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-primary mr-2"></i>Flexible payment options
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Updated Testimonials Section -->
<section id="testimonials" class="testimonials bg-light">
    <div class="container">
        <h2>Patient Experiences</h2>
        <p class="lead mb-5">Hear from those who've transformed their smiles</p>
        
        <div class="testimonial-carousel">
            <!-- Slides -->
            <div class="testimonial-slide active-slide">
                <div class="testimonial-card">
                    <p class="mb-3">"The entire team made me feel comfortable from start to finish. My dental implants look completely natural!"</p>
                    <h4 class="text-primary">Sarah Johnson</h4>
                    <span class="text-muted">Dental Implant Patient</span>
                </div>
            </div>
            
            <div class="testimonial-slide">
                <div class="testimonial-card">
                    <p class="mb-3">"Finally found a clinic that explains every procedure clearly. The whitening results exceeded my expectations."</p>
                    <h4 class="text-primary">Michael Chen</h4>
                    <span class="text-muted">Teeth Whitening Patient</span>
                </div>
            </div>
            
            <div class="testimonial-slide">
                <div class="testimonial-card">
                    <p class="mb-3">"Emergency service saved me during a holiday weekend. Quick, professional care when I needed it most."</p>
                    <h4 class="text-primary">Emily Rodriguez</h4>
                    <span class="text-muted">Emergency Care Patient</span>
                </div>
            </div>
            
            <!-- Indicator Dots -->
            <div class="carousel-indicators"></div>
        </div>
    </div>
</section>

    <!-- Enhanced Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="animate-slideUp">Visit Our Clinic</h2>
            <div class="contact-info animate-fadeIn">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                    <h4>Location</h4>
                    <p>123 Dental Avenue<br>Health City, HC 4567</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock fa-2x mb-3"></i>
                    <h4>Working Hours</h4>
                    <p>Mon-Fri: 8am - 7pm<br>Saturday: 9am - 4pm</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt fa-2x mb-3"></i>
                    <h4>Contact</h4>
                    <p>(555) 123-4567<br>emergency@clinic.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js"></script>
   
    <!-- Add scroll-to-top button before closing body tag -->
    <button class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

</body>

</html>