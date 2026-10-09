<?php
/**
 * KotseTayo - Printable Purchase Request Receipt
 * Opened after submitting purchase-request.php?id=<inquiry_id>
 * or re-opened later with the reference number.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    die('Receipt not found. Please check your reference link.');
}

$row = fetch_one(
    "SELECT i.*, c.brand, c.model, c.year, c.price, c.transmission, c.fuel_type, c.color, c.engine
     FROM inquiries i
     LEFT JOIN cars c ON c.id = i.car_id
     WHERE i.id = ?",
    [$id]
);

if (!$row) {
    http_response_code(404);
    die('Receipt not found. Please check your reference link.');
}

$ref = 'KT-' . date('Y', strtotime($row['created_at'])) . '-' . str_pad((string)$row['id'], 6, '0', STR_PAD_LEFT);
$auto_print = isset($_GET['print']);

include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container form-container">
        <div class="receipt-actions no-print" style="margin-bottom:20px">
            <a class="btn btn-outline btn-sm" href="purchase-request.php">&larr; Back</a>
            <a class="btn btn-outline btn-sm" href="cars.php">Browse Cars</a>
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
                    <span>Ref: <?php echo e($ref); ?></span>
                    <span><?php echo e(date('F j, Y g:i A', strtotime($row['created_at']))); ?></span>
                    <span class="badge <?php echo e(status_class($row['status'])); ?>"><?php echo e($row['status']); ?></span>
                </div>
            </div>

            <div class="receipt-section">
                <h3>Vehicle</h3>
                <table class="receipt-table">
                    <tr><th>Vehicle</th><td><?php echo e(trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? ''))); ?></td></tr>
                    <tr><th>Year</th><td><?php echo e($row['year'] ?? ''); ?></td></tr>
                    <tr><th>Details</th><td><?php echo e(($row['transmission'] ?? '') . ' / ' . ($row['fuel_type'] ?? '') . ' / ' . ($row['color'] ?? '')); ?></td></tr>
                    <?php if (!empty($row['engine'])): ?>
                    <tr><th>Engine</th><td><?php echo e($row['engine']); ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Price</th><td><strong><?php echo format_money($row['price'] ?? 0); ?></strong></td></tr>
                </table>
            </div>

            <div class="receipt-section">
                <h3>Buyer</h3>
                <table class="receipt-table">
                    <tr><th>Name</th><td><?php echo e($row['customer_name']); ?></td></tr>
                    <tr><th>Email</th><td><?php echo e($row['email']); ?></td></tr>
                    <tr><th>Contact</th><td><?php echo e($row['contact']); ?></td></tr>
                    <tr><th>Request note</th><td><?php echo nl2br(e($row['message'] ?? '')); ?></td></tr>
                </table>
            </div>

            <p class="receipt-note">This is an acknowledgment of your purchase request, not an official sales invoice. No online payment was processed. Our team will contact you to arrange inspection, documents, and payment manually. Please present reference <strong><?php echo e($ref); ?></strong> (Inquiry #<?php echo (int)$row['id']; ?>) when you visit or call.</p>

            <div class="receipt-sign">
                <div class="sign-box">
                    <div class="sign-space"></div>
                    <div class="sign-line"></div>
                    <strong><?php echo e($row['customer_name']); ?></strong>
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
            <a class="btn btn-outline" href="purchase-request.php?car_id=<?php echo (int)$row['car_id']; ?>">New Request</a>
        </div>
    </div>
</section>

<?php if ($auto_print): ?>
<script>window.addEventListener('load', function () { window.print(); });</script>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
