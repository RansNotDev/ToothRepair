<?php
            require_once './database/db_connection.php';

            // Fetch services from database
            $stmt = $conn->prepare("SELECT service_id, service_name, description, price, image FROM services");
            $stmt->execute();
            $result = $stmt->get_result();
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="">
        <meta name="author" content="">

        <title>Services | Tooth Repair Dental Clinic</title>

        <!-- CSS FILES -->        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">
        <link href="css/landing_page.css" rel="stylesheet">

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
                            <h1 class="mb-lg-0" style="color: white;">Services Listing</h1>
                        </div>

                        <div class="col-lg-4 col-12 d-flex justify-content-lg-end align-items-center ms-auto">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>

                                    <li class="breadcrumb-item active" aria-current="page">Services Listing</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                </div>
            </section>


            <section class="services-section section-padding section-bg" id="services-section">                
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <h2 class="mb-4" style="color: #0d6efd;">Our Services</h2>
                        </div>

                        <?php foreach ($services as $service): ?>
                        <div class="col-lg-6 col-12">
                            <div class="services-thumb">
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-12">
                                        <div class="services-image-wrap">
                                            <a href="#">
                                                <?php if (!empty($service['image'])): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" 
                                                         class="services-image img-fluid" 
                                                         alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" 
                                                         class="services-image services-image-hover img-fluid" 
                                                         alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                                                <?php else: ?>
                                                    <img src="images/services/default-service.jpg" 
                                                         class="services-image img-fluid" 
                                                         alt="Default service image">
                                                    <img src="images/services/default-service.jpg" 
                                                         class="services-image services-image-hover img-fluid" 
                                                         alt="Default service image">
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
                                                <a class="services-title-link" href="services-detail.html">
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

                                                <a href="landing_services.php" class="custom-btn btn button button--atlas mt-2 ms-auto bg-primary">
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
