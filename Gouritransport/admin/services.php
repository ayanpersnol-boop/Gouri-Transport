<?php
/**
 * Gouri Transport - Admin Services Management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Services Management';

// Handle form submission
$action = $_GET['action'] ?? 'list';
$serviceId = intval($_GET['id'] ?? 0);

// Save service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        redirect(ADMIN_URL . '/services.php');
    }
    
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $shortDesc = sanitize($_POST['short_description'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'truck');
    $features = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = intval($_POST['sort_order'] ?? 0);
    
    if (empty($title) || empty($slug) || empty($description)) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        try {
            $db = getDB();
            $featuresJson = json_encode(array_values($features));
            
            if ($serviceId > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE services 
                    SET title = ?, slug = ?, description = ?, short_description = ?, icon = ?, features = ?, is_active = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $description, $shortDesc, $icon, $featuresJson, $isActive, $sortOrder, $serviceId]);
                setFlashMessage('success', 'Service updated successfully');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO services (title, slug, description, short_description, icon, features, is_active, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $description, $shortDesc, $icon, $featuresJson, $isActive, $sortOrder]);
                setFlashMessage('success', 'Service created successfully');
            }
            redirect(ADMIN_URL . '/services.php');
        } catch (Exception $e) {
            setFlashMessage('error', 'Error saving service');
        }
    }
}

// Delete service
if ($action === 'delete' && $serviceId > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        setFlashMessage('success', 'Service deleted successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting service');
    }
    redirect(ADMIN_URL . '/services.php');
}

// Get services
$services = getDB()->query("SELECT * FROM services ORDER BY sort_order, id DESC")->fetchAll();

// Get single service for edit
$service = null;
if (($action === 'edit' || $action === 'add') && $serviceId > 0) {
    $stmt = getDB()->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
}

// Available icons
$icons = ['truck', 'shipping-fast', 'warehouse', 'bolt', 'truck-moving', 'temperature-low', 'box', 'plane', 'ship', 'train'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'add' || ($action === 'edit' && $service)): ?>
    <!-- Add/Edit Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cubes me-2"></i><?php echo $action === 'edit' ? 'Edit' : 'Add'; ?> Service</h4>
        <a href="<?php echo ADMIN_URL; ?>/services.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
    
    <div class="table-card">
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required 
                               value="<?php echo $service['title'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-control" required 
                               value="<?php echo $service['slug'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <?php foreach ($icons as $icon): ?>
                                <option value="<?php echo $icon; ?>" <?php echo ($service['icon'] ?? 'truck') === $icon ? 'selected' : ''; ?>>
                                    <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($icon); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" 
                               value="<?php echo $service['sort_order'] ?? 0; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" class="form-check-input" 
                                   <?php echo ($service['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_description" class="form-control" 
                               value="<?php echo $service['short_description'] ?? ''; ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo $service['description'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Features (one per line)</label>
                        <textarea name="features" class="form-control" rows="4" placeholder="Nationwide Coverage&#10;GPS Tracking&#10;24/7 Support"><?php 
                            if ($service && $service['features']) {
                                $features = json_decode($service['features'], true);
                                echo implode("\n", $features);
                            }
                        ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="save_service" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Service
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
<?php else: ?>
    <!-- Services List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cubes me-2"></i>Services</h4>
        <a href="<?php echo ADMIN_URL; ?>/services.php?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Service
        </a>
    </div>
    
    <div class="table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $svc): ?>
                        <tr>
                            <td><i class="fas fa-<?php echo $svc['icon']; ?> text-primary"></i></td>
                            <td><?php echo sanitize($svc['title']); ?></td>
                            <td><code><?php echo $svc['slug']; ?></code></td>
                            <td>
                                <span class="badge bg-<?php echo $svc['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $svc['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo $svc['sort_order']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo ADMIN_URL; ?>/services.php?action=edit&id=<?php echo $svc['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>/services.php?action=delete&id=<?php echo $svc['id']; ?>" 
                                       class="btn btn-sm btn-danger" title="Delete"
                                       onclick="return confirm('Are you sure?')">
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
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
