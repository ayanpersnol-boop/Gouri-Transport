<?php
/**
 * Gouri Transport - Admin Users Management (Super Admin Only)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Admin Users';

// Check if super admin
if ($_SESSION['admin_role'] !== 'super_admin') {
    setFlashMessage('error', 'Access denied');
    redirect(ADMIN_URL . '/dashboard.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        redirect(ADMIN_URL . '/users.php');
    }
    
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = sanitize($_POST['full_name'] ?? '');
    $role = sanitize($_POST['role'] ?? 'staff');
    $phone = sanitize($_POST['phone'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $userId = intval($_POST['user_id'] ?? 0);
    
    try {
        $db = getDB();
        
        if ($userId > 0) {
            // Update
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE admin_users 
                    SET username = ?, email = ?, password_hash = ?, full_name = ?, role = ?, phone = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $passwordHash, $fullName, $role, $phone, $isActive, $userId]);
            } else {
                $stmt = $db->prepare("
                    UPDATE admin_users 
                    SET username = ?, email = ?, full_name = ?, role = ?, phone = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $fullName, $role, $phone, $isActive, $userId]);
            }
            setFlashMessage('success', 'User updated successfully');
        } else {
            // Insert
            if (empty($password)) {
                setFlashMessage('error', 'Password is required for new users');
                redirect(ADMIN_URL . '/users.php');
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO admin_users (username, email, password_hash, full_name, role, phone, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash, $fullName, $role, $phone, $isActive]);
            setFlashMessage('success', 'User created successfully');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'Error: ' . $e->getMessage());
    }
    redirect(ADMIN_URL . '/users.php');
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $_SESSION['admin_id']) {
        getDB()->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'User deleted');
    } else {
        setFlashMessage('error', 'Cannot delete yourself');
    }
    redirect(ADMIN_URL . '/users.php');
}

// Get users
$users = getDB()->query("SELECT * FROM admin_users ORDER BY id DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Admin Users</h4>
    <button type="button" class="btn btn-primary btn-sm" data-mdb-modal-init data-mdb-target="#addUser">
        <i class="fas fa-plus me-2"></i>Add User
    </button>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo sanitize($user['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo $user['username']; ?> | <?php echo $user['email']; ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'super_admin' ? 'danger' : 'info'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo $user['last_login'] ? formatDate($user['last_login'], 'd M Y H:i') : 'Never'; ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-warning" data-mdb-modal-init data-mdb-target="#editUser<?php echo $user['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Admin User</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="staff">Staff</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
