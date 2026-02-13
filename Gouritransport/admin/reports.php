<?php
/**
 * Gouri Transport - Admin Reports & Analytics
 */

// Debug - show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Reports & Analytics';

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get report data
try {
    $db = getDB();
    
    // Daily bookings count
    $dailyBookings = $db->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM bookings 
        WHERE DATE(created_at) BETWEEN '{$startDate}' AND '{$endDate}'
        GROUP BY DATE(created_at) 
        ORDER BY date DESC
        LIMIT 30
    ")->fetchAll();
    
    // Status breakdown
    $statusStats = $db->query("
        SELECT status, COUNT(*) as count 
        FROM bookings 
        WHERE DATE(created_at) BETWEEN '{$startDate}' AND '{$endDate}'
        GROUP BY status
    ")->fetchAll();
    
    // Revenue by month
    $monthlyRevenue = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(final_price) as revenue
        FROM bookings 
        WHERE status = 'delivered' AND final_price > 0
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ")->fetchAll();
    
    // Popular services
    $popularServices = $db->query("
        SELECT v.name, COUNT(*) as bookings_count
        FROM bookings b
        JOIN vehicle_types v ON b.vehicle_type_id = v.id
        WHERE DATE(b.created_at) BETWEEN '{$startDate}' AND '{$endDate}'
        GROUP BY v.id
        ORDER BY bookings_count DESC
        LIMIT 5
    ")->fetchAll();
    
    // Summary stats
    $totalBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) BETWEEN '{$startDate}' AND '{$endDate}'")->fetchColumn();
    $totalRevenue = $db->query("SELECT COALESCE(SUM(final_price), 0) FROM bookings WHERE status = 'delivered' AND DATE(created_at) BETWEEN '{$startDate}' AND '{$endDate}'")->fetchColumn();
    $avgBookingValue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;
    
} catch (Exception $e) {
    error_log("Reports error: " . $e->getMessage());
    $dailyBookings = $statusStats = $monthlyRevenue = $popularServices = [];
    $totalBookings = $totalRevenue = $avgBookingValue = 0;
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $startDate; ?>">
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $endDate; ?>">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i>
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo number_format($totalBookings); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-value"><?php echo formatPrice($totalRevenue); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value"><?php echo formatPrice($avgBookingValue); ?></div>
            <div class="stat-label">Avg. Booking Value</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value">
                <?php 
                $convRate = 0;
                if ($totalBookings > 0) {
                    $convRate = round(($totalRevenue / max($totalBookings * 1000, 1)) * 100, 1);
                }
                echo $convRate;
                ?>%
            </div>
            <div class="stat-label">Conversion Rate</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Status Breakdown -->
    <div class="col-lg-4">
        <div class="table-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Status Breakdown</h5>
            </div>
            <div class="card-body">
                <?php foreach ($statusStats as $stat): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-<?php echo getStatusBadgeClass($stat['status']); ?>">
                            <?php echo getStatusLabel($stat['status']); ?>
                        </span>
                        <span class="fw-bold"><?php echo $stat['count']; ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-<?php echo getStatusBadgeClass($stat['status']); ?>" 
                             style="width: <?php echo ($stat['count'] / max($totalBookings, 1)) * 100; ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Popular Services -->
    <div class="col-lg-4">
        <div class="table-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Popular Vehicle Types</h5>
            </div>
            <div class="card-body">
                <?php foreach ($popularServices as $svc): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><i class="fas fa-truck text-primary me-2"></i><?php echo $svc['name']; ?></span>
                        <span class="badge bg-primary"><?php echo $svc['bookings_count']; ?> bookings</span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($popularServices)): ?>
                    <p class="text-muted text-center">No data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="col-lg-4">
        <div class="table-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Monthly Revenue</h5>
            </div>
            <div class="card-body">
                <?php foreach ($monthlyRevenue as $rev): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><?php echo date('M Y', strtotime($rev['month'] . '-01')); ?></span>
                        <span class="fw-bold text-success"><?php echo formatPrice($rev['revenue']); ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($monthlyRevenue)): ?>
                    <p class="text-muted text-center">No revenue data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Daily Activity -->
<div class="table-card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Daily Booking Activity</h5>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bookings Count</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dailyBookings as $day): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($day['date'])); ?></td>
                        <td><span class="badge bg-primary"><?php echo $day['count']; ?></span></td>
                        <td>
                            <div class="progress" style="height: 8px; width: 100px;">
                                <div class="progress-bar" style="width: <?php echo min($day['count'] * 10, 100); ?>"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($dailyBookings)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No data for selected period</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
