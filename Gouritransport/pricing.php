<?php
/**
 * Gouri Transport - Pricing Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Pricing';

// Get pricing plans
$pricingPlans = [];
$vehicleTypes = [];

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM pricing_plans WHERE is_active = 1 ORDER BY sort_order");
    $pricingPlans = $stmt->fetchAll();
    
    $stmt = $db->query("SELECT * FROM vehicle_types WHERE is_active = 1 ORDER BY name");
    $vehicleTypes = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback data
    $pricingPlans = [
        ['name' => 'Basic', 'description' => 'Perfect for small businesses', 'base_price' => 500, 'per_km_rate' => 20, 'max_weight' => 1000, 'is_popular' => false,
         'features' => '["Local delivery", "Basic tracking", "Email support", "Standard insurance"]'],
        ['name' => 'Standard', 'description' => 'Most popular choice', 'base_price' => 1000, 'per_km_rate' => 35, 'max_weight' => 5000, 'is_popular' => true,
         'features' => '["Nationwide delivery", "Real-time GPS tracking", "24/7 Phone support", "Enhanced insurance", "Priority handling"]'],
        ['name' => 'Premium', 'description' => 'Enterprise-grade solution', 'base_price' => 2500, 'per_km_rate' => 50, 'max_weight' => 25000, 'is_popular' => false,
         'features' => '["All routes covered", "Advanced tracking suite", "Dedicated account manager", "Full insurance coverage", "Express options", "Warehouse storage"]']
    ];
    
    $vehicleTypes = [
        ['name' => 'Truck', 'base_price' => 1500, 'per_km_rate' => 45, 'capacity_weight' => 10000],
        ['name' => 'Mini Truck', 'base_price' => 800, 'per_km_rate' => 25, 'capacity_weight' => 3000],
        ['name' => 'Container', 'base_price' => 3000, 'per_km_rate' => 35, 'capacity_weight' => 25000],
        ['name' => 'Pickup', 'base_price' => 400, 'per_km_rate' => 15, 'capacity_weight' => 1000]
    ];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Pricing Plans</h1>
                <p class="lead mb-0">Transparent pricing for all your transport needs</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Pricing</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Plans -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Choose Your Plan</h2>
            <p class="section-subtitle">Flexible pricing options to suit businesses of all sizes</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach ($pricingPlans as $plan): ?>
                <?php 
                $features = json_decode($plan['features'], true) ?: [];
                $isPopular = $plan['is_popular'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card <?php echo $isPopular ? 'popular' : ''; ?>">
                        <?php if ($isPopular): ?>
                            <span class="pricing-badge">MOST POPULAR</span>
                        <?php endif; ?>
                        
                        <h3 class="pricing-name"><?php echo sanitize($plan['name']); ?></h3>
                        <p class="text-muted"><?php echo sanitize($plan['description']); ?></p>
                        
                        <div class="pricing-price">
                            <?php echo formatPrice($plan['base_price']); ?>
                            <span>/ base</span>
                        </div>
                        <p class="text-muted">+ <?php echo formatPrice($plan['per_km_rate']); ?> per km</p>
                        
                        <ul class="pricing-features">
                            <?php foreach ($features as $feature): ?>
                                <li><i class="fas fa-check"></i><?php echo sanitize($feature); ?></li>
                            <?php endforeach; ?>
                            <?php if ($plan['max_weight']): ?>
                                <li><i class="fas fa-check"></i>Up to <?php echo number_format($plan['max_weight']); ?> kg capacity</li>
                            <?php endif; ?>
                        </ul>
                        
                        <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-<?php echo $isPopular ? 'primary' : 'outline-primary'; ?> btn-rounded w-100">
                            Book Now <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Freight Pricing Table -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Vehicle Type Pricing</h2>
            <p class="section-subtitle">Detailed pricing based on vehicle type and capacity</p>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Base Price</th>
                                <th>Per KM Rate</th>
                                <th>Capacity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicleTypes as $vehicle): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo sanitize($vehicle['name']); ?></strong>
                                        <?php if ($vehicle['description']): ?>
                                            <br><small class="text-muted"><?php echo sanitize($vehicle['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatPrice($vehicle['base_price']); ?></td>
                                    <td><?php echo formatPrice($vehicle['per_km_rate']); ?></td>
                                    <td><?php echo number_format($vehicle['capacity_weight']); ?> kg</td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/booking.php?vehicle=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary btn-rounded">
                                            Book
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4 d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>Note:</strong> Final pricing may vary based on distance, cargo type, and special requirements. Contact us for a detailed quote.
            </div>
        </div>
    </div>
</section>

<!-- Price Calculator -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-form-card">
                    <h3 class="mb-4 text-center"><i class="fas fa-calculator text-primary me-2"></i>Quick Price Calculator</h3>
                    
                    <form id="priceCalculator">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Type</label>
                                <select class="form-select" id="calcVehicle">
                                    <option value="">Select Vehicle</option>
                                    <?php foreach ($vehicleTypes as $vehicle): ?>
                                        <option value="<?php echo $vehicle['per_km_rate']; ?>" data-base="<?php echo $vehicle['base_price']; ?>">
                                            <?php echo $vehicle['name']; ?> (₹<?php echo $vehicle['per_km_rate']; ?>/km)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Distance (km)</label>
                                <input type="number" class="form-control" id="calcDistance" placeholder="Enter distance in km">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary btn-rounded" onclick="calculatePrice()">
                                <i class="fas fa-calculator me-2"></i>Calculate
                            </button>
                        </div>
                        
                        <div id="priceResult" class="mt-4 p-4 bg-light rounded text-center" style="display: none;">
                            <h5>Estimated Price</h5>
                            <div class="display-4 text-primary fw-bold" id="estimatedPrice">₹0</div>
                            <p class="text-muted mb-0">*Final price may vary based on actual requirements</p>
                            <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary mt-3">Proceed to Book</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function calculatePrice() {
    const vehicle = document.getElementById('calcVehicle');
    const distance = document.getElementById('calcDistance');
    
    if (!vehicle.value || !distance.value) {
        alert('Please select a vehicle type and enter distance');
        return;
    }
    
    const basePrice = parseFloat(vehicle.options[vehicle.selectedIndex].dataset.base);
    const perKmRate = parseFloat(vehicle.value);
    const dist = parseFloat(distance.value);
    
    const totalPrice = basePrice + (perKmRate * dist);
    
    document.getElementById('estimatedPrice').textContent = '₹' + totalPrice.toLocaleString('en-IN');
    document.getElementById('priceResult').style.display = 'block';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
