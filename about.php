<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="">
        <meta name="author" content="">

        <title>About Us | Tooth Repair Dental Clinic</title>
        <!-- CSS FILES -->        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">
        <link href="css/landing_page.css" rel="stylesheet">
        <link rel="stylesheet" href="css/components/about.css">

    </head>
    
    <body>
    <?php include 'headerlanding.php'; ?>
        <?php include 'navigation.php'; ?>

        <main>

            <section class="banner-section d-flex justify-content-center align-items-end">
                <div class="section-overlay"></div>

                <div class="container">
                    <div class="row">

                        <div class="col-lg-7 col-12">
                            <h1 class="text-white mb-lg-0">About Us</h1>
                        </div>

                        <div class="col-lg-4 col-12 d-flex justify-content-lg-end align-items-center ms-auto">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>

                                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                </div>
            </section>
         

            <section class="section-padding">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-12">
                            <div class="about-image-wrap">
                                <img src="img/dentist_person.jpg" 
                                     class="featured-image img-fluid" 
                                     alt="Dental Professional at Tooth Repair Dental Clinic"
                                     loading="lazy">
                            </div>
                        </div>

                        <div class="col-lg-6 col-12">
                            <div class="featured-block">
                                <h2 class="mb-4 text-primary">Professional Dental Care Since 2015</h2>

                                <p class="mb-3">Welcome to Tooth Repair Dental Clinic, your trusted partner in dental health. We are committed to providing exceptional dental care services with a focus on patient comfort and satisfaction.</p>

                                <ul class="list-unstyled mb-4">
                                    <li class="d-flex align-items-center mb-3">
                                        <i class="bi-check-circle-fill text-primary me-2"></i>
                                        <span>Modern Dental Equipment</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3">
                                        <i class="bi-check-circle-fill text-primary me-2"></i>
                                        <span>Experienced Dental Professionals</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3">
                                        <i class="bi-check-circle-fill text-primary me-2"></i>
                                        <span>Comfortable and Relaxing Environment</span>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="bi-check-circle-fill text-primary me-2"></i>
                                        <span>Comprehensive Dental Services</span>
                                    </li>
                                </ul>

                                <a class="custom-btn btn button button--atlas mt-3" href="contact.php">
                                    <span>Get in touch</span>
                                    <div class="marquee" aria-hidden="true">
                                        <div class="marquee__inner">
                                            <span>Get in touch</span>
                                            <span>Get in touch</span>
                                            <span>Get in touch</span>
                                            <span>Get in touch</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <section class="team-section section-padding section-bg">
                <div class="container">
                    <div class="row">

                        <div class="col-lg-12 col-12">
                            <h2 class="mb-4">Meet People</h2>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-lg-0 mb-md-5">
                            <img src="images/teams/young-cleaning-man-wearing-casual-clothes.jpg" class="team-image img-fluid">
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 d-flex justify-content-lg-center mt-4 mt-lg-0 mt-md-0 mb-5 mb-lg-0">
                            <div class="team-info mx-auto mx-lg-0">
                                <h4 class="mb-2">Josh</h4>

                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing tempor incididunt dolore magna</p>

                                <div class="border-top mt-3 pt-3">
                                    <p class="d-flex mb-0">
                                        <i class="bi-whatsapp me-2"></i>

                                        <a href="tel: 110-220-9800">
                                            110-220-9800
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <img src="images/teams/happy-young-woman-wiping-kitchen-counter-wearing-yellow-gloves.jpg" class="team-image img-fluid">
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 d-flex justify-content-lg-center mt-4 mt-lg-0 mt-md-0">
                            <div class="team-info mx-auto mx-lg-0">
                                <h4 class="mb-2">Marie</h4>

                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing tempor incididunt dolore magna</p>

                                <div class="border-top mt-3 pt-3">
                                    <p class="d-flex mb-0">
                                        <i class="bi-whatsapp me-2"></i>

                                        <a href="tel: 110-220-9800">
                                            110-220-9800
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </main>

        <?php include 'sitefooter.php'; ?>

        <!-- JAVASCRIPT FILES -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.backstretch.min.js"></script>
        <script src="js/counter.js"></script>
        <script src="js/countdown.js"></script>
        <script src="js/init.js"></script>
        <script src="js/modernizr.js"></script>
        <script src="js/animated-headline.js"></script>
  

    </body>
</html>
