<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$car_id = (int)($_GET['car_id'] ?? $_POST['car_id'] ?? 0);
$car = $car_id ? fetch_one("SELECT * FROM cars WHERE id = ?", [$car_id]) : null;

$success = false;
$receipt = null;
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

    // Re-fetch the selected car so the receipt always matches the submitted choice.
    $car = $car_id ? fetch_one("SELECT * FROM cars WHERE id = ?", [$car_id]) : null;

    if ($old['name'] === '') $errors[] = 'Full name is required.';
    if ($old['contact'] === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $old['contact'])) $errors[] = 'A valid contact number is required.';
    if ($old['email'] === '' || !valid_email($old['email'])) $errors[] = 'A valid email address is required.';
    if ($old['address'] === '') $errors[] = 'Address is required.';
    if ($car_id === 0 || !$car) $errors[] = 'Please select the vehicle you wish to purchase.';

    if (empty($errors)) {
        $msg = 'Purchase request for ' . ($car['brand'] ?? '') . ' ' . ($car['model'] ?? '') . '. Preferred contact method: ' . $old['contact_method'] . '. ' . $old['message'];

        query("INSERT INTO inquiries (car_id, customer_name, email, contact, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')",
              [$car_id, $old['name'], $old['email'], $old['contact'], trim($msg)]);

        $inquiry_id = insert_id();

        query("INSERT INTO customers (name, email, contact, address) VALUES (?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE name = VALUES(name), address = VALUES(address)",
              [$old['name'], $old['email'], $old['contact'], $old['address']]);

        $receipt = [
            'id'             => $inquiry_id,
            'ref'            => 'KT-' . date('Y') . '-' . str_pad((string)$inquiry_id, 6, '0', STR_PAD_LEFT),
            'date'           => date('F j, Y g:i A'),
            'name'           => $old['name'],
            'email'          => $old['email'],
            'contact'        => $old['contact'],
            'address'        => $old['address'],
            'contact_method' => $old['contact_method'] !== '' ? $old['contact_method'] : 'Phone',
            'message'        => $old['message'],
            'car'            => $car,
        ];

        $success = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner page-banner-image">
    <div class="container">
        <h1>Request to Purchase</h1>
        <p>Let us know you're ready to buy and our team will assist you with the process.</p>
    </div>
</section>

<section class="section">
    <div class="container form-container">
<?php if ($success && $receipt): ?>
            <div class="alert alert-success no-print">
                Your purchase request has been submitted successfully. Please save or print your receipt below.
            </div>

            <div class="receipt" id="purchaseReceipt">
                <div class="receipt-head">
                    <div class="receipt-brand-row">
                        <div class="receipt-logo-wrap">
                            <img src="<?php echo asset_uri('assets/images/KotseTayo.png'); ?>" alt="KotseTayo logo" class="receipt-logo">
                        </div>
                        <div>
                            <div class="receipt-sub">Car Buy &amp; Sell Management System<br>P Burgos St, Concepcion, Baras, Rizal &bull; +63 (917) 555-4321</div>
                        </div>
                    </div>
                    <div class="receipt-meta">
                        <strong>Purchase Request Receipt</strong>
                        <span>Ref: <?php echo e($receipt['ref']); ?></span>
                        <span><?php echo e($receipt['date']); ?></span>
                        <span class="badge status-pending">Pending</span>
                    </div>
                </div>

                <div class="receipt-section">
                    <h3>Vehicle</h3>
                    <table class="receipt-table">
                        <tr>
                            <th>Vehicle</th>
                            <td><?php echo e($receipt['car']['brand'] . ' ' . $receipt['car']['model']); ?></td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td><?php echo e($receipt['car']['year']); ?></td>
                        </tr>
                        <tr>
                            <th>Details</th>
                            <td><?php echo e($receipt['car']['transmission'] . ' / ' . $receipt['car']['fuel_type'] . ' / ' . $receipt['car']['color']); ?></td>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <td><strong><?php echo format_money($receipt['car']['price']); ?></strong></td>
                        </tr>
                    </table>
                </div>

                <div class="receipt-section">
                    <h3>Buyer</h3>
                    <table class="receipt-table">
                        <tr><th>Name</th><td><?php echo e($receipt['name']); ?></td></tr>
                        <tr><th>Email</th><td><?php echo e($receipt['email']); ?></td></tr>
                        <tr><th>Contact</th><td><?php echo e($receipt['contact']); ?></td></tr>
                        <tr><th>Address</th><td><?php echo e($receipt['address']); ?></td></tr>
                        <tr><th>Contact via</th><td><?php echo e($receipt['contact_method']); ?></td></tr>
                        <?php if ($receipt['message'] !== ''): ?>
                        <tr><th>Message</th><td><?php echo nl2br(e($receipt['message'])); ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>

                <p class="receipt-note">This is an acknowledgment of your purchase request, not an official sales invoice. No online payment was processed. Our team will contact you to arrange inspection, documents, and payment manually. Please present this reference <strong><?php echo e($receipt['ref']); ?></strong> (Inquiry #<?php echo (int)$receipt['id']; ?>) when you visit or call.</p>

                <div class="receipt-sign">
                    <div class="sign-box">
                        <div class="sign-space"></div>
                        <div class="sign-line"></div>
                        <strong><?php echo e($receipt['name']); ?></strong>
                        <span>Customer signature over printed name</span>
                    </div>
                    <div class="sign-box">
                        <div class="sign-space"></div>
                        <div class="sign-line"></div>
                        <strong>Authorized Representative</strong>
                        <span>KotseTayo signature over printed name / Date</span>
                    </div>
                </div>
            </div>

            <div class="receipt-actions no-print">
                <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">Print Receipt</button>
                <a class="btn btn-outline" href="purchase-receipt.php?id=<?php echo (int)$receipt['id']; ?>">Open Printable Version</a>
                <a class="btn btn-outline" href="cars.php">Browse More Cars</a>
            </div>
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