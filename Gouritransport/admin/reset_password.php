<?php
/**
 * Password reset script for admin
 * Run this once to set correct password
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';

$correctHash = password_hash('admin123', PASSWORD_DEFAULT);

try {
    $db = getDB();
    
    // Update admin password
    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$correctHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Admin password updated successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "New Hash: " . substr($correctHash, 0, 20) . "...<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        // Check if admin exists
        $stmt = $db->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Create admin user
            $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute(['admin', 'admin@gouritransport.com', $correctHash, 'Super Admin', 'super_admin']);
            echo "✓ Admin user created successfully!<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
            echo "<a href='login.php'>Go to Login</a>";
        } else {
            echo "Admin exists but password wasn't updated. Try running again.";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
