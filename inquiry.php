<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$car_id = (int)($_GET['car_id'] ?? $_POST['car_id'] ?? 0);
$car = $car_id ? fetch_one("SELECT * FROM cars WHERE id = ?", [$car_id]) : null;

$success = false;
$errors = [];
$old = ['customer_name' => '', 'email' => '', 'contact' => ''];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['customer_name'] = clean($_POST['customer_name'] ?? '');
    $old['email']         = clean($_POST['email'] ?? '');
    $old['contact']       = clean($_POST['contact'] ?? '');
    $message              = clean($_POST['message'] ?? '');
    $car_id               = (int)($_POST['car_id'] ?? 0);

    if ($old['customer_name'] === '') $errors[] = 'Full name is required.';
    if ($old['email'] === '' || !valid_email($old['email'])) $errors[] = 'A valid email address is required.';
    if ($old['contact'] === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $old['contact'])) $errors[] = 'A valid contact number is required.';
    if ($car_id === 0) $errors[] = 'Please select a vehicle for your inquiry.';
    if ($message === '') $errors[] = 'Message is required.';

    if (empty($errors)) {
        query("INSERT INTO inquiries (car_id, customer_name, email, contact, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')",
              [$car_id, $old['customer_name'], $old['email'], $old['contact'], $message]);

        // Add / update the customer record.
        query("INSERT INTO customers (name, email, contact, address) VALUES (?, ?, ?, '')
               ON DUPLICATE KEY UPDATE name = VALUES(name)",
              [$old['customer_name'], $old['email'], $old['contact']]);

        $success = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Send an Inquiry</h1>
        <p>Tell us which vehicle you're interested in and our team will get back to you.</p>
    </div>
</section>

<section class="section">
    <div class="container form-container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                Your inquiry has been submitted successfully. Our team will contact you shortly.
            </div>
            <p><a class="btn btn-primary" href="cars.php">Browse More Cars</a></p>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="post" action="inquiry.php" class="form" id="inquiryForm" novalidate>
                <div class="form-group">
                    <label for="customer_name">Full Name *</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo e($old['customer_name']); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo e($old['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number *</label>
                        <input type="tel" id="contact" name="contact" value="<?php echo e($old['contact']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="car_id_select">Interested Vehicle *</label>
                    <select name="car_id" id="car_id_select">
                        <option value="">-- Select a vehicle --</option>
                        <?php
                        $avail = fetch_all("SELECT * FROM cars WHERE status = 'Available' ORDER BY brand");
                        foreach ($avail as $ac):
                        ?>
                        <option value="<?php echo (int)$ac['id']; ?>" <?php echo $car_id === (int)$ac['id'] ? 'selected' : ''; ?>>
                            <?php echo e($ac['brand'] . ' ' . $ac['model'] . ' (' . $ac['year'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required><?php echo e($message); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Submit Inquiry</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>