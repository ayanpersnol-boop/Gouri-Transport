<?php
/**
 * Gouri Transport - Booking / Get Quote Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Book a Transport';

// Get vehicle types
$vehicleTypes = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM vehicle_types WHERE is_active = 1 ORDER BY name");
    $vehicleTypes = $stmt->fetchAll();
} catch (Exception $e) {
    // Use default vehicle types
    $vehicleTypes = [
        ['id' => 1, 'name' => 'Truck', 'description' => 'Heavy duty truck for large cargo'],
        ['id' => 2, 'name' => 'Mini Truck', 'description' => 'Medium cargo transportation'],
        ['id' => 3, 'name' => 'Container', 'description' => 'Container shipping for bulk goods'],
        ['id' => 4, 'name' => 'Pickup', 'description' => 'Small deliveries and parcels'],
        ['id' => 5, 'name' => 'Refrigerated Truck', 'description' => 'Temperature controlled transport'],
        ['id' => 6, 'name' => 'Flatbed Truck', 'description' => 'Oversized and heavy equipment']
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
        redirect($_SERVER['REQUEST_URI']);
    }
    
    // Get form data
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $pickupLocation = sanitize($_POST['pickup_location'] ?? '');
    $deliveryLocation = sanitize($_POST['delivery_location'] ?? '');
    $vehicleTypeId = intval($_POST['vehicle_type'] ?? 0);
    $goodsDescription = sanitize($_POST['goods_description'] ?? '');
    $deliveryDate = sanitize($_POST['delivery_date'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($pickupLocation)) $errors[] = 'Pickup location is required';
    if (empty($deliveryLocation)) $errors[] = 'Delivery location is required';
    if (empty($vehicleTypeId)) $errors[] = 'Please select a vehicle type';
    
    if (empty($errors)) {
        try {
            $db = getDB();
            
            // Generate tracking ID
            $trackingId = generateTrackingId();
            
            // Insert booking
            $stmt = $db->prepare("
                INSERT INTO bookings 
                (tracking_id, full_name, email, phone, pickup_location, delivery_location, 
                 vehicle_type_id, goods_description, delivery_date, message, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $trackingId, $fullName, $email, $phone, $pickupLocation, $deliveryLocation,
                $vehicleTypeId, $goodsDescription, $deliveryDate, $message
            ]);
            
            $bookingId = $db->lastInsertId();
            
            // Send confirmation emails
            sendCustomerConfirmation($email, $fullName, $trackingId);
            sendAdminNotification($bookingId, $trackingId, $fullName, $email, $phone);
            
            // Redirect with success
            setFlashMessage('success', "Thank you! Your booking has been received. Your tracking ID is: {$trackingId}");
            redirect(APP_URL . '/track.php?id=' . $trackingId);
            
        } catch (Exception $e) {
            error_log("Booking error: " . $e->getMessage());
            setFlashMessage('error', 'Something went wrong. Please try again later.');
        }
    } else {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

// Get pre-selected service if any
$selectedService = intval($_GET['service'] ?? 0);

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Book a Transport</h1>
                <p class="lead mb-0">Fill out the form below to get a quote for your shipment</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Book Now</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Booking Form -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <!-- Form -->
            <div class="col-lg-8">
                <div class="booking-form-card">
                    <h3 class="mb-4">Request a Quote</h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row g-3">
                            <!-- Personal Information -->
                            <div class="col-12">
                                <h5 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Personal Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                                    <label class="form-label" for="full_name">Full Name *</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="tel" id="phone" name="phone" class="form-control" required>
                                    <label class="form-label" for="phone">Phone Number *</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                    <label class="form-label" for="email">Email Address *</label>
                                </div>
                            </div>
                            
                            <!-- Shipment Details -->
                            <div class="col-12 mt-4">
                                <h5 class="text-primary mb-3"><i class="fas fa-box me-2"></i>Shipment Details</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="pickup_location" name="pickup_location" class="form-control" required>
                                    <label class="form-label" for="pickup_location">Pickup Location *</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="delivery_location" name="delivery_location" class="form-control" required>
                                    <label class="form-label" for="delivery_location">Delivery Location *</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="date" id="delivery_date" name="delivery_date" class="form-control">
                                    <label class="form-label" for="delivery_date">Preferred Delivery Date</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <select class="form-select" name="vehicle_type" id="vehicle_type" required>
                                    <option value="">Select Vehicle Type *</option>
                                    <?php foreach ($vehicleTypes as $vehicle): ?>
                                        <option value="<?php echo $vehicle['id']; ?>">
                                            <?php echo $vehicle['name']; ?> - <?php echo $vehicle['description']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <textarea id="goods_description" name="goods_description" class="form-control" rows="3"></textarea>
                                    <label class="form-label" for="goods_description">Description of Goods (Type, Weight, Quantity, etc.)</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <textarea id="message" name="message" class="form-control" rows="3"></textarea>
                                    <label class="form-label" for="message">Additional Message / Special Requirements</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg btn-rounded w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Request Transport
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>How It Works</h5>
                        <ol class="list-unstyled mb-0">
                            <li class="mb-3 d-flex">
                                <span class="badge bg-primary rounded-circle me-3" style="min-width: 25px;">1</span>
                                <span>Fill out the booking form with your details</span>
                            </li>
                            <li class="mb-3 d-flex">
                                <span class="badge bg-primary rounded-circle me-3" style="min-width: 25px;">2</span>
                                <span>We review your request and send a quote</span>
                            </li>
                            <li class="mb-3 d-flex">
                                <span class="badge bg-primary rounded-circle me-3" style="min-width: 25px;">3</span>
                                <span>Confirm the booking and we arrange pickup</span>
                            </li>
                            <li class="d-flex">
                                <span class="badge bg-success rounded-circle me-3" style="min-width: 25px;">4</span>
                                <span>Track your shipment until delivery</span>
                            </li>
                        </ol>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-phone text-primary me-2"></i>Need Help?</h5>
                        <p class="card-text">Call us for immediate assistance with your booking.</p>
                        <a href="tel:<?php echo getSetting('contact_phone', '+91 1234567890'); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-phone-alt me-2"></i><?php echo getSetting('contact_phone', '+91 1234567890'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-shield-alt text-primary me-2"></i>Why Book With Us?</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Free instant quotes</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>No hidden charges</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Real-time tracking</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Insurance included</li>
                            <li><i class="fas fa-check text-success me-2"></i>24/7 customer support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
