<?php
/**
 * Gouri Transport - Header Component
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Get settings
$companyName = getSetting('company_name', 'Gouri Transport');
$contactPhone = getSetting('contact_phone', '+91 1234567890');
$contactEmail = getSetting('contact_email', 'info@gouritransport.com');

// Check current theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : getSetting('default_theme', 'light');

// Get current page for active nav
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentPage = $currentPage === 'index' ? 'home' : $currentPage;
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo getSetting('meta_description', 'Professional transport and logistics services. Road freight, warehousing, express delivery, and more.'); ?>">
    <meta name="keywords" content="<?php echo getSetting('meta_keywords', 'transport, logistics, freight, shipping, delivery, truck, cargo'); ?>">
    <meta name="author" content="<?php echo $companyName; ?>">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?><?php echo $companyName; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo ASSETS_URL; ?>/css/style.css" rel="stylesheet">
    
    <?php if (isset($extraCss)): ?>
        <?php echo $extraCss; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $theme; ?>-mode">

    <!-- Top Bar (Desktop Only) -->
    <div class="top-bar d-none d-lg-block">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center py-2">
                <div class="d-flex gap-4">
                    <a href="tel:<?php echo $contactPhone; ?>" class="text-white text-decoration-none small">
                        <i class="fas fa-phone me-2"></i><?php echo $contactPhone; ?>
                    </a>
                    <a href="mailto:<?php echo $contactEmail; ?>" class="text-white text-decoration-none small">
                        <i class="fas fa-envelope me-2"></i><?php echo $contactEmail; ?>
                    </a>
                </div>
                <div class="d-flex gap-3">
                    <a href="<?php echo getSetting('facebook_url', '#'); ?>" class="text-white" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo getSetting('twitter_url', '#'); ?>" class="text-white" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="<?php echo getSetting('instagram_url', '#'); ?>" class="text-white" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="<?php echo getSetting('linkedin_url', '#'); ?>" class="text-white" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-1">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/index.php">
                <div class="logo-icon me-2">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <div>
                    <span class="fw-bold text-primary"><?php echo $companyName; ?></span>
                    <small class="d-block text-muted" style="font-size: 0.7rem;">Transport & Logistics</small>
                </div>
            </a>
            
            <!-- Mobile Menu Button -->
            <button class="navbar-toggler" type="button" data-mdb-collapse-init data-mdb-target="#mainNavbar">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php">
                            <i class="fas fa-home me-1 d-lg-none"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'services' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/services.php">
                            <i class="fas fa-cubes me-1 d-lg-none"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'pricing' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pricing.php">
                            <i class="fas fa-tags me-1 d-lg-none"></i>Pricing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'track' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/track.php">
                            <i class="fas fa-map-marker-alt me-1 d-lg-none"></i>Track
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/contact.php">
                            <i class="fas fa-phone me-1 d-lg-none"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3 ms-lg-4 mt-3 mt-lg-0">
                    <!-- Theme Toggle -->
                    <button class="btn btn-link btn-floating theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-<?php echo $theme === 'dark' ? 'sun' : 'moon'; ?>"></i>
                    </button>
                    
                    <!-- Book Now Button -->
                    <a href="<?php echo APP_URL; ?>/booking.php" class="btn btn-primary btn-rounded">
                        <i class="fas fa-calendar-check me-2"></i>Book Now
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content">
