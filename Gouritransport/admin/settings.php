<?php
/**
 * Gouri Transport - Admin Settings
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Website Settings';

// Get all settings
$settings = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM website_settings ORDER BY setting_group, setting_key");
    $settings = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Settings error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        redirect(ADMIN_URL . '/settings.php');
    }
    
    try {
        $db = getDB();
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO website_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        setFlashMessage('success', 'Settings saved successfully');
        redirect(ADMIN_URL . '/settings.php');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error saving settings');
    }
}

// Group settings
$groupedSettings = [];
foreach ($settings as $setting) {
    $group = $setting['setting_group'] ?: 'general';
    $groupedSettings[$group][] = $setting;
}

$groupLabels = [
    'general' => 'General Settings',
    'contact' => 'Contact Information',
    'social' => 'Social Media',
    'appearance' => 'Appearance',
    'seo' => 'SEO Settings',
    'email' => 'Email Configuration'
];

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Website Settings</h4>
</div>

<div class="table-card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <?php $first = true; foreach ($groupedSettings as $group => $items): ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                           data-mdb-tab-init data-mdb-target="#<?php echo $group; ?>"
                           role="tab">
                            <?php echo $groupLabels[$group] ?? ucfirst($group); ?>
                        </a>
                    </li>
                <?php $first = false; endforeach; ?>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content">
                <?php $first = true; foreach ($groupedSettings as $group => $items): ?>
                    <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" id="<?php echo $group; ?>" role="tabpanel">
                        <div class="row g-3">
                            <?php foreach ($items as $setting): ?>
                                <div class="col-md-6">
                                    <label class="form-label text-capitalize">
                                        <?php echo str_replace(['_', 'url'], [' ', 'URL'], $setting['setting_key']); ?>
                                    </label>
                                    <?php if (strpos($setting['setting_key'], 'description') !== false || strpos($setting['setting_key'], 'keywords') !== false || strpos($setting['setting_key'], 'message') !== false): ?>
                                        <textarea name="settings[<?php echo $setting['setting_key']; ?>]" class="form-control" rows="3"><?php echo sanitize($setting['setting_value']); ?></textarea>
                                    <?php else: ?>
                                        <input type="text" name="settings[<?php echo $setting['setting_key']; ?>]" 
                                               class="form-control" 
                                               value="<?php echo sanitize($setting['setting_value']); ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php $first = false; endforeach; ?>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
