<footer class="site-footer">
            <div class="container">
                <div class="row">

                    <div class="col-lg-12 col-12 d-flex align-items-center mb-4 pb-2">
                        

                        <ul class="footer-menu d-flex flex-wrap ms-5">
                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">About Us</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Blog</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Reviews</a></li>

                            <li class="footer-menu-item"><a href="#" class="footer-menu-link">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-5 col-12 mb-4 mb-lg-0">
                        <h5 class="site-footer-title mb-3">Our Services</h5>

                        <ul class="footer-menu">
                            <?php
                            // Include database connection if not already included
                            include_once('database/db_connection.php');
                            
                            // Fetch services from database
                            $query = "SELECT service_id, service_name FROM services ORDER BY service_name ASC";
                            $result = mysqli_query($conn, $query);

                            // Check if query was successful
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<li class="footer-menu-item">
                                            <a href="#" class="footer-menu-link">
                                                <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                                ' . htmlspecialchars($row['service_name']) . '
                                            </a>
                                        </li>';
                                }
                            } else {
                                // Fallback in case no services are found
                                echo '<li class="footer-menu-item">
                                        <a href="#" class="footer-menu-link">
                                            <i class="bi-chevron-double-right footer-menu-link-icon me-2"></i>
                                            No services available
                                        </a>
                                    </li>';
                            }

                            // Free result set
                            mysqli_free_result($result);
                            ?>
                        </ul>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 mb-4 mb-lg-0 mb-md-0">
                        <h5 class="site-footer-title mb-3">Clinic Location</h5>

                        <p class="text-white d-flex mt-3 mb-2">
                            <i class="bi-geo-alt-fill me-2"></i>
                            Ground Floor, Navarro Building, Poblacion, Malasiqui, Pangasinan
                        </p>

                        <p class="text-white d-flex mb-2">
                            <i class="bi-telephone-fill me-2"></i>

                            <a href="tel: 110-220-9800" class="site-footer-link">
                                0981-261-4001
                            </a>
                        </p>

                        <p class="text-white d-flex">
                            <i class="bi-envelope-fill me-2"></i>

                            <a href="mailto:info@company.com" class="site-footer-link">
                                toothrepairdentalclinic@gmail.com
                            </a>
                        </p>

                        <ul class="social-icons-list mt-4 d-flex gap-3">
                            <li>
                                <a href="#" class="social-link facebook" aria-label="Facebook">
                                    <i class="bi-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-link instagram" aria-label="Instagram">
                                    <i class="bi-instagram"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-link tiktok" aria-label="TikTok">
                                    <i class="bi-tiktok"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-link linkedin" aria-label="LinkedIn">
                                    <i class="bi-linkedin"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6 mt-3 mt-lg-0 mt-md-0">
                        <div class="featured-block">
                            <h5 class="text-black mb-3">Service Hours</h5>

                            <strong class="d-block text-white mb-1">Monday - Friday</strong>

                            <p class="text-white mb-3">7:00 AM - 5:00 PM</p>

                            <strong class="d-block text-white mb-1">Saturday</strong>

                            <p class="text-white mb-0">8:00 AM - 5:00 PM</p>
<br>
                            <strong class="d-block text-white mb-1">Sunday</strong>

                            <p class="text-white mb-0">Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="site-footer-bottom">
                <div class="container">
                    <div class="row">

                        <div class="col-lg-6 col-12">
                            <p class="copyright-text mb-0">Copyright © <?php echo date("Y") ?> Tooth Repair Dental Clinic</p>
                        </div>
                        
                        <div class="col-lg-6 col-12 text-end">
                            <p class="copyright-text mb-0">
                            // Designed by <a href="#" target="_parent">Not Just Rans</a> //</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </footer>