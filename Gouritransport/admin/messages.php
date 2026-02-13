<?php
/**
 * Gouri Transport - Admin Contact Messages
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Contact Messages';

// Handle mark as read
if (isset($_GET['read'])) {
    $id = intval($_GET['read']);
    getDB()->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Message marked as read');
    redirect(ADMIN_URL . '/messages.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    getDB()->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Message deleted');
    redirect(ADMIN_URL . '/messages.php');
}

// Get messages
$messages = getDB()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Messages</h4>
    <span class="badge bg-danger"><?php echo count(array_filter($messages, fn($m) => !$m['is_read'])); ?> unread</span>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <p class="mb-1">No messages yet</p>
                            <small>Messages will appear here when customers submit the contact form</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="<?php echo $msg['is_read'] ? '' : 'table-primary'; ?>">
                        <td>
                            <?php if (!$msg['is_read']): ?>
                                <span class="badge bg-danger">New</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Read</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo sanitize($msg['name']); ?></strong><br>
                            <small><?php echo $msg['email']; ?></small>
                        </td>
                        <td><?php echo sanitize($msg['subject']); ?></td>
                        <td><?php echo formatDate($msg['created_at'], 'd M Y H:i'); ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info" data-mdb-modal-init data-mdb-target="#msg<?php echo $msg['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$msg['is_read']): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/messages.php?read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo ADMIN_URL; ?>/messages.php?delete=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Message Modals -->
<?php foreach ($messages as $msg): ?>
<div class="modal fade" id="msg<?php echo $msg['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo sanitize($msg['subject']); ?></h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>From:</strong> <?php echo sanitize($msg['name']); ?> (<?php echo $msg['email']; ?>)</p>
                <p><strong>Phone:</strong> <?php echo $msg['phone'] ?: 'N/A'; ?></p>
                <p><strong>Date:</strong> <?php echo formatDate($msg['created_at'], 'd M Y H:i'); ?></p>
                <hr>
                <p><?php echo nl2br(sanitize($msg['message'])); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Close</button>
                <a href="mailto:<?php echo $msg['email']; ?>" class="btn btn-primary">Reply via Email</a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
