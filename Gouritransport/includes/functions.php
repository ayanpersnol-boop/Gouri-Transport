<?php
/**
 * Gouri Transport - Helper Functions
 */

// Include configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('gouri_transport_admin');
    session_start();
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize Input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate Tracking ID
 */
function generateTrackingId() {
    $prefix = 'GTR';
    $date = date('Ymd');
    $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
    return $prefix . $date . $random;
}

/**
 * Send Email
 */
function sendEmail($to, $subject, $body, $toName = '') {
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Log email to database
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, body, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$to, $toName, $subject, $body]);
        $emailId = $db->lastInsertId();
        
        // Try to send email
        $mailSent = mail($to, $subject, $body, $headers);
        
        if ($mailSent) {
            $stmt = $db->prepare("UPDATE email_logs SET status = 'sent', sent_at = NOW() WHERE id = ?");
            $stmt->execute([$emailId]);
            return true;
        } else {
            $stmt = $db->prepare("UPDATE email_logs SET status = 'failed', error_message = 'PHP mail() failed' WHERE id = ?");
            $stmt->execute([$emailId]);
            return false;
        }
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send Admin Notification Email
 */
function sendAdminNotification($bookingId, $trackingId, $customerName, $customerEmail, $customerPhone) {
    $subject = "New Booking Request - " . $trackingId;
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2 style='color: #4a6fa5;'>New Booking Request Received</h2>
        <p>A new booking has been submitted on your website.</p>
        
        <table style='border-collapse: collapse; width: 100%; max-width: 600px;'>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Booking ID:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>#{$bookingId}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Tracking ID:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$trackingId}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Customer Name:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$customerName}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Email:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$customerEmail}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Phone:</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$customerPhone}</td>
            </tr>
        </table>
        
        <p style='margin-top: 20px;'>
            <a href='" . ADMIN_URL . "/bookings.php?id={$bookingId}' style='background: #4a6fa5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Booking</a>
        </p>
    </body>
    </html>
    ";
    
    return sendEmail(ADMIN_EMAIL, $subject, $body, 'Admin');
}

/**
 * Send Customer Confirmation Email
 */
function sendCustomerConfirmation($to, $name, $trackingId) {
    $subject = "Your Booking Confirmation - " . $trackingId;
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2 style='color: #4a6fa5;'>Thank You for Your Booking!</h2>
        <p>Dear {$name},</p>
        <p>Your transport booking has been received successfully. Here are your booking details:</p>
        
        <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
            <p style='font-size: 18px; margin: 0;'><strong>Tracking ID:</strong> {$trackingId}</p>
        </div>
        
        <p>You can track your shipment status anytime by visiting our tracking page and entering your Tracking ID.</p>
        
        <p>
            <a href='" . APP_URL . "/track.php?id={$trackingId}' style='background: #4a6fa5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Track Your Shipment</a>
        </p>
        
        <p>Our team will review your request and contact you shortly to confirm the details.</p>
        
        <p>If you have any questions, please don't hesitate to contact us at {$GLOBALS['contact_phone']} or reply to this email.</p>
        
        <p>Best regards,<br><strong>Gouri Transport Team</strong></p>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $body, $name);
}

/**
 * Format Price
 */
function formatPrice($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Format Date
 */
function formatDate($date, $format = 'd M, Y') {
    return date($format, strtotime($date));
}

/**
 * Get Status Badge Class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-info',
        'in_progress' => 'badge-primary',
        'delivered' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Get Status Label
 */
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    return $labels[$status] ?? ucfirst($status);
}

/**
 * Flash Messages
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Check if user is admin logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin login
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(ADMIN_URL . '/login.php');
    }
}

/**
 * Get Website Setting
 */
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM website_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Truncate Text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get Current URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Generate Pagination
 */
function generatePagination($totalItems, $itemsPerPage, $currentPage, $urlPattern) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $disabled = $currentPage <= 1 ? 'disabled' : '';
    $prevUrl = str_replace('{page}', $currentPage - 1, $urlPattern);
    $html .= "<li class=\"page-item {$disabled}\"><a class=\"page-link\" href=\"{$prevUrl}\">&laquo;</a></li>";
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        $url = str_replace('{page}', $i, $urlPattern);
        $html .= "<li class=\"page-item {$active}\"><a class=\"page-link\" href=\"{$url}\">{$i}</a></li>";
    }
    
    // Next button
    $disabled = $currentPage >= $totalPages ? 'disabled' : '';
    $nextUrl = str_replace('{page}', $currentPage + 1, $urlPattern);
    $html .= "<li class=\"page-item {$disabled}\"><a class=\"page-link\" href=\"{$nextUrl}\">&raquo;</a></li>";
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Upload File
 */
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }
    
    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $uploadPath = $destination . '/' . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'file_name' => $newFileName];
    }
    
    return ['success' => false, 'error' => 'Move failed'];
}

/**
 * Log Activity
 */
function logActivity($action, $details = '') {
    if (!isAdminLoggedIn()) return;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
