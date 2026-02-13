<?php
/**
 * Gouri Transport - Footer Component
 */

// Get settings for footer
$companyName = getSetting('company_name', 'Gouri Transport');
$contactAddress = getSetting('contact_address', '123 Transport Nagar, Mumbai, Maharashtra 400001');
$contactPhone = getSetting('contact_phone', '+91 1234567890');
$contactEmail = getSetting('contact_email', 'info@gouritransport.com');
$workingHours = getSetting('working_hours', 'Mon - Sat: 8:00 AM - 8:00 PM');

// Get active services for footer
$footerServices = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT title, slug FROM services WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
    $footerServices = $stmt->fetchAll();
} catch (Exception $e) {
    // Silent fail
}
?>
    </main><!-- End Main Content -->

    <!-- Footer -->
    <footer class="footer bg-dark text-white">
        <div class="container py-5">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand mb-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="logo-icon me-2 text-primary">
                                <i class="fas fa-truck-fast fa-lg"></i>
                            </div>
                            <span class="h5 fw-bold mb-0"><?php echo $companyName; ?></span>
                        </div>
                        <p class="text-white-50">
                            <?php echo getSetting('company_description', 'Delivering goods safely across cities and countries with modern fleet and professional service.'); ?>
                        </p>
                    </div>
                    <div class="social-links">
                        <a href="<?php echo getSetting('facebook_url', '#'); ?>" class="btn btn-outline-light btn-floating btn-sm me-2" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo getSetting('twitter_url', '#'); ?>" class="btn btn-outline-light btn-floating btn-sm me-2" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="<?php echo getSetting('instagram_url', '#'); ?>" class="btn btn-outline-light btn-floating btn-sm me-2" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo getSetting('linkedin_url', '#'); ?>" class="btn btn-outline-light btn-floating btn-sm" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/index.php" class="text-white-50 text-decoration-none hover-white">Home</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/services.php" class="text-white-50 text-decoration-none hover-white">Services</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/pricing.php" class="text-white-50 text-decoration-none hover-white">Pricing</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/track.php" class="text-white-50 text-decoration-none hover-white">Track Shipment</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/contact.php" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
                        <li class="mb-2"><a href="<?php echo APP_URL; ?>/booking.php" class="text-white-50 text-decoration-none hover-white">Book Now</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3">Services</h6>
                    <ul class="list-unstyled footer-links">
                        <?php if (!empty($footerServices)): ?>
                            <?php foreach ($footerServices as $service): ?>
                                <li class="mb-2">
                                    <a href="<?php echo APP_URL; ?>/services.php?slug=<?php echo $service['slug']; ?>" 
                                       class="text-white-50 text-decoration-none hover-white">
                                        <?php echo $service['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Road Transport</a></li>
                            <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Freight Services</a></li>
                            <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Warehousing</a></li>
                            <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Express Delivery</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled footer-contact">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-map-marker-alt me-3 mt-1 text-primary"></i>
                            <span class="text-white-50"><?php echo $contactAddress; ?></span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-phone me-3 mt-1 text-primary"></i>
                            <a href="tel:<?php echo $contactPhone; ?>" class="text-white-50 text-decoration-none hover-white"><?php echo $contactPhone; ?></a>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-envelope me-3 mt-1 text-primary"></i>
                            <a href="mailto:<?php echo $contactEmail; ?>" class="text-white-50 text-decoration-none hover-white"><?php echo $contactEmail; ?></a>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-clock me-3 mt-1 text-primary"></i>
                            <span class="text-white-50"><?php echo $workingHours; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom bg-black py-3">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <p class="mb-2 mb-md-0 text-white-50 small">
                        &copy; <?php echo date('Y'); ?> <?php echo $companyName; ?>. All Rights Reserved.
                    </p>
                    <div class="d-flex gap-3 small">
                        <a href="#" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a>
                        <a href="#" class="text-white-50 text-decoration-none hover-white">Terms of Service</a>
                        <a href="#" class="text-white-50 text-decoration-none hover-white">FAQ</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav d-lg-none">
        <a href="<?php echo APP_URL; ?>/index.php" class="mobile-nav-item <?php echo $currentPage === 'home' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo APP_URL; ?>/services.php" class="mobile-nav-item <?php echo $currentPage === 'services' ? 'active' : ''; ?>">
            <i class="fas fa-cubes"></i>
            <span>Services</span>
        </a>
        <a href="<?php echo APP_URL; ?>/booking.php" class="mobile-nav-item mobile-nav-primary <?php echo $currentPage === 'booking' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Book</span>
        </a>
        <a href="<?php echo APP_URL; ?>/contact.php" class="mobile-nav-item <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
            <i class="fas fa-phone"></i>
            <span>Contact</span>
        </a>
    </nav>

    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- MDBootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    
    <script>
        // Theme Toggle
        document.getElementById('themeToggle')?.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-mdb-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-mdb-theme', newTheme);
            document.body.classList.remove(currentTheme + '-mode');
            document.body.classList.add(newTheme + '-mode');
            
            // Update icon
            this.querySelector('i').className = 'fas fa-' + (newTheme === 'dark' ? 'sun' : 'moon');
            
            // Save preference
            document.cookie = 'theme=' + newTheme + ';path=/;max-age=31536000';
        });
        
        // Back to Top Button
        const backToTopBtn = document.getElementById('btn-back-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    
    <?php if (isset($extraJs)): ?>
        <?php echo $extraJs; ?>
    <?php endif; ?>
</body>
</html>
