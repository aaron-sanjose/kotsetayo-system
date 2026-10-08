<?php
/**
 * KotseTayo - Admin Dashboard
 */
require_once __DIR__ . '/../includes/admin-header.php';

// Vehicle statistics
$stats['total_vehicles']  = (int)fetch_one("SELECT COUNT(*) AS c FROM cars")['c'];
$stats['available']       = (int)fetch_one("SELECT COUNT(*) AS c FROM cars WHERE status = 'Available'")['c'];
$stats['reserved']        = (int)fetch_one("SELECT COUNT(*) AS c FROM cars WHERE status = 'Reserved'")['c'];
$stats['sold']            = (int)fetch_one("SELECT COUNT(*) AS c FROM cars WHERE status = 'Sold'")['c'];

// Sales statistics
$stats['total_sales']     = (int)fetch_one("SELECT COUNT(*) AS c FROM sales")['c'];
$stats['sales_this_month']= (int)fetch_one("SELECT COUNT(*) AS c FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())")['c'];
$stats['revenue']         = (float)fetch_one("SELECT COALESCE(SUM(selling_price),0) AS s FROM sales")['s'];

// Inquiry statistics
$stats['inquiries_pending']   = (int)fetch_one("SELECT COUNT(*) AS c FROM inquiries WHERE status = 'Pending'")['c'];
$stats['inquiries_contacted'] = (int)fetch_one("SELECT COUNT(*) AS c FROM inquiries WHERE status = 'Contacted'")['c'];
$stats['inquiries_completed'] = (int)fetch_one("SELECT COUNT(*) AS c FROM inquiries WHERE status = 'Completed'")['c'];

// Recent inquiries
$recent_inquiries = fetch_all(
    "SELECT i.*, c.brand, c.model FROM inquiries i
     LEFT JOIN cars c ON c.id = i.car_id
     ORDER BY i.created_at DESC LIMIT 5"
);

// Recent sales
$recent_sales = fetch_all(
    "SELECT s.*, c.brand, c.model, cu.name AS buyer_name
     FROM sales s
     LEFT JOIN cars c ON c.id = s.car_id
     LEFT JOIN customers cu ON cu.id = s.customer_id
     ORDER BY s.sale_date DESC LIMIT 5"
);
?>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-label">Total Vehicles</div><div class="stat-value"><?php echo $stats['total_vehicles']; ?></div></div>
    <div class="stat-card stat-avail"><div class="stat-label">Available</div><div class="stat-value"><?php echo $stats['available']; ?></div></div>
    <div class="stat-card stat-resv"><div class="stat-label">Reserved</div><div class="stat-value"><?php echo $stats['reserved']; ?></div></div>
    <div class="stat-card stat-sold"><div class="stat-label">Sold</div><div class="stat-value"><?php echo $stats['sold']; ?></div></div>

    <div class="stat-card"><div class="stat-label">Total Sales</div><div class="stat-value"><?php echo $stats['total_sales']; ?></div></div>
    <div class="stat-card"><div class="stat-label">Sales This Month</div><div class="stat-value"><?php echo $stats['sales_this_month']; ?></div></div>
    <div class="stat-card stat-rev"><div class="stat-label">Total Revenue</div><div class="stat-value"><?php echo format_money($stats['revenue']); ?></div></div>

    <div class="stat-card stat-pend"><div class="stat-label">Pending Inquiries</div><div class="stat-value"><?php echo $stats['inquiries_pending']; ?></div></div>
    <div class="stat-card stat-cont"><div class="stat-label">Contacted</div><div class="stat-value"><?php echo $stats['inquiries_contacted']; ?></div></div>
    <div class="stat-card stat-compl"><div class="stat-label">Completed</div><div class="stat-value"><?php echo $stats['inquiries_completed']; ?></div></div>
</div>

<div class="admin-cols">
    <div class="panel">
        <div class="panel-head"><h2>Recent Inquiries</h2><a class="btn btn-outline btn-sm" href="inquiries.php">View All</a></div>
        <?php if (empty($recent_inquiries_error ?? [])): ?>
        <table class="table">
            <thead><tr><th>Customer</th><th>Vehicle</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($recent_inquiries)): ?>
                <tr><td colspan="4" class="text-muted">No inquiries yet.</td></tr>
            <?php else: foreach ($recent_inquiries as $ri): ?>
                <tr>
                    <td><?php echo e($ri['customer_name']); ?></td>
                    <td><?php echo e($ri['brand'] . ' ' . $ri['model']); ?></td>
                    <td><span class="badge <?php echo e(status_class($ri['status'])); ?>"><?php echo e($ri['status']); ?></span></td>
                    <td><?php echo e(date('M j, Y', strtotime($ri['created_at']))); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Recent Sales</h2><a class="btn btn-outline btn-sm" href="sales.php">View All</a></div>
        <table class="table">
            <thead><tr><th>Vehicle</th><th>Buyer</th><th>Price</th><th>Date</th></tr></thead>
            <tbody>
                <?php if (empty($recent_sales)): ?>
                    <tr><td colspan="4" class="empty-muted">No sales recorded yet.</td></tr>
                <?php else: foreach ($recent_sales as $rs): ?>
                    <tr>
                        <td><?php echo e($rs['brand'] . ' ' . $rs['model']); ?></td>
                        <td><?php echo e($rs['buyer_name']); ?></td>
                        <td><?php echo format_money($rs['selling_price']); ?></td>
                        <td><?php echo e(date('M j, Y', strtotime($rs['sale_date']))); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>