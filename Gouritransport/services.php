<?php
/**
 * Gouri Transport - Services Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Our Services';

// Get services
$services = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching services: " . $e->getMessage());
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Our Services</h1>
                <p class="lead mb-0">Comprehensive transport and logistics solutions for all your cargo needs</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Services</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="section-padding">
    <div class="container">
        <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <?php 
                    $features = json_decode($service['features'], true) ?: [];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="service-card h-100 shadow-hover">
                            <div class="service-icon">
                                <i class="fas fa-<?php echo $service['icon']; ?>"></i>
                            </div>
                            <h3 class="service-title"><?php echo sanitize($service['title']); ?></h3>
                            <p class="service-desc"><?php echo sanitize($service['description']); ?></p>
                            
                            <?php if (!empty($features)): ?>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <?php foreach ($features as $feature): ?>
                                        <li class="mb-2 text-muted">
                                            <i class="fas fa-check-circle text-success me-2"></i><?php echo sanitize($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <a href="<?php echo APP_URL; ?>/booking.php?service=<?php echo $service['id']; ?>" 
                               class="btn btn-primary w-100 btn-rounded">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Fallback Services -->
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-truck"></i></div>
                        <h3 class="service-title">Road Transport</h3>
                        <p class="service-desc">Complete road transportation solutions for all types of cargo. We ensure safe and timely delivery across cities and states with our modern fleet of vehicles.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Nationwide Coverage</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>GPS Tracking</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Real-time Updates</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Secure Handling</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-shipping-fast"></i></div>
                        <h3 class="service-title">Freight Services</h3>
                        <p class="service-desc">Full truckload and less-than-truckload freight services tailored to your business needs. Cost-effective solutions for bulk shipments.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>FTL & LTL Options</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Competitive Rates</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Flexible Scheduling</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Insurance Coverage</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-warehouse"></i></div>
                        <h3 class="service-title">Logistics & Warehousing</h3>
                        <p class="service-desc">End-to-end logistics management with secure warehousing facilities. Storage, inventory management, and distribution services.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Secure Storage</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Inventory Management</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Distribution Services</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Climate Control</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-bolt"></i></div>
                        <h3 class="service-title">Express Delivery</h3>
                        <p class="service-desc">Time-critical delivery services for urgent shipments. Same-day and next-day delivery options available for priority cargo.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Same Day Delivery</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Next Day Options</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Priority Handling</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>24/7 Service</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-truck-moving"></i></div>
                        <h3 class="service-title">Heavy Equipment Transport</h3>
                        <p class="service-desc">Specialized transportation for construction equipment, machinery, and oversized loads. Expert handling with proper permits.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Expert Handling</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Route Planning</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Permit Assistance</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Insurance Included</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 shadow-hover">
                        <div class="service-icon"><i class="fas fa-temperature-low"></i></div>
                        <h3 class="service-title">Temperature Controlled</h3>
                        <p class="service-desc">Refrigerated transport for perishable goods, pharmaceuticals, and temperature-sensitive cargo. Maintains optimal conditions.</p>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Precise Temperature Control</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Pharma Certified</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>Real-time Monitoring</li>
                            <li class="mb-2 text-muted"><i class="fas fa-check-circle text-success me-2"></i>HACCP Compliant</li>
                        </ul>
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary w-100 btn-rounded">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Request Quote
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="section-padding bg-light">
    <div class="container text-center">
        <h2 class="mb-3">Need a Custom Solution?</h2>
        <p class="lead mb-4">Contact us to discuss your specific transport requirements</p>
        <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-primary btn-lg btn-rounded px-5">
            <i class="fas fa-comments me-2"></i>Get in Touch
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
