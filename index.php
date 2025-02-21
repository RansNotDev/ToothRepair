<?php
require_once './database/db_connection.php';

// Fetch services from database
$stmt = $conn->prepare("SELECT service_id, service_name, description, price, image FROM services WHERE is_active = 1");
$stmt->execute();
$result = $stmt->get_result();
$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tooth Repair</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/breakpoints.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/landing_page.css">
    
</head>

<body>
    <?php include 'headerlanding.php'; ?>
    <?php include 'navigation.php'; ?>

    <main>
        <section class="hero-section hero-section-full-height d-flex justify-content-center align-items-center">
            <div class="section-overlay"></div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 col-12 text-center mx-auto">
                        <h1 class="cd-headline rotate-1 text-dark mb-4 pb-2">
                            <span class="hero-text">Let's Brighten Your</span>
                            <span class="cd-words-wrapper">
                                <b class="is-visible">Smile</b>
                                <b>Confidence</b>
                                <b>Day</b>
                            </span>
                        </h1>
                        <a class="custom-btn btn button button--atlas smoothscroll me-3" href="#intro-section">
                            <span>About Us</span>
                            <div class="marquee" aria-hidden="true">
                                <div class="marquee__inner">
                                    <span>About Us</span>
                                    <span>About Us</span>
                                    <span>About Us</span>
                                    <span>About Us</span>
                                </div>
                            </div>
                        </a>
                        <a class="custom-btn custom-border-btn custom-btn-bg-white btn button button--pan smoothscroll" href="#services-section">
                            <span>Explore Services</span>
                        </a>
                    </div>
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,224L40,229.3C80,235,160,245,240,250.7C320,256,400,256,480,240C560,224,640,192,720,176C800,160,880,160,960,138.7C1040,117,1120,75,1200,80C1280,85,1360,139,1400,165.3L1440,192L1440,320L1440,320L1400,320C1360,320,1280,320,1200,320C1120,320,1040,320,960,320C880,320,800,320,720,320C640,320,560,320,480,320C400,320,320,320,240,320C160,320,80,320,40,320L0,320Z"></path>
            </svg>
        </section>

        <section class="intro-section" id="intro-section">
            <div class="container">
                <div class="row justify-content-lg-center align-items-center">
                    <div class="col-lg-6 col-12">
                        <h2 class="mb-4 text-primary">Trusted &amp; Professional Dental Care</h2>
                        <p><a href="#">Tooth Repair Dental Clinic</a> is committed to providing high-quality and affordable dental care in Malasiqui, Pangasinan. Our clinic offers a comfortable and professional environment to enhance patient experience.</p>
                        <p>We prioritize modern dental techniques and patient-centered care to ensure a safe and effective treatment for everyone. Please <a href="mailto:toothrepairdentalclinic@gmail.com">contact us</a> for more information or to book an appointment. Thank you.</p>
                    </div>
                    <div class="col-lg-6 col-12 custom-block-wrap">
                        <img src="img/dentist_teeth.png" class="img-fluid" alt="Dental Care Team">
                        <div class="custom-block d-flex flex-column">
                            <h6 class="text-white mb-3">Need an Appointment? <br> Call us now:</h6>
                            <p class="d-flex mb-0">
                                <i class="bi-telephone-fill custom-icon me-2"></i>
                                <a href="tel:+639123456789">+63 981-261-4001</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="services-section section-padding section-bg" id="services-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-12 col-12">
                        <h2 class="mb-4 text-primary">Our Services</h2>
                    </div>
                    <?php foreach ($services as $service): ?>
                        <div class="col-lg-6 col-12">
                            <div class="services-thumb">
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-12">
                                        <div class="services-image-wrap">
                                            <a href="landing_services.php">
                                                <?php if (!empty($service['image'])): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" class="services-image img-fluid" alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" class="services-image services-image-hover img-fluid" alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                                                <?php else: ?>
                                                    <img src="images/services/default-service.jpg" class="services-image img-fluid" alt="Default service image">
                                                    <img src="images/services/default-service.jpg" class="services-image services-image-hover img-fluid" alt="Default service image">
                                                <?php endif; ?>
                                                <div class="services-icon-wrap">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <p class="text-white mb-0">
                                                            <i class="bi-cash me-2"></i>
                                                            ₱<?php echo htmlspecialchars($service['price']); ?>
                                                        </p>
                                                        <p class="text-white mb-0">
                                                            <i class="bi-clock-fill me-2"></i>
                                                            30 min
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-lg-7 col-md-7 col-12 d-flex align-items-center">
                                        <div class="services-info mt-4 mt-lg-0 mt-md-0">
                                            <h4 class="services-title mb-1 mb-lg-2">
                                                <a class="services-title-link" href="landing_services.php">
                                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                                </a>
                                            </h4>
                                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                                            <div class="d-flex flex-wrap align-items-center">
                                                <div class="reviews-icons">
                                                    <i class="bi-star-fill"></i>
                                                    <i class="bi-star-fill"></i>
                                                    <i class="bi-star-fill"></i>
                                                    <i class="bi-star-fill"></i>
                                                    <i class="bi-star-fill"></i>
                                                </div>
                                                <a href="landing_services.php" class="custom-btn btn button button--atlas mt-2 ms-auto">
                                                    <span>Learn More</span>
                                                    <div class="marquee" aria-hidden="true">
                                                        <div class="marquee__inner">
                                                            <span>Learn More</span>
                                                            <span>Learn More</span>
                                                            <span>Learn More</span>
                                                            <span>Learn More</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php include 'feedbacks.php'; ?>
    </main>

    <?php include 'sitefooter.php'; ?>

    <!-- JAVASCRIPT FILES <script src="js/jquery.backstretch.min.js"></script>-->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/counter.js"></script>
    <script src="js/countdown.js"></script>
    <script src="js/init.js"></script>
    <script src="js/modernizr.js"></script>
    <script src="js/animated-headline.js"></script>
    <script src="js/custom.js"></script>
</body>

</html>