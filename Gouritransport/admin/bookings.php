<?php
/**
 * Gouri Transport - Admin Bookings Management
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Bookings Management';

// Get filter parameters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Handle actions
$action = $_GET['action'] ?? 'list';
$bookingId = intval($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        redirect(ADMIN_URL . '/bookings.php');
    }
    
    $newStatus = sanitize($_POST['status'] ?? '');
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $trackingId = sanitize($_POST['tracking_id'] ?? '');
    $driverName = sanitize($_POST['driver_name'] ?? '');
    $driverPhone = sanitize($_POST['driver_phone'] ?? '');
    $finalPrice = floatval($_POST['final_price'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    $updateMessage = sanitize($_POST['update_message'] ?? '');
    
    try {
        $db = getDB();
        
        // Update booking
        $stmt = $db->prepare("
            UPDATE bookings 
            SET status = ?, assigned_driver = ?, driver_phone = ?, final_price = ?, admin_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $driverName, $driverPhone, $finalPrice, $notes, $bookingId]);
        
        // Add tracking update
        if (!empty($updateMessage)) {
            $stmt = $db->prepare("INSERT INTO tracking_updates (booking_id, status, description, updated_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$bookingId, getStatusLabel($newStatus), $updateMessage, $_SESSION['admin_id']]);
        }
        
        setFlashMessage('success', 'Booking updated successfully');
        redirect(ADMIN_URL . '/bookings.php?action=view&id=' . $bookingId);
    } catch (Exception $e) {
        setFlashMessage('error', 'Error updating booking');
    }
}

// Handle delete
if ($action === 'delete' && $bookingId > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        setFlashMessage('success', 'Booking deleted successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting booking');
    }
    redirect(ADMIN_URL . '/bookings.php');
}

// Build query
$query = "SELECT b.*, v.name as vehicle_name FROM bookings b LEFT JOIN vehicle_types v ON b.vehicle_type_id = v.id WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

if ($search) {
    $query .= " AND (b.tracking_id LIKE ? OR b.full_name LIKE ? OR b.email LIKE ? OR b.phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Count total for pagination
$countQuery = str_replace("b.*, v.name as vehicle_name", "COUNT(*) as total", $query);
$stmt = getDB()->prepare($countQuery);
$stmt->execute($params);
$totalItems = $stmt->fetch()['total'];

// Get bookings
$query .= " ORDER BY b.created_at DESC LIMIT {$offset}, {$limit}";
$stmt = getDB()->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get single booking for view/edit
$booking = null;
$trackingUpdates = [];
if ($action === 'view' && $bookingId > 0) {
    $stmt = getDB()->prepare("SELECT b.*, v.name as vehicle_name FROM bookings b LEFT JOIN vehicle_types v ON b.vehicle_type_id = v.id WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        $stmt = getDB()->prepare("SELECT t.*, a.full_name as updated_by_name FROM tracking_updates t LEFT JOIN admin_users a ON t.updated_by = a.id WHERE t.booking_id = ? ORDER BY t.created_at DESC");
        $stmt->execute([$bookingId]);
        $trackingUpdates = $stmt->fetchAll();
    }
}

// Get vehicle types for dropdown
$vehicleTypes = getDB()->query("SELECT * FROM vehicle_types WHERE is_active = 1 ORDER BY name")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if ($action === 'view' && $booking): ?>
    <!-- View Booking Detail -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Booking Details</h4>
        <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
    
    <div class="row g-4">
        <!-- Booking Info -->
        <div class="col-lg-8">
            <div class="table-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Booking Information</h5>
                    <span class="badge bg-<?php echo getStatusBadgeClass($booking['status']); ?> fs-6">
                        <?php echo getStatusLabel($booking['status']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tracking ID</small>
                            <strong class="fs-5 text-primary"><?php echo $booking['tracking_id']; ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Booking Date</small>
                            <strong><?php echo formatDate($booking['created_at'], 'd M Y, h:i A'); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Customer Name</small>
                            <strong><?php echo sanitize($booking['full_name']); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email</small>
                            <strong><?php echo sanitize($booking['email']); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Phone</small>
                            <strong><?php echo sanitize($booking['phone']); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Vehicle Type</small>
                            <strong><?php echo $booking['vehicle_name'] ?? 'N/A'; ?></strong>
                        </div>
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Pickup Location</small>
                            <strong><?php echo sanitize($booking['pickup_location']); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Delivery Location</small>
                            <strong><?php echo sanitize($booking['delivery_location']); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Delivery Date</small>
                            <strong><?php echo $booking['delivery_date'] ? formatDate($booking['delivery_date']) : 'Not specified'; ?></strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Goods Description</small>
                            <p class="mb-0"><?php echo nl2br(sanitize($booking['goods_description'])); ?></p>
                        </div>
                        <?php if ($booking['message']): ?>
                            <div class="col-12">
                                <small class="text-muted d-block">Additional Message</small>
                                <p class="mb-0"><?php echo nl2br(sanitize($booking['message'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tracking Updates -->
            <div class="table-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Tracking History</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($trackingUpdates)): ?>
                        <div class="tracking-timeline">
                            <?php foreach ($trackingUpdates as $update): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?php echo formatDate($update['created_at'], 'd M Y, h:i A'); ?>
                                        <?php if ($update['updated_by_name']): ?>
                                            <small class="text-muted">by <?php echo $update['updated_by_name']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-title"><?php echo sanitize($update['status']); ?></div>
                                    <div class="timeline-desc"><?php echo sanitize($update['description']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No tracking updates yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Update Form -->
        <div class="col-lg-4">
            <div class="table-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Booking</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="tracking_id" value="<?php echo $booking['tracking_id']; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="in_progress" <?php echo $booking['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="delivered" <?php echo $booking['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" value="<?php echo sanitize($booking['assigned_driver']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Driver Phone</label>
                            <input type="text" name="driver_phone" class="form-control" value="<?php echo sanitize($booking['driver_phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Final Price (₹)</label>
                            <input type="number" name="final_price" class="form-control" step="0.01" value="<?php echo $booking['final_price']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Update Message</label>
                            <textarea name="update_message" class="form-control" rows="2" placeholder="Enter status update message for customer..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?php echo sanitize($booking['admin_notes']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Update Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
<?php else: ?>
    <!-- Bookings List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>All Bookings</h4>
    </div>
    
    <!-- Filters -->
    <div class="table-card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by tracking ID, name, email, phone..." value="<?php echo sanitize($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="btn btn-secondary w-100">
                        <i class="fas fa-undo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><code><?php echo $booking['tracking_id']; ?></code></td>
                                <td>
                                    <strong><?php echo sanitize($booking['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo $booking['phone']; ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?php echo truncateText($booking['pickup_location'], 15); ?> →<br>
                                        <?php echo truncateText($booking['delivery_location'], 15); ?>
                                    </small>
                                </td>
                                <td><?php echo $booking['vehicle_name'] ?? 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($booking['status']); ?>">
                                        <?php echo getStatusLabel($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($booking['created_at'], 'd M Y'); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo ADMIN_URL; ?>/bookings.php?action=view&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/bookings.php?action=delete&id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this booking?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalItems > $limit): ?>
            <div class="card-footer">
                <?php 
                $urlPattern = ADMIN_URL . '/bookings.php?page={page}' . ($status ? '&status=' . $status : '') . ($search ? '&search=' . urlencode($search) : '');
                echo generatePagination($totalItems, $limit, $page, $urlPattern); 
                ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
