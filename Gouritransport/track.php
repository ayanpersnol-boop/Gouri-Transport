<?php
/**
 * Gouri Transport - Tracking Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Track Shipment';

$trackingData = null;
$trackingUpdates = [];
$error = '';

if (isset($_GET['id'])) {
    $trackingId = sanitize($_GET['id']);
    
    try {
        $db = getDB();
        
        // Get booking details
        $stmt = $db->prepare("SELECT b.*, v.name as vehicle_name FROM bookings b LEFT JOIN vehicle_types v ON b.vehicle_type_id = v.id WHERE b.tracking_id = ?");
        $stmt->execute([$trackingId]);
        $trackingData = $stmt->fetch();
        
        if ($trackingData) {
            // Get tracking updates
            $stmt = $db->prepare("SELECT * FROM tracking_updates WHERE booking_id = ? ORDER BY created_at DESC");
            $stmt->execute([$trackingData['id']]);
            $trackingUpdates = $stmt->fetchAll();
            
            // If no updates, create default ones based on status
            if (empty($trackingUpdates)) {
                $defaultUpdates = [
                    ['status' => 'Booking Confirmed', 'description' => 'Your booking has been received and is being processed.', 'created_at' => $trackingData['created_at']],
                ];
                
                if (in_array($trackingData['status'], ['confirmed', 'in_progress', 'delivered'])) {
                    $defaultUpdates[] = ['status' => 'Pickup Scheduled', 'description' => 'Pickup has been scheduled for your shipment.', 'created_at' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime($trackingData['created_at'])))];
                }
                
                if (in_array($trackingData['status'], ['in_progress', 'delivered'])) {
                    $defaultUpdates[] = ['status' => 'In Transit', 'description' => 'Your shipment is on its way to the destination.', 'created_at' => date('Y-m-d H:i:s', strtotime('+2 days', strtotime($trackingData['created_at'])))];
                }
                
                if ($trackingData['status'] === 'delivered') {
                    $defaultUpdates[] = ['status' => 'Delivered', 'description' => 'Your shipment has been delivered successfully.', 'created_at' => date('Y-m-d H:i:s', strtotime('+3 days', strtotime($trackingData['created_at'])))];
                }
                
                $trackingUpdates = $defaultUpdates;
            }
        } else {
            $error = 'Tracking ID not found. Please check and try again.';
        }
    } catch (Exception $e) {
        $error = 'Unable to retrieve tracking information. Please try again later.';
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Track Your Shipment</h1>
                <p class="lead mb-0">Enter your tracking ID to get real-time updates on your shipment</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Track</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Tracking Form -->
<section class="section-padding <?php echo $trackingData ? 'pb-0' : ''; ?>">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="tracking-box">
                    <div class="text-center mb-4">
                        <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                        <h3>Track Your Shipment</h3>
                        <p class="text-muted">Enter your tracking ID below</p>
                    </div>
                    
                    <form method="GET" action="">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-primary text-white border-0">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="id" class="form-control form-control-lg" 
                                   placeholder="Enter Tracking ID (e.g., GTR20231015ABCD)" 
                                   value="<?php echo isset($_GET['id']) ? sanitize($_GET['id']) : ''; ?>" required>
                            <button type="submit" class="btn btn-primary btn-lg">
                                Track
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($trackingData): ?>
<!-- Tracking Results -->
<section class="section-padding">
    <div class="container">
        <!-- Shipment Overview -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Shipment Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <small class="text-muted d-block">Tracking ID</small>
                        <strong class="fs-5 text-primary"><?php echo $trackingData['tracking_id']; ?></strong>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-<?php echo $trackingData['status'] === 'delivered' ? 'success' : ($trackingData['status'] === 'cancelled' ? 'danger' : 'primary'); ?> fs-6">
                            <?php echo getStatusLabel($trackingData['status']); ?>
                        </span>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <small class="text-muted d-block">Vehicle Type</small>
                        <strong><?php echo $trackingData['vehicle_name'] ?? 'N/A'; ?></strong>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <small class="text-muted d-block">Booking Date</small>
                        <strong><?php echo formatDate($trackingData['created_at']); ?></strong>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">From</small>
                                <strong><?php echo $trackingData['pickup_location']; ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">To</small>
                                <strong><?php echo $trackingData['delivery_location']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tracking Timeline -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Tracking History</h5>
                    </div>
                    <div class="card-body">
                        <div class="tracking-timeline">
                            <?php foreach ($trackingUpdates as $index => $update): ?>
                                <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <div class="timeline-date">
                                        <i class="far fa-clock me-1"></i><?php echo formatDate($update['created_at'], 'd M Y, h:i A'); ?>
                                    </div>
                                    <div class="timeline-title"><?php echo $update['status']; ?></div>
                                    <div class="timeline-desc"><?php echo $update['description'] ?? $update['location'] ?? ''; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong><?php echo $trackingData['full_name']; ?></strong></p>
                        <p class="mb-2 text-muted"><i class="fas fa-envelope me-2"></i><?php echo $trackingData['email']; ?></p>
                        <p class="mb-0 text-muted"><i class="fas fa-phone me-2"></i><?php echo $trackingData['phone']; ?></p>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">If you have any questions about your shipment, please contact our support team.</p>
                        <a href="tel:<?php echo getSetting('contact_phone'); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-phone me-2"></i>Call Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
