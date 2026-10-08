<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$success = false;
$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = clean($_POST['name'] ?? '');
    $old['email']   = clean($_POST['email'] ?? '');
    $old['subject'] = clean($_POST['subject'] ?? '');
    $old['message'] = clean($_POST['message'] ?? '');

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if ($old['email'] === '' || !valid_email($old['email'])) $errors[] = 'A valid email address is required.';
    if ($old['subject'] === '') $errors[] = 'Subject is required.';
    if ($old['message'] === '') $errors[] = 'Message is required.';

    if (empty($errors)) {
        query("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)",
              [$old['name'], $old['email'], $old['subject'], $old['message']]);
        $success = true;
        $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We're here to help you find the right car.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div class="contact-info">
            <div class="contact-block"><h3>Visit Us</h3><p>1234 Auto Plaza, Business District, City</p></div>
            <div class="contact-block"><h3>Call Us</h3><p>(02) 1234 5678<br>+63 912 345 6789</p></div>
            <div class="contact-block"><h3>Email Us</h3><p>sales@carbuy.example.com</p></div>
            <div class="contact-block"><h3>Business Hours</h3><p>Mon - Sat: 9:00 AM - 6:00 PM<br>Sunday: Closed</p></div>
        </div>

        <div class="form-container">
            <?php if ($success): ?>
                <div class="alert alert-success">Thank you! Your message has been sent. We will get back to you soon.</div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="post" action="contact.php" class="form" id="contactForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo e($old['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo e($old['email']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" value="<?php echo e($old['subject']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required><?php echo e($old['message']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title-center">Find Us Here</h2>
        <div class="map-placeholder">
            <!-- Google Maps placeholder - replace with an embedded map if desired. -->
            <p>&#128205; Google Maps placeholder</p>
            <span>1234 Auto Ave, Business District, City</span>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>