<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net/npm">
    <link rel="preload" as="image" href="assets/images/hero.jpg">

    <title>Tooth Repair Clinic | Appointment System</title>
    <style>
        :root {
            --primary-color: #2C7BE5;
            --primary-hover: #1A68D1;
            --secondary-color: #6AD6A4;
            --accent-color: #FF6B6B;
            --text-color: #2D3748;
            --light-gray: #F8F9FA;
            --dark-gray: #4A5568;
            --white: #FFFFFF;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            padding-top: 0; /* Remove the top padding */
            margin: 0;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            background-color: var(--primary-color);
            position: fixed; /* Changed from absolute to fixed */
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar .nav-link,
        .navbar-brand {
            color: #fff !important;
            font-weight: 600;
        }

        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            margin-top: 0; /* Remove any top margin */
            padding-top: 76px; /* Add padding to account for navbar */
            background: linear-gradient(135deg, rgba(44,123,229,0.9), rgba(106,214,164,0.9)), url('assets/images/hero.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .hero .content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease;
        }

        .cta-group {
            margin-top: 2rem;
        }

        .btn-primary {
            border: none;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--overlay-color);
            z-index: 1;
        }

        
        
        /* Section Headings */
        h2.section-title {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        h2.section-title::after {
            content: '';
            display: block;
            height: 3px;
            width: 50%;
            background: var(--primary-color);
            margin: 0.5rem auto;
        }

        /* Service Cards */
        .service-card {
            transition: transform 0.3s, box-shadow 0.3s;
            transform: translateY(30px);
            opacity: 0;
            animation: slideUp 0.6s ease forwards;
            animation-delay: calc(var(--card-index) * 0.2s);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
        }

        /* Footer */
        footer {
            background: #333;
            color: #fff;
            padding: 2rem 0;
            text-align: center;
        }

        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
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
            background: var(--primary-hover);
            transform: translateY(-3px);
        }

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
            opacity: 0;
            animation: fadeIn 1s ease forwards;
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

        .floating-appointment-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 16px 32px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            transform: translateY(0);
            transition: var(--transition);
            z-index: 1000;
        }

        .floating-appointment-btn:hover {
            transform: translateY(-4px);
            background: var(--primary-hover);
        }

        .appointment-form {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .form-control {
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            padding: 12px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44,123,229,0.1);
        }

        .btn-primary {
            padding: 12px 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            text-transform: uppercase;
            transition: var(--transition);
        }

        /* Chatbot Styles */
        .chatbot {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
        }

        .chat-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            color: white;
            font-size: 24px;
        }

        .chat-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 300px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .chat-header {
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            height: 300px;
            overflow-y: auto;
            padding: 15px;
        }

        .bot-message, .user-message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 15px;
            max-width: 80%;
        }

        .bot-message {
            background: var(--light-gray);
            margin-right: auto;
        }

        .user-message {
            background: var(--primary-color);
            color: white;
            margin-left: auto;
        }

        .chat-input {
            display: flex;
            padding: 15px;
            border-top: 1px solid var(--light-gray);
        }

        .chat-input input {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--light-gray);
            border-radius: 20px;
            margin-right: 10px;
        }

        .chat-input button {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
        }

        /* Common section styles */
        section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 76px 0; /* Account for fixed navbar */
        }

        /* Hero Section specific updates */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(44,123,229,0.9), rgba(106,214,164,0.9)), url('assets/images/hero.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Services Section specific updates */
        #services {
            min-height: 100vh;
            background: var(--light-gray);
        }

        /* About Section specific updates */
        #about {
            min-height: 100vh;
            background: var(--white);
        }

        /* Testimonials Section specific updates */
        #testimonials {
            min-height: 100vh;
            background: var(--light-gray);
        }

        /* Contact Section specific updates */
        #contact {
            min-height: 100vh;
            background: var(--white);
        }

        /* Container adjustments for better centering */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            section {
                padding: 60px 0;
            }
            
            .container {
                padding: 0 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Tooth Repair Clinic</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#hero">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="pages/loginpage.php">Log In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-overlay"></div>
        <div class="content container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center animate-fadeIn">
                    <h1 class="display-3 fw-bold mb-4">Your Perfect Smile Starts Here</h1>
                    <p class="lead mb-4">Experience world-class dental care with our team of expert professionals</p>
                    <div class="cta-group">
                        <a href="calendar.php" class="btn btn-primary btn-lg px-5 py-3 me-3">Book Appointment</a>
                        <a href="#services" class="btn btn-outline-light btn-lg px-5 py-3">Our Services</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-0">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-6 text-center">
                    <h2 class="section-title">Our Services</h2>
                    <p class="lead text-muted">Comprehensive dental care tailored to your needs</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" style="--card-index: 0">
                    <div class="service-card h-100 rounded-4 p-4 bg-white">
                        <div class="service-icon mb-4 rounded-circle p-3 d-inline-block" style="background: var(--light-gray);">
                            <i class="fas fa-tooth fa-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Professional Whitening</h5>
                        <p class="text-muted mb-4">Advanced treatment that removes stubborn stains and brightens your smile.</p>
                        <a href="#" class="btn btn-link text-primary p-0 stretched-link">
                            Learn More 
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <!-- Add similar cards for other services -->
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-0">
        <div class="container">
            <h2 class="section-title">Why Choose Us?</h2>
            <div class="row align-items-center mt-4">
                <div class="col-md-6">
                    <img src="assets/images/clinic-interior.jpg" class="img-fluid rounded" alt="Clinic Interior">
                </div>
                <div class="col-md-6">
                    <h3>25 Years of Excellence</h3>
                    <p class="lead">State-of-the-art technology combined with a compassionate approach.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-primary me-2"></i>ADA-certified professionals</li>
                        <li><i class="fas fa-check text-primary me-2"></i>100% sterilization guarantee</li>
                        <li><i class="fas fa-check text-primary me-2"></i>Emergency dental services</li>
                        <li><i class="fas fa-check text-primary me-2"></i>Flexible payment options</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-0">
        <div class="container">
            <h2 class="section-title">Patient Experiences</h2>
            <div id="testimonialCarousel" class="carousel slide mt-4" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="card mx-auto" style="max-width: 600px;">
                            <div class="card-body">
                                <p class="card-text">"The entire team made me feel comfortable from start to finish. My dental implants look completely natural!"</p>
                                <h5 class="card-title text-primary">Sarah Johnson</h5>
                                <p class="card-subtitle text-muted">Dental Implant Patient</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 600px;">
                            <div class="card-body">
                                <p class="card-text">"Finally found a clinic that explains every procedure clearly. The whitening results exceeded my expectations."</p>
                                <h5 class="card-title text-primary">Michael Chen</h5>
                                <p class="card-subtitle text-muted">Teeth Whitening Patient</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="card mx-auto" style="max-width: 600px;">
                            <div class="card-body">
                                <p class="card-text">"Emergency service saved me during a holiday weekend. Quick, professional care when I needed it most."</p>
                                <h5 class="card-title text-primary">Emily Rodriguez</h5>
                                <p class="card-subtitle text-muted">Emergency Care Patient</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-0">
        <div class="container">
            <h2 class="section-title">Visit Our Clinic</h2>
            <div class="row text-center mt-4">
                <div class="col-md-4 mb-3">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
                    <h5>Location</h5>
                    <p>Poblacion Malasiqui<br>Pangasinan, PH 2421</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                    <h5>Working Hours</h5>
                    <p>Mon-Fri: 8am - 7pm<br>Saturday: 9am - 4pm</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-phone-alt fa-2x text-primary mb-2"></i>
                    <h5>Contact</h5>
                    <p>(555) 123-4567<br>emergency@clinic.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Book an Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="save_appointment.php" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="fullname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="contact_number">
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Appointment Date</label>
                                    <input type="date" class="form-control" id="date" name="appointment_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="time" class="form-label">Appointment Time</label>
                                    <input type="time" class="form-control" id="time" name="appointment_time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="service" class="form-label">Select Service</label>
                                    <select class="form-select" id="service" name="service" required>
                                        <option>Teeth Whitening</option>
                                        <option>Teeth Cleaning</option>
                                        <option>Teeth Filling</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms">
                                    <label class="form-check-label" for="terms">
                                        I agree to the <span class="text-primary">terms and conditions</span>
                                    </label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- Optional image or info panel side -->
                        <div class="col-md-6 d-none d-md-block">
                            <img src="assets/images/appointment.jpg" alt="Appointment" class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button type="button" class="btn btn-primary rounded-circle position-fixed" style="bottom:20px; right:20px; width:50px; height:50px;" id="scrollTopBtn">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y") ?> Tooth Repair Clinic. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll to Top Button functionality
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        window.addEventListener('scroll', () => {
            scrollTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
        });
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Chatbot functionality
        const chatbot = document.createElement('div');
        chatbot.classList.add('chatbot');
        chatbot.innerHTML = `
            <div class="chat-icon" id="chatIcon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="chat-window" id="chatWindow" style="display: none;">
                <div class="chat-header">
                    <h5>Tooth Repair Assistant</h5>
                    <button class="close-chat" id="closeChat">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="bot-message">Hello! How can I help you today?</div>
                </div>
                <div class="chat-input">
                    <input type="text" id="userInput" placeholder="Type your question...">
                    <button id="sendMessage">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(chatbot);

        // Add this JavaScript code after the existing script tags
        document.addEventListener('DOMContentLoaded', function() {
            const chatIcon = document.getElementById('chatIcon');
            const chatWindow = document.getElementById('chatWindow');
            const closeChat = document.getElementById('closeChat');
            const userInput = document.getElementById('userInput');
            const sendMessage = document.getElementById('sendMessage');
            const chatMessages = document.getElementById('chatMessages');

            chatIcon.addEventListener('click', () => {
                chatWindow.style.display = 'block';
            });

            closeChat.addEventListener('click', () => {
                chatWindow.style.display = 'none';
            });

            function handleUserMessage(message) {
                const userMsg = message.toLowerCase();
                let response;

                if (userMsg.includes('working hours') || userMsg.includes('open')) {
                    response = "We're open Monday-Friday: 8am - 7pm and Saturday: 9am - 4pm.";
                } else if (userMsg.includes('location') || userMsg.includes('address')) {
                    response = "We're located at Poblacion Malasiqui, Pangasinan, PH 2421";
                } else if (userMsg.includes('appointment') || userMsg.includes('book')) {
                    response = "To book an appointment, please click the Book Appointment button in the menu or call us at (555) 123-4567.";
                } else if (userMsg.includes('services')) {
                    response = "Our services include teeth whitening, cleaning, filling, and other dental procedures. Would you like specific information about any service?";
                } else if (userMsg.includes('emergency')) {
                    response = "For dental emergencies, please call our emergency hotline at (555) 123-4567. We provide 24/7 emergency services.";
                } else if (userMsg.includes('payment') || userMsg.includes('insurance')) {
                    response = "We accept most major insurance plans and offer flexible payment options. Please contact our office for specific details.";
                } else {
                    response = "I'm not sure about that. Please contact our office at (555) 123-4567 for more specific information.";
                }

                return response;
            }

            function addMessage(message, isUser = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = isUser ? 'user-message' : 'bot-message';
                messageDiv.textContent = message;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            sendMessage.addEventListener('click', () => {
                const message = userInput.value.trim();
                if (message) {
                    addMessage(message, true);
                    const response = handleUserMessage(message);
                    setTimeout(() => addMessage(response), 500);
                    userInput.value = '';
                }
            });

            userInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    sendMessage.click();
                }
            });
        });
    </script>
    <script>
        // Add shadow to navbar on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>