-- Gouri Transport - Complete Database Schema
-- MySQL Database for Transport & Logistics Service Website

-- Create Database
CREATE DATABASE IF NOT EXISTS gouri_transport 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gouri_transport;

-- ============================================
-- 1. ADMIN USERS TABLE
-- ============================================
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'staff') DEFAULT 'staff',
    phone VARCHAR(20),
    avatar VARCHAR(255),
    last_login DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. SERVICES TABLE
-- ============================================
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(255),
    icon VARCHAR(50) DEFAULT 'truck',
    image VARCHAR(255),
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. VEHICLE TYPES TABLE
-- ============================================
CREATE TABLE vehicle_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    capacity_weight DECIMAL(10, 2),
    capacity_volume DECIMAL(10, 2),
    base_price DECIMAL(10, 2),
    per_km_rate DECIMAL(10, 2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. BOOKINGS TABLE
-- ============================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    delivery_location VARCHAR(255) NOT NULL,
    vehicle_type_id INT,
    goods_description TEXT,
    delivery_date DATE,
    message TEXT,
    status ENUM('pending', 'confirmed', 'in_progress', 'delivered', 'cancelled') DEFAULT 'pending',
    estimated_price DECIMAL(10, 2),
    final_price DECIMAL(10, 2),
    distance_km DECIMAL(8, 2),
    assigned_driver VARCHAR(100),
    driver_phone VARCHAR(20),
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. PRICING PLANS TABLE
-- ============================================
CREATE TABLE pricing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    base_price DECIMAL(10, 2) NOT NULL,
    per_km_rate DECIMAL(10, 2) NOT NULL,
    max_weight DECIMAL(10, 2),
    features JSON,
    is_popular BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. FREIGHT PRICING TABLE
-- ============================================
CREATE TABLE freight_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_from VARCHAR(100) NOT NULL,
    route_to VARCHAR(100) NOT NULL,
    vehicle_type_id INT,
    base_price DECIMAL(10, 2) NOT NULL,
    per_km_rate DECIMAL(10, 2) NOT NULL,
    estimated_days INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. TRACKING STATUS TABLE
-- ============================================
CREATE TABLE tracking_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    location VARCHAR(255),
    description TEXT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. TESTIMONIALS TABLE
-- ============================================
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_title VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    content TEXT NOT NULL,
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 9. FAQ TABLE
-- ============================================
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 10. WEBSITE SETTINGS TABLE
-- ============================================
CREATE TABLE website_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 11. CONTACT MESSAGES TABLE
-- ============================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 12. EMAIL NOTIFICATIONS LOG
-- ============================================
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(100) NOT NULL,
    recipient_name VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Admin User (password: admin123 - CHANGE THIS!)
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@gouritransport.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin');

-- Default Vehicle Types
INSERT INTO vehicle_types (name, description, capacity_weight, capacity_volume, base_price, per_km_rate) VALUES
('Truck', 'Heavy duty truck for large cargo', 10000.00, 50.00, 1500.00, 45.00),
('Mini Truck', 'Medium cargo transportation', 3000.00, 20.00, 800.00, 25.00),
('Container', 'Container shipping for bulk goods', 25000.00, 70.00, 3000.00, 35.00),
('Pickup', 'Small deliveries and parcels', 1000.00, 8.00, 400.00, 15.00),
('Refrigerated Truck', 'Temperature controlled transport', 8000.00, 40.00, 2500.00, 50.00),
('Flatbed Truck', 'Oversized and heavy equipment', 15000.00, 60.00, 2000.00, 40.00);

-- Default Services
INSERT INTO services (title, slug, description, short_description, icon, features) VALUES
('Road Transport', 'road-transport', 'Complete road transportation solutions for all types of cargo. We ensure safe and timely delivery across cities and states with our modern fleet of vehicles.', 'Reliable road freight services across the country', 'truck', '["Nationwide Coverage", "GPS Tracking", "Real-time Updates", "Secure Handling"]'),
('Freight Services', 'freight-services', 'Full truckload and less-than-truckload freight services tailored to your business needs. Cost-effective solutions for bulk shipments.', 'Full and partial truckload freight solutions', 'shipping-fast', '["FTL & LTL Options", "Competitive Rates", "Flexible Scheduling", "Insurance Coverage"]'),
('Logistics & Warehousing', 'logistics-warehousing', 'End-to-end logistics management with secure warehousing facilities. Storage, inventory management, and distribution services.', 'Complete logistics and storage solutions', 'warehouse', '["Secure Storage", "Inventory Management", "Distribution Services", "Climate Control"]'),
('Express Delivery', 'express-delivery', 'Time-critical delivery services for urgent shipments. Same-day and next-day delivery options available for priority cargo.', 'Fast and reliable express delivery', 'bolt', '["Same Day Delivery", "Next Day Options", "Priority Handling", "24/7 Service"]'),
('Heavy Equipment Transport', 'heavy-equipment', 'Specialized transportation for construction equipment, machinery, and oversized loads. Expert handling with proper permits.', 'Safe transport of heavy machinery', 'truck-moving', '["Expert Handling", "Route Planning", "Permit Assistance", "Insurance Included"]'),
('Temperature Controlled', 'temperature-controlled', 'Refrigerated transport for perishable goods, pharmaceuticals, and temperature-sensitive cargo. Maintains optimal conditions throughout.', 'Climate-controlled cargo transport', 'temperature-low', '["Precise Temperature Control", "Pharma Certified", "Real-time Monitoring", "HACCP Compliant"]');

-- Default Pricing Plans
INSERT INTO pricing_plans (name, description, base_price, per_km_rate, max_weight, features, is_popular) VALUES
('Basic', 'Perfect for small businesses and occasional shipping needs', 500.00, 20.00, 1000.00, '["Local delivery", "Basic tracking", "Email support", "Standard insurance"]',
 FALSE),
('Standard', 'Most popular choice for regular shipping requirements', 1000.00, 35.00, 5000.00, '["Nationwide delivery", "Real-time GPS tracking", "24/7 Phone support", "Enhanced insurance", "Priority handling"]',
 TRUE),
('Premium', 'Enterprise-grade solution for high-volume logistics', 2500.00, 50.00, 25000.00, '["All routes covered", "Advanced tracking suite", "Dedicated account manager", "Full insurance coverage", "Express options", "Warehouse storage"]',
 FALSE);

-- Default Testimonials
INSERT INTO testimonials (customer_name, customer_title, rating, content) VALUES
('Rajesh Sharma', 'CEO, Sharma Trading Co.', 5, 'Gouri Transport has been our logistics partner for 3 years. Their reliability and professional service have helped our business grow. Highly recommended!'),
('Priya Patel', 'Operations Manager, Fresh Foods Ltd', 5, 'The temperature-controlled transport service is exceptional. Our perishable goods always arrive in perfect condition. Great team to work with!'),
('Amit Kumar', 'Owner, Kumar Constructions', 4, 'Excellent heavy equipment transport service. They handled our machinery with care and delivered on time. Professional crew and competitive pricing.'),
('Sneha Gupta', 'Logistics Head, Tech Solutions', 5, 'Their express delivery service saved us during a critical shipment deadline. Fast, reliable, and great customer support. Will definitely use again!');

-- Default FAQs
INSERT INTO faqs (question, answer, category, sort_order) VALUES
('How do I book a transport service?', 'You can easily book through our website by filling out the booking form on the "Book Now" page. Alternatively, you can call our customer service team at +91 1234567890 for assistance.', 'General', 1),
('What areas do you cover?', 'We provide transportation services across all major cities and towns in India. We also offer international freight services to select countries. Contact us for specific route inquiries.', 'Coverage', 2),
('How is the pricing calculated?', 'Our pricing is based on distance, vehicle type, weight/volume of goods, and any special requirements. You can get an instant quote by filling out our booking form with your shipment details.', 'Pricing', 3),
('Do you provide tracking for shipments?', 'Yes, all shipments come with real-time GPS tracking. Once your booking is confirmed, you\'ll receive a tracking ID to monitor your shipment status anytime.', 'Tracking', 4),
('What types of vehicles do you have?', 'Our fleet includes trucks, mini trucks, containers, refrigerated trucks, flatbed trucks, and pickup vehicles to handle various cargo types and sizes.', 'Services', 5),
('Is my cargo insured?', 'Yes, all shipments include basic insurance coverage. We also offer enhanced insurance options for high-value goods. Please discuss insurance requirements when booking.', 'Insurance', 6),
('How do I track my shipment?', 'Enter your tracking ID on our "Track Shipment" page to see real-time status updates, current location, and estimated delivery time.', 'Tracking', 7),
('What if my goods are damaged during transport?', 'While we take utmost care in handling your goods, in the rare event of damage, please contact us immediately. Our insurance coverage will handle valid claims as per our terms.', 'Insurance', 8);

-- Default Website Settings
INSERT INTO website_settings (setting_key, setting_value, setting_group) VALUES
('company_name', 'Gouri Transport', 'general'),
('company_tagline', 'Fast, Reliable & Secure Transport Services', 'general'),
('company_description', 'Delivering goods safely across cities and countries with modern fleet and professional service.', 'general'),
('contact_email', 'info@gouritransport.com', 'contact'),
('contact_phone', '+91 1234567890', 'contact'),
('contact_address', '123 Transport Nagar, Mumbai, Maharashtra 400001', 'contact'),
('working_hours', 'Mon - Sat: 8:00 AM - 8:00 PM', 'contact'),
('facebook_url', 'https://facebook.com/gouritransport', 'social'),
('twitter_url', 'https://twitter.com/gouritransport', 'social'),
('instagram_url', 'https://instagram.com/gouritransport', 'social'),
('linkedin_url', 'https://linkedin.com/company/gouritransport', 'social'),
('default_theme', 'light', 'appearance'),
('logo_light', 'assets/images/logo-light.png', 'appearance'),
('logo_dark', 'assets/images/logo-dark.png', 'appearance'),
('favicon', 'assets/images/favicon.ico', 'appearance'),
('meta_title', 'Gouri Transport - Reliable Logistics & Transport Services', 'seo'),
('meta_description', 'Professional transport and logistics services. Road freight, warehousing, express delivery, and more. Book your shipment today!', 'seo'),
('meta_keywords', 'transport, logistics, freight, shipping, delivery, truck, cargo, warehouse', 'seo'),
('smtp_host', 'smtp.gmail.com', 'email'),
('smtp_port', '587', 'email'),
('smtp_username', '', 'email'),
('smtp_password', '', 'email'),
('admin_notification_email', 'admin@gouritransport.com', 'email');
