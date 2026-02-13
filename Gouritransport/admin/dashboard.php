<?php
/**
 * Gouri Transport - Admin Dashboard
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Dashboard';

// Get statistics
try {
    $db = getDB();
    
    $totalBookings = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pendingBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $inProgressBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'in_progress'")->fetchColumn();
    $deliveredBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'delivered'")->fetchColumn();
    
    // Revenue calculation
    $totalRevenue = $db->query("SELECT COALESCE(SUM(final_price), 0) FROM bookings WHERE status = 'delivered'")->fetchColumn();
    
    // Recent bookings
    $recentBookings = $db->query("SELECT b.*, v.name as vehicle_name FROM bookings b LEFT JOIN vehicle_types v ON b.vehicle_type_id = v.id ORDER BY b.created_at DESC LIMIT 10")->fetchAll();
    
    // Unread messages
    $unreadMessages = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
    
    // Today's bookings
    $todayBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalBookings = $pendingBookings = $inProgressBookings = $deliveredBookings = $totalRevenue = $unreadMessages = $todayBookings = 0;
    $recentBookings = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo number_format($totalBookings); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo number_format($pendingBookings); ?></div>
            <div class="stat-label">Pending Bookings</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-value"><?php echo number_format($inProgressBookings); ?></div>
            <div class="stat-label">In Progress</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo number_format($deliveredBookings); ?></div>
            <div class="stat-label">Delivered</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-value"><?php echo formatPrice($totalRevenue); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-value"><?php echo number_format($todayBookings); ?></div>
            <div class="stat-label">Today's Bookings</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-value"><?php echo number_format($unreadMessages); ?></div>
            <div class="stat-label">Unread Messages</div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="table-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Bookings</h5>
        <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Route</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentBookings)): ?>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><code><?php echo $booking['tracking_id']; ?></code></td>
                            <td>
                                <strong><?php echo sanitize($booking['full_name']); ?></strong><br>
                                <small class="text-muted"><?php echo $booking['phone']; ?></small>
                            </td>
                            <td>
                                <small>
                                    <?php echo truncateText($booking['pickup_location'], 20); ?> →<br>
                                    <?php echo truncateText($booking['delivery_location'], 20); ?>
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
                                <a href="<?php echo ADMIN_URL; ?>/bookings.php?action=view&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
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
</div>

<!-- Recent Messages & Analytics Row -->
<div class="row g-4 mt-2">
    <!-- Recent Messages -->
    <div class="col-lg-6">
        <div class="table-card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Messages</h5>
                <a href="<?php echo ADMIN_URL; ?>/messages.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php 
                $recentMessages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
                if (!empty($recentMessages)): 
                    foreach ($recentMessages as $msg): 
                ?>
                    <a href="<?php echo ADMIN_URL; ?>/messages.php" class="list-group-item list-group-item-action <?php echo !$msg['is_read'] ? 'bg-light' : ''; ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo sanitize($msg['subject']); ?></h6>
                            <small class="text-muted"><?php echo formatDate($msg['created_at'], 'd M'); ?></small>
                        </div>
                        <p class="mb-1 text-truncate"><?php echo sanitize($msg['message']); ?></p>
                        <small class="text-muted">From: <?php echo sanitize($msg['name']); ?></small>
                        <?php if (!$msg['is_read']): ?>
                            <span class="badge bg-danger ms-2">New</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; else: ?>
                    <div class="list-group-item text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No messages yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Analytics -->
    <div class="col-lg-6">
        <div class="table-card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Booking Status Overview</h5>
                <a href="<?php echo ADMIN_URL; ?>/reports.php" class="btn btn-sm btn-primary">Full Reports</a>
            </div>
            <div class="card-body">
                <?php
                $statusStats = $db->query("
                    SELECT status, COUNT(*) as count 
                    FROM bookings 
                    GROUP BY status
                ")->fetchAll();
                $total = array_sum(array_column($statusStats, 'count'));
                foreach ($statusStats as $stat): 
                    $percent = $total > 0 ? round(($stat['count'] / $total) * 100, 1) : 0;
                ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="badge bg-<?php echo getStatusBadgeClass($stat['status']); ?>">
                                <?php echo getStatusLabel($stat['status']); ?>
                            </span>
                            <span class="fw-bold"><?php echo $stat['count']; ?> (<?php echo $percent; ?>%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo getStatusBadgeClass($stat['status']); ?>" style="width: <?php echo $percent; ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($statusStats)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <p>No booking data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
