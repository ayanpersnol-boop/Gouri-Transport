<?php
/**
 * Gouri Transport - Admin Pricing Management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Pricing Management';

$action = $_GET['action'] ?? 'plans';

// Handle pricing plans
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_plan'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        redirect(ADMIN_URL . '/pricing.php');
    }
    
    $planId = intval($_POST['plan_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $basePrice = floatval($_POST['base_price'] ?? 0);
    $perKmRate = floatval($_POST['per_km_rate'] ?? 0);
    $maxWeight = floatval($_POST['max_weight'] ?? 0);
    $features = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $db = getDB();
        $featuresJson = json_encode(array_values($features));
        
        if ($planId > 0) {
            $stmt = $db->prepare("
                UPDATE pricing_plans 
                SET name = ?, description = ?, base_price = ?, per_km_rate = ?, max_weight = ?, features = ?, is_popular = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $basePrice, $perKmRate, $maxWeight, $featuresJson, $isPopular, $isActive, $planId]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO pricing_plans (name, description, base_price, per_km_rate, max_weight, features, is_popular, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $basePrice, $perKmRate, $maxWeight, $featuresJson, $isPopular, $isActive]);
        }
        setFlashMessage('success', 'Pricing plan saved successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error saving plan');
    }
    redirect(ADMIN_URL . '/pricing.php');
}

// Handle vehicle types
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_vehicle'])) {
    $vehicleId = intval($_POST['vehicle_id'] ?? 0);
    $name = sanitize($_POST['vehicle_name'] ?? '');
    $desc = sanitize($_POST['vehicle_description'] ?? '');
    $capacity = floatval($_POST['capacity_weight'] ?? 0);
    $basePrice = floatval($_POST['base_price'] ?? 0);
    $perKm = floatval($_POST['per_km_rate'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $db = getDB();
        if ($vehicleId > 0) {
            $stmt = $db->prepare("
                UPDATE vehicle_types 
                SET name = ?, description = ?, capacity_weight = ?, base_price = ?, per_km_rate = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $desc, $capacity, $basePrice, $perKm, $isActive, $vehicleId]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO vehicle_types (name, description, capacity_weight, base_price, per_km_rate, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $desc, $capacity, $basePrice, $perKm, $isActive]);
        }
        setFlashMessage('success', 'Vehicle type saved successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error saving vehicle');
    }
    redirect(ADMIN_URL . '/pricing.php?action=vehicles');
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $type = $_GET['type'] ?? '';
    try {
        $db = getDB();
        if ($type === 'plan') {
            $stmt = $db->prepare("DELETE FROM pricing_plans WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'vehicle') {
            $stmt = $db->prepare("DELETE FROM vehicle_types WHERE id = ?");
            $stmt->execute([$id]);
        }
        setFlashMessage('success', 'Item deleted successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting item');
    }
    redirect(ADMIN_URL . '/pricing.php' . ($type === 'vehicle' ? '?action=vehicles' : ''));
}

// Get data
$db = getDB();
$plans = $db->query("SELECT * FROM pricing_plans ORDER BY sort_order, id DESC")->fetchAll();
$vehicles = $db->query("SELECT * FROM vehicle_types ORDER BY name")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Pricing Management</h4>
    <div class="btn-group">
        <a href="<?php echo ADMIN_URL; ?>/pricing.php?action=plans" class="btn btn-<?php echo $action === 'plans' ? 'primary' : 'outline-primary'; ?> btn-sm">Pricing Plans</a>
        <a href="<?php echo ADMIN_URL; ?>/pricing.php?action=vehicles" class="btn btn-<?php echo $action === 'vehicles' ? 'primary' : 'outline-primary'; ?> btn-sm">Vehicle Types</a>
    </div>
</div>

<?php if ($action === 'plans'): ?>
    <!-- Pricing Plans -->
    <div class="row g-4 mb-4">
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="table-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="mb-0"><?php echo sanitize($plan['name']); ?></h5>
                            <?php if ($plan['is_popular']): ?>
                                <span class="badge bg-warning">Popular</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small"><?php echo sanitize($plan['description']); ?></p>
                        <div class="mb-3">
                            <strong class="fs-4 text-primary"><?php echo formatPrice($plan['base_price']); ?></strong>
                            <span class="text-muted">base + <?php echo formatPrice($plan['per_km_rate']); ?>/km</span>
                        </div>
                        <p class="small text-muted">Max capacity: <?php echo number_format($plan['max_weight']); ?> kg</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-warning" data-mdb-modal-init data-mdb-target="#editPlan<?php echo $plan['id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pricing.php?delete=<?php echo $plan['id']; ?>&type=plan" 
                               class="btn btn-sm btn-danger" onclick="return confirm('Delete this plan?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Add New Plan Card -->
        <div class="col-md-4">
            <div class="table-card h-100 border-dashed" style="border: 2px dashed #dee2e6;">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                    <h5>Add New Plan</h5>
                    <button type="button" class="btn btn-outline-primary" data-mdb-modal-init data-mdb-target="#addPlan">
                        Create Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Plan Modal -->
    <div class="modal fade" id="addPlan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Pricing Plan</h5>
                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label>Base Price</label>
                                <input type="number" name="base_price" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-6">
                                <label>Per KM Rate</label>
                                <input type="number" name="per_km_rate" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Max Weight (kg)</label>
                            <input type="number" name="max_weight" class="form-control" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label>Features (one per line)</label>
                            <textarea name="features" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_popular" class="form-check-input">
                            <label class="form-check-label">Popular</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_active" class="form-check-input" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_plan" class="btn btn-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<?php else: ?>
    <!-- Vehicle Types -->
    <div class="table-card">
        <div class="card-header">
            <h5 class="mb-0">Vehicle Types</h5>
            <button type="button" class="btn btn-primary btn-sm" data-mdb-modal-init data-mdb-target="#addVehicle">
                <i class="fas fa-plus me-2"></i>Add Vehicle
            </button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Capacity (kg)</th>
                        <th>Base Price</th>
                        <th>Per KM</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?php echo sanitize($vehicle['name']); ?></td>
                            <td><?php echo sanitize($vehicle['description']); ?></td>
                            <td><?php echo number_format($vehicle['capacity_weight']); ?></td>
                            <td><?php echo formatPrice($vehicle['base_price']); ?></td>
                            <td><?php echo formatPrice($vehicle['per_km_rate']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $vehicle['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $vehicle['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-warning" data-mdb-modal-init data-mdb-target="#editVehicle<?php echo $vehicle['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?php echo ADMIN_URL; ?>/pricing.php?delete=<?php echo $vehicle['id']; ?>&type=vehicle&action=vehicles" 
                                       class="btn btn-sm btn-danger" onclick="return confirm('Delete this vehicle type?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicle" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Vehicle Type</h5>
                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="vehicle_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <input type="text" name="vehicle_description" class="form-control">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label>Capacity (kg)</label>
                                <input type="number" name="capacity_weight" class="form-control" step="0.01">
                            </div>
                            <div class="col-6">
                                <label>Base Price</label>
                                <input type="number" name="base_price" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Per KM Rate</label>
                            <input type="number" name="per_km_rate" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_vehicle" class="btn btn-primary">Save Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
