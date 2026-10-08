<?php
/**
 * CarBuy - Sales Reports
 */
require_once __DIR__ . '/../includes/admin-header.php';

// Filters
$date_from = clean($_GET['date_from'] ?? '');
$date_to   = clean($_GET['date_to'] ?? '');
$car_filter = clean($_GET['vehicle'] ?? '');
$buyer_filter = clean($_GET['buyer'] ?? '');

$where = [];
$params = [];
if ($date_from !== '') { $where[] = "s.sale_date >= ?"; $params[] = $date_from; }
if ($date_to !== '')   { $where[] = "s.sale_date <= ?"; $params[] = $date_to; }
if ($car_filter !== '') { $where[] = "s.car_id = ?"; $params[] = (int)$car_filter; }
if ($buyer_filter !== '') { $where[] = "cu.name LIKE ?"; $params[] = '%' . $buyer_filter . '%'; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// Summary statistics (respecting filters).
$summary = fetch_one(
    "SELECT
        COUNT(s.id) AS total_sold,
        COALESCE(SUM(s.selling_price), 0) AS total_revenue,
        COALESCE(AVG(s.selling_price), 0) AS avg_price,
        SUM(CASE WHEN s.sale_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS sales_this_month,
        SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS sales_this_year,
        COALESCE(SUM(CASE WHEN s.sale_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN s.selling_price ELSE 0 END), 0) AS revenue_this_month,
        COALESCE(SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) THEN s.selling_price ELSE 0 END), 0) AS revenue_this_year
      FROM sales s
      LEFT JOIN customers cu ON cu.id = s.customer_id$whereSql",
    $params
);

$sales = fetch_all(
    "SELECT s.*, c.brand, c.model, cu.name AS buyer_name, cu.contact AS buyer_contact
     FROM sales s
     LEFT JOIN cars c ON c.id = s.car_id
     LEFT JOIN customers cu ON cu.id = s.customer_id$whereSql
     ORDER BY s.sale_date DESC, s.id DESC",
    $params
);

$all_cars = fetch_all("SELECT id, brand, model, year FROM cars ORDER BY brand");

// For the CSS-only bar chart.
$max_rev = (float)$summary['total_revenue'];
$month_rev = (float)$summary['revenue_this_month'];
$year_rev = (float)$summary['revenue_this_year'];
$chart_max = max(1, $max_rev, $month_rev, $year_rev);
?>

<div class="report-summary">
    <div class="stat-card"><div class="stat-label">Total Vehicles Sold</div><div class="stat-value"><?php echo (int)$summary['total_sold']; ?></div></div>
    <div class="stat-card stat-rev"><div class="stat-label">Total Revenue</div><div class="stat-value"><?php echo format_money($summary['total_revenue']); ?></div></div>
    <div class="stat-card"><div class="stat-label">Sales This Month</div><div class="stat-value"><?php echo (int)$summary['sales_this_month']; ?></div></div>
    <div class="stat-card"><div class="stat-label">Revenue This Month</div><div class="stat-value"><?php echo format_money($summary['revenue_this_month']); ?></div></div>
    <div class="stat-card"><div class="stat-label">Sales This Year</div><div class="stat-value"><?php echo (int)$summary['sales_this_year']; ?></div></div>
    <div class="stat-card"><div class="stat-label">Avg Selling Price</div><div class="stat-value"><?php echo format_money($summary['avg_price']); ?></div></div>
</div>
<div class="panel">
    <div class="panel-head"><h2>Revenue Overview</h2></div>
    <div class="css-chart">
        <div class="chart-bar">
            <span class="chart-label">Total</span>
            <div class="chart-track"><div class="chart-fill" style="width:<?php echo round(($max_rev / $chart_max) * 100); ?>%"></div></div>
            <span class="chart-value"><?php echo format_money($max_rev); ?></span>
        </div>
        <div class="chart-bar">
            <span class="chart-label">This Month</span>
            <div class="chart-track"><div class="chart-fill" style="width:<?php echo round(($month_rev / $chart_max) * 100); ?>%"></div></div>
            <span class="chart-value"><?php echo format_money($month_rev); ?></span>
        </div>
        <div class="chart-bar">
            <span class="chart-label">This Year</span>
            <div class="chart-track"><div class="chart-fill" style="width:<?php echo round(($year_rev / $chart_max) * 100); ?>%"></div></div>
            <span class="chart-value"><?php echo format_money($year_rev); ?></span>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Sales Report</h2></div>

    <form method="get" action="reports.php" class="filter-panel filter-panel-admin">
        <div class="filter-row">
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo e($date_from); ?>">
            </div>
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo e($date_to); ?>">
            </div>
            <div class="filter-group">
                <label for="vehicle">Vehicle</label>
                <select name="vehicle" id="vehicle">
                    <option value="">All Vehicles</option>
                    <?php foreach ($all_cars as $ac): ?>
                    <option value="<?php echo (int)$ac['id']; ?>" <?php echo $car_filter == $ac['id'] ? 'selected' : ''; ?>>
                        <?php echo e($ac['brand'] . ' ' . $ac['model'] . ' (' . $ac['year'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="buyer">Buyer</label>
                <input type="text" id="buyer" name="buyer" value="<?php echo e($buyer_filter); ?>" placeholder="Buyer name">
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="reports.php" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Vehicle</th><th>Buyer</th><th>Selling Price</th><th>Sale Date</th><th>Payment</th></tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="5" class="text-muted">No sales match the selected filters.</td></tr>
                <?php else: foreach ($sales as $sale): ?>
                <tr>
                    <td><strong><?php echo e($sale['brand'] . ' ' . $sale['model']); ?></strong></td>
                    <td><?php echo e($sale['buyer_name']); ?></td>
                    <td><?php echo format_money($sale['selling_price']); ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($sale['sale_date']))); ?></td>
                    <td><span class="badge <?php echo e(status_class($sale['payment_status'])); ?>"><?php echo e($sale['payment_status']); ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>