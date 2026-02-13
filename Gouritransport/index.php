<?php
/**
 * Gouri Transport - Homepage
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Fast, Reliable & Secure Transport Services';

// Get services
$services = [];
$testimonials = [];
$faqs = [];

try {
    $db = getDB();
    
    // Get active services
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
    $services = $stmt->fetchAll();
    
    // Get testimonials
    $stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
    $testimonials = $stmt->fetchAll();
    
    // Get FAQs
    $stmt = $db->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
    $faqs = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error fetching homepage data: " . $e->getMessage());
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content">
                    <h1 class="hero-title">Fast, Reliable & Secure Transport Services</h1>
                    <p class="hero-subtitle">Delivering goods safely across cities and countries with our modern fleet and professional logistics team.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-light btn-lg btn-rounded px-4">
                            <i class="fas fa-calendar-check me-2"></i>Book a Transport
                        </a>
                        <a href="<?php echo APP_URL; ?>/services.php" class="btn btn-outline-light btn-lg btn-rounded px-4">
                            <i class="fas fa-cubes me-2"></i>Our Services
                        </a>
                    </div>
                    
                    <!-- Hero Stats -->
                    <div class="hero-stats">
                        <div class="row">
                            <div class="col-4">
                                <div class="stat-item">
                                    <span class="stat-number">500+</span>
                                    <span class="stat-label">Happy Clients</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <span class="stat-number">1000+</span>
                                    <span class="stat-label">Deliveries</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <span class="stat-number">50+</span>
                                    <span class="stat-label">Vehicles</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo ASSETS_URL; ?>/images/hero-truck.png" alt="Transport Truck" class="img-fluid" 
                         onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063196.png'" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Comprehensive transport and logistics solutions tailored to meet your business needs</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-<?php echo $service['icon']; ?>"></i>
                            </div>
                            <h3 class="service-title"><?php echo sanitize($service['title']); ?></h3>
                            <p class="service-desc"><?php echo sanitize($service['short_description'] ?: truncateText($service['description'], 100)); ?></p>
                            <a href="<?php echo APP_URL; ?>/services.php?slug=<?php echo $service['slug']; ?>" class="btn btn-outline-primary btn-sm">
                                Learn More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Services -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-truck"></i></div>
                        <h3 class="service-title">Road Transport</h3>
                        <p class="service-desc">Reliable road freight services across the country with GPS tracking.</p>
                        <a href="<?php echo APP_URL; ?>/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-shipping-fast"></i></div>
                        <h3 class="service-title">Freight Services</h3>
                        <p class="service-desc">Full and partial truckload freight solutions for all cargo sizes.</p>
                        <a href="<?php echo APP_URL; ?>/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-warehouse"></i></div>
                        <h3 class="service-title">Logistics & Warehousing</h3>
                        <p class="service-desc">Complete logistics and storage solutions with inventory management.</p>
                        <a href="<?php echo APP_URL; ?>/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>/services.php" class="btn btn-primary btn-lg btn-rounded">
                View All Services <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section-padding why-choose-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose Us</h2>
            <p class="section-subtitle">Experience the difference with our professional transport services</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <div class="feature-content">
                        <h5>On-Time Delivery</h5>
                        <p>We guarantee timely delivery of your goods with our efficient logistics network.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="feature-content">
                        <h5>GPS Tracking</h5>
                        <p>Real-time tracking of your shipments with our advanced GPS technology.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <div class="feature-content">
                        <h5>24/7 Support</h5>
                        <p>Round-the-clock customer support to assist you anytime, anywhere.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-tags"></i></div>
                    <div class="feature-content">
                        <h5>Affordable Pricing</h5>
                        <p>Competitive rates without compromising on quality service.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-content">
                        <h5>Safe & Secure</h5>
                        <p>Your goods are insured and handled with utmost care.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <div class="feature-content">
                        <h5>Expert Team</h5>
                        <p>Experienced drivers and logistics professionals at your service.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-route"></i></div>
                    <div class="feature-content">
                        <h5>Nationwide Coverage</h5>
                        <p>Extensive network covering all major cities and towns.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-box-open"></i></div>
                    <div class="feature-content">
                        <h5>Flexible Options</h5>
                        <p>Customized solutions for all types of cargo and requirements.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Simple steps to book your transport service</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <span class="position-absolute top-0 end-0 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: bold;">1</span>
                    </div>
                    <h4>Book Online</h4>
                    <p class="text-muted">Fill out our simple booking form with your shipment details.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <span class="position-absolute top-0 end-0 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: bold;">2</span>
                    </div>
                    <h4>Get Confirmation</h4>
                    <p class="text-muted">Our team will call you to confirm and schedule pickup.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <span class="position-absolute top-0 end-0 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: bold;">3</span>
                    </div>
                    <h4>We Pickup</h4>
                    <p class="text-muted">Our driver arrives at your location to collect the goods.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="position-absolute top-0 end-0 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: bold;">4</span>
                    </div>
                    <h4>Safe Delivery</h4>
                    <p class="text-muted">Your goods are delivered safely to the destination.</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary btn-lg btn-rounded px-5">
                Get Started Now <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">What Our Clients Say</h2>
            <p class="section-subtitle">Trusted by hundreds of businesses across the country</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i > $testimonial['rating'] ? '-half-alt' : ''; ?><?php echo $i > ceil($testimonial['rating']) ? ' far' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-text">"<?php echo sanitize($testimonial['content']); ?>"</p>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <?php echo strtoupper(substr($testimonial['customer_name'], 0, 2)); ?>
                                </div>
                                <div>
                                    <p class="testimonial-name"><?php echo sanitize($testimonial['customer_name']); ?></p>
                                    <p class="testimonial-title"><?php echo sanitize($testimonial['customer_title']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Testimonials -->
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Excellent service! They delivered our goods on time and in perfect condition. Highly recommended!"</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">RS</div>
                            <div>
                                <p class="testimonial-name">Rajesh Sharma</p>
                                <p class="testimonial-title">CEO, Sharma Trading Co.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"The temperature-controlled transport is exceptional. Our perishable goods always arrive fresh."</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">PP</div>
                            <div>
                                <p class="testimonial-name">Priya Patel</p>
                                <p class="testimonial-title">Operations Manager, Fresh Foods Ltd</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"Professional heavy equipment transport. They handled our machinery with great care."</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">AK</div>
                            <div>
                                <p class="testimonial-name">Amit Kumar</p>
                                <p class="testimonial-title">Owner, Kumar Constructions</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Find answers to common questions about our services</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" 
                                            data-mdb-collapse-init data-mdb-target="#faq<?php echo $index; ?>">
                                        <?php echo sanitize($faq['question']); ?>
                                    </button>
                                </h2>
                                <div id="faq<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                     data-mdb-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <?php echo nl2br(sanitize($faq['answer'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback FAQs -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-mdb-collapse-init data-mdb-target="#faq1">
                                    How do I book a transport service?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can easily book through our website by filling out the booking form on the "Book Now" page. Alternatively, you can call our customer service team for assistance.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-mdb-collapse-init data-mdb-target="#faq2">
                                    What areas do you cover?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We provide transportation services across all major cities and towns in India. Contact us for specific route inquiries.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-mdb-collapse-init data-mdb-target="#faq3">
                                    Do you provide tracking for shipments?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, all shipments come with real-time GPS tracking. Once your booking is confirmed, you'll receive a tracking ID to monitor your shipment status anytime.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted">Still have questions?</p>
                    <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-padding bg-gradient-primary text-white">
    <div class="container text-center">
        <h2 class="mb-3">Ready to Ship Your Goods?</h2>
        <p class="lead mb-4">Get started with our reliable transport services today. Request a free quote!</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-light btn-lg btn-rounded px-4">
                <i class="fas fa-paper-plane me-2"></i>Get a Quote
            </a>
            <a href="tel:<?php echo $contactPhone; ?>" class="btn btn-outline-light btn-lg btn-rounded px-4">
                <i class="fas fa-phone me-2"></i>Call Us Now
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
