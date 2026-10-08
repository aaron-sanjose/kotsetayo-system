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

<section class="page-banner page-banner-image">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We're here to help you find the right car.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div class="contact-info">
            <div class="contact-block"><h3>Visit Us</h3><p>P Burgos St, Concepcion, Baras, Rizal</p></div>
            <div class="contact-block"><h3>Call Us</h3><p>+63 (2) 8911-2233<br>+63 (917) 555-4321</p></div>
            <div class="contact-block"><h3>Email Us</h3><p>sales@kotsetayo.example.com</p></div>
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
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1931.1819224157562!2d121.26478036758665!3d14.521168739056268!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ea79246bf14f%3A0x9d0c7a543cf6a878!2sP%20Burgos%20St%2C%20Concepcion%2C%20Baras%2C%20Rizal!5e0!3m2!1sen!2sph!4v1789011553984!5m2!1sen!2sph" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
