<?php
/**
 * CarBuy - Customer / Buyer Management
 */
require_once __DIR__ . '/../includes/admin-header.php';

$customers = fetch_all(
    "SELECT cu.*,
            (SELECT COUNT(*) FROM inquiries i WHERE i.email = cu.email) AS inquiry_count,
            (SELECT COUNT(*) FROM sales s WHERE s.customer_id = cu.id) AS purchase_count
     FROM customers cu
     ORDER BY cu.created_at DESC"
);

// Optional single-customer detail view.
$view_id = (int)($_GET['view'] ?? 0);
$view_customer = null;
if ($view_id > 0) {
    $view_customer = fetch_one("SELECT * FROM customers WHERE id = ?", [$view_id]);
}

if ($view_customer) {
    $view_inquiries = fetch_all(
        "SELECT i.*, c.brand, c.model FROM inquiries i
         LEFT JOIN cars c ON c.id = i.car_id
         WHERE i.email = ? ORDER BY i.created_at DESC",
        [$view_customer['email']]
    );
    $view_sales = fetch_all(
        "SELECT s.*, c.brand, c.model FROM sales s
         LEFT JOIN cars c ON c.id = s.car_id
         WHERE s.customer_id = ? ORDER BY s.sale_date DESC",
        [$view_id]
    );
}
?>

<div class="panel">
    <div class="panel-head">
        <h2>Customers / Buyers (<?php echo count($customers); ?>)</h2>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Contact</th><th>Address</th>
                    <th>Inquiries</th><th>Purchases</th><th>Date Added</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="8" class="text-muted">No customers yet.</td></tr>
                <?php else: foreach ($customers as $cust): ?>
                <tr>
                    <td><strong><?php echo e($cust['name']); ?></strong></td>
                    <td><?php echo e($cust['email']); ?></td>
                    <td><?php echo e($cust['contact']); ?></td>
                    <td><?php echo e($cust['address']); ?></td>
                    <td><?php echo (int)$cust['inquiry_count']; ?></td>
                    <td><?php echo (int)$cust['purchase_count']; ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($cust['created_at']))); ?></td>
                    <td><a class="btn btn-outline btn-sm" href="customers.php?view=<?php echo (int)$cust['id']; ?>">View</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($view_customer): ?>
<div class="panel">
    <div class="panel-head">
        <h2>Customer Details: <?php echo e($view_customer['name']); ?></h2>
        <a class="btn btn-outline btn-sm" href="customers.php">&larr; Back</a>
    </div>
    <div class="customer-detail-info">
        <p><strong>Email:</strong> <?php echo e($view_customer['email']); ?></p>
        <p><strong>Contact:</strong> <?php echo e($view_customer['contact']); ?></p>
        <p><strong>Address:</strong> <?php echo e($view_customer['address']); ?></p>
    </div>

    <h3 style="margin-top:1.5rem">Inquiries</h3>
    <table class="table">
        <thead><tr><th>Vehicle</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
            <?php if (empty($view_inquiries)): ?>
                <tr><td colspan="4" class="text-muted">No inquiries.</td></tr>
            <?php else: foreach ($view_inquiries as $vi): ?>
                <tr>
                    <td><?php echo e($vi['brand'] . ' ' . $vi['model']); ?></td>
                    <td class="cell-message"><?php echo e(mb_substr($vi['message'] ?? '', 0, 60)); ?></td>
                    <td><span class="badge <?php echo e(status_class($vi['status'])); ?>"><?php echo e($vi['status']); ?></span></td>
                    <td><?php echo e(date('M j, Y', strtotime($vi['created_at']))); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem">Purchases / Sales</h3>
    <table class="table">
        <thead><tr><th>Vehicle</th><th>Price</th><th>Date</th><th>Payment</th></tr></thead>
        <tbody>
            <?php if (empty($view_sales)): ?>
                <tr><td colspan="4" class="text-muted">No recorded purchases.</td></tr>
            <?php else: foreach ($view_sales as $vs): ?>
                <tr>
                    <td><?php echo e($vs['brand'] . ' ' . $vs['model']); ?></td>
                    <td><?php echo format_money($vs['selling_price']); ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($vs['sale_date']))); ?></td>
                    <td><span class="badge <?php echo e(status_class($vs['payment_status'])); ?>"><?php echo e($vs['payment_status']); ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>