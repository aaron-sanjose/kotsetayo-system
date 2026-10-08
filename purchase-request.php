<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$car_id = (int)($_GET['car_id'] ?? $_POST['car_id'] ?? 0);
$car = $car_id ? fetch_one("SELECT * FROM cars WHERE id = ?", [$car_id]) : null;

$success = false;
$errors = [];
$old = ['name' => '', 'email' => '', 'contact' => '', 'address' => '', 'contact_method' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']            = clean($_POST['name'] ?? '');
    $old['email']           = clean($_POST['email'] ?? '');
    $old['contact']         = clean($_POST['contact'] ?? '');
    $old['address']         = clean($_POST['address'] ?? '');
    $old['contact_method']  = clean($_POST['contact_method'] ?? '');
    $old['message']         = clean($_POST['message'] ?? '');
    $car_id                 = (int)($_POST['car_id'] ?? 0);

    if ($old['name'] === '') $errors[] = 'Full name is required.';
    if ($old['contact'] === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $old['contact'])) $errors[] = 'A valid contact number is required.';
    if ($old['email'] === '' || !valid_email($old['email'])) $errors[] = 'A valid email address is required.';
    if ($old['address'] === '') $errors[] = 'Address is required.';
    if ($car_id === 0) $errors[] = 'Please select the vehicle you wish to purchase.';

    if (empty($errors)) {
        $msg = 'Purchase request for ' . ($car['brand'] ?? '') . ' ' . ($car['model'] ?? '') . '. Preferred contact method: ' . $old['contact_method'] . '. ' . $old['message'];

        query("INSERT INTO inquiries (car_id, customer_name, email, contact, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')",
              [$car_id, $old['name'], $old['email'], $old['contact'], trim($msg)]);

        query("INSERT INTO customers (name, email, contact, address) VALUES (?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE name = VALUES(name), address = VALUES(address)",
              [$old['name'], $old['email'], $old['contact'], $old['address']]);

        $success = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Request to Purchase</h1>
        <p>Let us know you're ready to buy and our team will assist you with the process.</p>
    </div>
</section>

<section class="section">
    <div class="container form-container">
<?php if ($success): ?>
            <div class="alert alert-success">
                Your purchase request has been submitted successfully. Our team will contact you shortly to complete the purchase.
            </div>
            <p><a class="btn btn-primary" href="cars.php">Browse More Cars</a></p>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="post" action="purchase-request.php" class="form" id="purchaseForm" novalidate>
                <div class="form-group">
                    <label>Selected Vehicle</label>
                    <select name="car_id" id="purchase_car_id" required>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo e($old['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number *</label>
                        <input type="tel" id="contact" name="contact" value="<?php echo e($old['contact']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo e($old['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_method">Preferred Contact Method</label>
                        <select name="contact_method" id="contact_method">
                            <option value="Phone" <?php echo $old['contact_method'] === 'Phone' ? 'selected' : ''; ?>>Phone</option>
                            <option value="Email" <?php echo $old['contact_method'] === 'Email' ? 'selected' : ''; ?>>Email</option>
                            <option value="SMS" <?php echo $old['contact_method'] === 'SMS' ? 'selected' : ''; ?>>SMS</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address *</label>
                    <textarea id="address" name="address" rows="2" required><?php echo e($old['address']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="message">Additional Message</label>
                    <textarea id="message" name="message" rows="4"><?php echo e($old['message']); ?></textarea>
                </div>
                <p class="form-note">No online payment is processed here. Our team will contact you to arrange the sale manually.</p>
                <button type="submit" class="btn btn-primary btn-lg">Submit Purchase Request</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>