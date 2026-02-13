<?php
/**
 * Gouri Transport - Contact Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us';

// Get settings
$contactAddress = getSetting('contact_address', '123 Transport Nagar, Mumbai, Maharashtra 400001');
$contactPhone = getSetting('contact_phone', '+91 1234567890');
$contactEmail = getSetting('contact_email', 'info@gouritransport.com');
$workingHours = getSetting('working_hours', 'Mon - Sat: 8:00 AM - 8:00 PM');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid form submission.');
        redirect($_SERVER['REQUEST_URI']);
    }
    
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        setFlashMessage('error', 'Please fill in all required fields.');
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            // Send notification to admin
            $emailBody = "
                <h3>New Contact Message</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>{$message}</p>
            ";
            sendEmail(ADMIN_EMAIL, "New Contact Message: {$subject}", $emailBody, $name);
            
            setFlashMessage('success', 'Thank you for your message! We will get back to you soon.');
            redirect($_SERVER['REQUEST_URI']);
        } catch (Exception $e) {
            setFlashMessage('error', 'Something went wrong. Please try again.');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                <p class="lead mb-0">Get in touch with our team for any inquiries or support</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="booking-form-card">
                    <h3 class="mb-4">Send Us a Message</h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                    <label class="form-label" for="name">Your Name *</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                    <label class="form-label" for="email">Email Address *</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="tel" id="phone" name="phone" class="form-control">
                                    <label class="form-label" for="phone">Phone Number</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="subject" name="subject" class="form-control" required>
                                    <label class="form-label" for="subject">Subject *</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-outline" data-mdb-input-init>
                                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                                    <label class="form-label" for="message">Your Message *</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg btn-rounded w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-5">
                <h4 class="mb-4">Contact Information</h4>
                
                <div class="contact-info-card shadow-hover">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h6 class="mb-1">Office Address</h6>
                        <p class="mb-0 text-muted"><?php echo $contactAddress; ?></p>
                    </div>
                </div>
                
                <div class="contact-info-card shadow-hover">
                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <h6 class="mb-1">Phone</h6>
                        <p class="mb-0"><a href="tel:<?php echo $contactPhone; ?>" class="text-muted"><?php echo $contactPhone; ?></a></p>
                    </div>
                </div>
                
                <div class="contact-info-card shadow-hover">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h6 class="mb-1">Email</h6>
                        <p class="mb-0"><a href="mailto:<?php echo $contactEmail; ?>" class="text-muted"><?php echo $contactEmail; ?></a></p>
                    </div>
                </div>
                
                <div class="contact-info-card shadow-hover">
                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h6 class="mb-1">Working Hours</h6>
                        <p class="mb-0 text-muted"><?php echo $workingHours; ?></p>
                    </div>
                </div>
                
                <!-- Social Links -->
                <h5 class="mt-5 mb-3">Follow Us</h5>
                <div class="d-flex gap-2">
                    <a href="<?php echo getSetting('facebook_url', '#'); ?>" class="btn btn-primary btn-floating" target="_blank">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="<?php echo getSetting('twitter_url', '#'); ?>" class="btn btn-info btn-floating" target="_blank">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="<?php echo getSetting('instagram_url', '#'); ?>" class="btn btn-danger btn-floating" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="<?php echo getSetting('linkedin_url', '#'); ?>" class="btn btn-primary btn-floating" target="_blank">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Link -->
<section class="section-padding">
    <div class="container text-center">
        <h3 class="mb-3">Have Questions?</h3>
        <p class="lead mb-4">Check out our frequently asked questions for quick answers</p>
        <a href="<?php echo APP_URL; ?>/index.php#faq" class="btn btn-outline-primary btn-lg btn-rounded">
            <i class="fas fa-question-circle me-2"></i>View FAQs
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
