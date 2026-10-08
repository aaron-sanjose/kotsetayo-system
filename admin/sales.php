<?php
/**
 * CarBuy - Sales Management
 */
require_once __DIR__ . '/../includes/admin-header.php';

$errors = [];

// Record a sale.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'record_sale') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $car_id   = (int)($_POST['car_id'] ?? 0);
        $buyer_name  = clean($_POST['buyer_name'] ?? '');
        $buyer_contact = clean($_POST['buyer_contact'] ?? '');
        $buyer_email  = clean($_POST['buyer_email'] ?? '');
        $selling_price = clean($_POST['selling_price'] ?? '');
        $sale_date   = clean($_POST['sale_date'] ?? '');
        $payment_status = clean($_POST['payment_status'] ?? 'Pending');
        $notes       = clean($_POST['notes'] ?? '');

        if ($car_id === 0) $errors[] = 'Please select a vehicle.';
        if ($buyer_name === '') $errors[] = 'Buyer name is required.';
        if ($buyer_contact === '') $errors[] = 'Buyer contact is required.';
        if (!is_numeric($selling_price) || (float)$selling_price < 0) $errors[] = 'Enter a valid selling price.';
        if ($sale_date === '' || !strtotime($sale_date)) $errors[] = 'Enter a valid sale date.';
        if (!in_array($payment_status, ['Pending', 'Paid', 'Partial'], true)) $payment_status = 'Pending';

        if (empty($errors)) {
            // Upsert customer (match by email if given, else contact).
            query("INSERT INTO customers (name, email, contact, address) VALUES (?, ?, ?, '')
                   ON DUPLICATE KEY UPDATE name = VALUES(name)",
                  [$buyer_name, $buyer_email !== '' ? $buyer_email : $buyer_contact . '@customer.local', $buyer_contact]);
            $customer = fetch_one("SELECT * FROM customers WHERE contact = ? ORDER BY id DESC LIMIT 1", [$buyer_contact]);
            $customer_id = (int)($customer['id'] ?? 0);

            // Record the sale.
            query("INSERT INTO sales (car_id, customer_id, selling_price, sale_date, payment_status, notes)
                   VALUES (?, ?, ?, ?, ?, ?)",
                  [$car_id, $customer_id, (float)$selling_price, $sale_date, $payment_status, $notes]);

            // Automatically mark the vehicle as sold.
            query("UPDATE cars SET status = 'Sold' WHERE id = ?", [$car_id]);

            set_flash('success', 'Sale recorded successfully. Vehicle marked as Sold.');
            header('Location: sales.php');
            exit;
        }
    }
}

$sales = fetch_all(
    "SELECT s.*, c.brand, c.model, cu.name AS buyer_name, cu.contact AS buyer_contact
     FROM sales s
     LEFT JOIN cars c ON c.id = s.car_id
     LEFT JOIN customers cu ON cu.id = s.customer_id
     ORDER BY s.sale_date DESC, s.id DESC"
);

$available_cars = fetch_all(
    "SELECT * FROM cars WHERE status IN ('Available', 'Reserved') ORDER BY brand"
);

$token = csrf_token();
?>

<div class="panel">
    <div class="panel-head"><h2>Record a Sale</h2></div>
<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="sales.php" class="form admin-form" id="saleForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
        <input type="hidden" name="form_action" value="record_sale">
        <div class="form-row">
            <div class="form-group">
                <label for="car_id">Select Vehicle *</label>
                <select name="car_id" id="car_id" required>
                    <option value="">-- Select vehicle --</option>
                    <?php foreach ($available_cars as $ac): ?>
                    <option value="<?php echo (int)$ac['id']; ?>" data-price="<?php echo e($ac['price']); ?>">
                        <?php echo e($ac['brand'] . ' ' . $ac['model'] . ' (' . $ac['year'] . ') - ' . $ac['status']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="selling_price">Selling Price (₱) *</label>
                <input type="number" id="selling_price" name="selling_price" min="0" step="0.01" placeholder="e.g. 1000000" required>
            </div>
            <div class="form-group">
                <label for="sale_date">Sale Date *</label>
                <input type="date" id="sale_date" name="sale_date" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="buyer_name">Buyer Name *</label>
                <input type="text" id="buyer_name" name="buyer_name" required>
            </div>
            <div class="form-group">
                <label for="buyer_contact">Buyer Contact *</label>
                <input type="text" id="buyer_contact" name="buyer_contact" required>
            </div>
            <div class="form-group">
                <label for="buyer_email">Buyer Email</label>
                <input type="email" id="buyer_email" name="buyer_email">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status">
                    <option value="Pending">Pending</option>
                    <option value="Partial">Partial</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Record Sale</button>
        </div>
        <p class="form-note">Recording a sale automatically changes the vehicle status to "Sold" and removes it from available listings.</p>
    </form>
</div>
<div class="panel">
    <div class="panel-head"><h2>Sales History (<?php echo count($sales); ?>)</h2></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Vehicle</th><th>Buyer</th><th>Selling Price</th>
                    <th>Sale Date</th><th>Payment</th><th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="6" class="text-muted">No sales recorded yet.</td></tr>
                <?php else: foreach ($sales as $sale): ?>
                <tr>
                    <td><strong><?php echo e($sale['brand'] . ' ' . $sale['model']); ?></strong></td>
                    <td><?php echo e($sale['buyer_name']); ?><br><span class="text-muted"><?php echo e($sale['buyer_contact']); ?></span></td>
                    <td><?php echo format_money($sale['selling_price']); ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($sale['sale_date']))); ?></td>
                    <td><span class="badge <?php echo e(status_class($sale['payment_status'])); ?>"><?php echo e($sale['payment_status']); ?></span></td>
                    <td class="cell-message"><?php echo e(mb_substr($sale['notes'] ?? '', 0, 40)); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>