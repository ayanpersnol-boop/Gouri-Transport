<?php
/**
 * Gouri Transport - Admin Login
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name('gouri_transport_admin');
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Update last login
                $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Redirect
                $redirect = $_SESSION['redirect_after_login'] ?? ADMIN_URL . '/dashboard.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
            error_log("Admin login error: " . $e->getMessage());
        }
    }
}

// Check for logout message
if (isset($_GET['logout']) && isset($_GET['msg'])) {
    $error = 'You have been logged out successfully.';
}
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo APP_NAME; ?></title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4a6fa5;
            --primary-dark: #3a5a8a;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-outline .form-control:focus ~ .form-notch .form-notch-leading,
        .form-outline .form-control:focus ~ .form-notch .form-notch-middle,
        .form-outline .form-control:focus ~ .form-notch .form-notch-trailing {
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .back-to-site {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
        }
        
        .back-to-site:hover {
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>
    <a href="<?php echo APP_URL; ?>/index.php" class="back-to-site">
        <i class="fas fa-arrow-left me-2"></i>Back to Website
    </a>
    
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-truck-fast"></i>
            <h4 class="mb-0"><?php echo APP_NAME; ?></h4>
            <small>Admin Panel</small>
        </div>
        
        <div class="login-body">
            <h5 class="text-center mb-4">Welcome Back!</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-<?php echo strpos($error, 'successfully') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-outline mb-4" data-mdb-input-init>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                    <label class="form-label" for="username">Username</label>
                </div>
                
                <div class="form-outline mb-4" data-mdb-input-init>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <label class="form-label" for="password">Password</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100 btn-rounded">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    Default: admin / admin123<br>
                    Please change after first login.
                </small>
            </div>
        </div>
    </div>
    
    <!-- MDBootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
</body>
</html>
