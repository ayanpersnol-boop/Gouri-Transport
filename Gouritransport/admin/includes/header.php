<?php
/**
 * Gouri Transport - Admin Header Component
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('gouri_transport_admin');
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require admin login
requireAdminLogin();

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'staff';

// Current page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>Admin | <?php echo APP_NAME; ?></title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4a6fa5;
            --primary-dark: #3a5a8a;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f6fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-brand i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .menu-header {
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-menu a i {
            width: 24px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-navbar .btn-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-dark);
        }
        
        .content-wrapper {
            padding: 1.5rem;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        /* Tables */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table-card .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-card .table {
            margin: 0;
        }
        
        .table-card .table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .top-navbar .btn-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="<?php echo ADMIN_URL; ?>/dashboard.php">
                <i class="fas fa-truck-fast"></i>
                <div>
                    <span class="fw-bold"><?php echo APP_NAME; ?></span>
                    <small class="d-block text-white-50" style="font-size: 0.7rem;">Admin Panel</small>
                </div>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-header">Main</div>
            <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="<?php echo ADMIN_URL; ?>/bookings.php" class="<?php echo $currentPage === 'bookings' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>Bookings
                <?php 
                try {
                    $db = getDB();
                    $pending = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
                    if ($pending > 0): 
                ?>
                    <span class="badge bg-danger ms-auto"><?php echo $pending; ?></span>
                <?php endif; } catch(Exception $e) {} ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/services.php" class="<?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                <i class="fas fa-cubes"></i>Services
            </a>
            <a href="<?php echo ADMIN_URL; ?>/pricing.php" class="<?php echo $currentPage === 'pricing' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>Pricing
            </a>
            
            <div class="menu-header">Reports</div>
            <a href="<?php echo ADMIN_URL; ?>/reports.php" class="<?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>Analytics
            </a>
            <a href="<?php echo ADMIN_URL; ?>/messages.php" class="<?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>Messages
            </a>
            
            <div class="menu-header">Settings</div>
            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>Settings
            </a>
            
            <?php if ($adminRole === 'super_admin'): ?>
            <a href="<?php echo ADMIN_URL; ?>/users.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>Admin Users
            </a>
            <?php endif; ?>
            
            <a href="<?php echo ADMIN_URL; ?>/logout.php">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </nav>
    </aside>
    
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <button class="btn-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted d-none d-md-block">Welcome, <strong><?php echo $adminName; ?></strong></span>
                <a href="<?php echo APP_URL; ?>/index.php" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>View Site
                </a>
            </div>
        </header>
        
        <!-- Content Wrapper -->
        <main class="content-wrapper">
            
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
