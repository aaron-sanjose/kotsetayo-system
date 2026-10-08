<?php
/**
 * KotseTayo - Sales Reports
 */
require_once __DIR__ . '/../includes/admin-header.php';

// Filters — validate dates strictly so bad input never silently breaks the report.
$raw_from = clean($_GET['date_from'] ?? '');
$raw_to   = clean($_GET['date_to'] ?? '');
$date_from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_from) ? $raw_from : '';
$date_to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_to) ? $raw_to : '';
// Auto-swap a reversed range instead of showing an empty report.
if ($date_from !== '' && $date_to !== '' && $date_from > $date_to) {
    [$date_from, $date_to] = [$date_to, $date_from];
}
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

// Total selling price of the filtered result set (shown in the table footer).
$filtered_total = 0.0;
foreach ($sales as $sale) {
    $filtered_total += (float)$sale['selling_price'];
}

// Quick date-range presets (keep vehicle/buyer filters when clicked).
$today = date('Y-m-d');
$presets = [
    'This Month'   => ['from' => date('Y-m-01'), 'to' => $today],
    'Last 30 Days' => ['from' => date('Y-m-d', strtotime('-29 days')), 'to' => $today],
    'This Year'    => ['from' => date('Y-01-01'), 'to' => $today],
];
$active_preset = '';
foreach ($presets as $label => $range) {
    if ($date_from === $range['from'] && $date_to === $range['to']) { $active_preset = $label; break; }
}
$preset_qs = array_filter([
    'vehicle' => $car_filter !== '' ? (int)$car_filter : null,
    'buyer'   => $buyer_filter !== '' ? $buyer_filter : null,
]);

// Human-readable summary of the active filters.
$has_filters = ($date_from !== '' || $date_to !== '' || $car_filter !== '' || $buyer_filter !== '');
$active_filters = [];
if ($date_from !== '' || $date_to !== '') {
    $active_filters[] = ($date_from !== '' ? date('M j, Y', strtotime($date_from)) : 'Start')
        . ' – ' . ($date_to !== '' ? date('M j, Y', strtotime($date_to)) : 'Today');
}
if ($car_filter !== '') {
    foreach ($all_cars as $ac) {
        if ((int)$ac['id'] === (int)$car_filter) {
            $active_filters[] = $ac['brand'] . ' ' . $ac['model'] . ' (' . $ac['year'] . ')';
            break;
        }
    }
}
if ($buyer_filter !== '') {
    $active_filters[] = 'Buyer: "' . $buyer_filter . '"';
}

// Totals used by the chart legend chips.
$max_rev = (float)$summary['total_revenue'];
$month_rev = (float)$summary['revenue_this_month'];
$year_rev = (float)$summary['revenue_this_year'];

// Revenue by month for the last 12 months (for the overview chart).
$monthly_rows = fetch_all(
    "SELECT DATE_FORMAT(s.sale_date, '%Y-%m') AS ym, COALESCE(SUM(s.selling_price), 0) AS rev
     FROM sales s
     WHERE s.sale_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
     GROUP BY ym"
);
$monthly_rev = [];
foreach ($monthly_rows as $row) {
    $monthly_rev[$row['ym']] = (float)$row['rev'];
}

// Build a complete 12-month timeline (zero-filled so gaps show as empty bars).
$chart_months = [];
for ($i = 11; $i >= 0; $i--) {
    $ts = strtotime("first day of -$i month");
    $ym = date('Y-m', $ts);
    $chart_months[] = [
        'label'   => date('M', $ts),
        'full'    => date('F Y', $ts),
        'rev'     => $monthly_rev[$ym] ?? 0.0,
        'current' => ($i === 0),
    ];
}

// Arrays passed to the Chart.js canvas below.
$chart_labels = [];
$chart_full   = [];
$chart_data   = [];
foreach ($chart_months as $m) {
    $chart_labels[] = $m['label'];
    $chart_full[]   = $m['full'];
    $chart_data[]   = $m['rev'];
}
?>

<div class="stat-grid report-stat-grid">
    <div class="stat-card dashboard-stat stat-sales"><div><div class="stat-label">Total Vehicles Sold</div><div class="stat-value"><?php echo (int)$summary['total_sold']; ?></div></div></div>
    <div class="stat-card dashboard-stat stat-rev"><div><div class="stat-label">Total Revenue</div><div class="stat-value"><?php echo format_money($summary['total_revenue']); ?></div></div></div>
    <div class="stat-card dashboard-stat stat-sales"><div><div class="stat-label">Sales This Month</div><div class="stat-value"><?php echo (int)$summary['sales_this_month']; ?></div></div></div>
    <div class="stat-card dashboard-stat stat-rev"><div><div class="stat-label">Revenue This Month</div><div class="stat-value"><?php echo format_money($summary['revenue_this_month']); ?></div></div></div>
    <div class="stat-card dashboard-stat stat-sales"><div><div class="stat-label">Sales This Year</div><div class="stat-value"><?php echo (int)$summary['sales_this_year']; ?></div></div></div>
    <div class="stat-card dashboard-stat stat-rev"><div><div class="stat-label">Avg Selling Price</div><div class="stat-value"><?php echo format_money($summary['avg_price']); ?></div></div></div>
</div>
<div class="panel">
    <div class="panel-head">
        <h2>Revenue Overview</h2>
        <span class="panel-sub">Monthly sales revenue, last 12 months</span>
    </div>
    <div class="rev-legend">
        <span>Total Revenue <strong><?php echo format_money($max_rev); ?></strong></span>
        <span>This Month <strong><?php echo format_money($month_rev); ?></strong></span>
        <span>This Year <strong><?php echo format_money($year_rev); ?></strong></span>
    </div>
    <div class="rev-chart-box">
        <canvas id="revenueChart" role="img" aria-label="Bar chart of monthly sales revenue for the last 12 months"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        var box = document.querySelector('.rev-chart-box');
        if (!window.Chart) {
            box.innerHTML = '<p class="text-muted">Chart library could not be loaded. Please check your internet connection and refresh.</p>';
            return;
        }

        var labels = <?php echo json_encode($chart_labels); ?>;
        var data   = <?php echo json_encode(array_map('floatval', $chart_data)); ?>;
        var full   = <?php echo json_encode($chart_full); ?>;
        var current = data.length - 1; // last column = current month

        function peso(v) {
            return '\u20B1' + Number(v).toLocaleString('en-PH', { maximumFractionDigits: 0 });
        }
        function compact(v) {
            if (v >= 1e9) return '\u20B1' + (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
            if (v >= 1e6) return '\u20B1' + (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
            if (v >= 1e3) return '\u20B1' + Math.round(v / 1e3) + 'K';
            return peso(v);
        }

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    backgroundColor: data.map(function (_, i) {
                        return i === current ? '#ffa31a' : 'rgba(255, 163, 26, .45)';
                    }),
                    hoverBackgroundColor: '#da8b16',
                    borderRadius: 7,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111',
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            title: function (items) { return full[items[0].dataIndex]; },
                            label: function (item) { return 'Revenue: ' + peso(item.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.06)' },
                        border: { display: false },
                        ticks: { color: '#8a8a8a', font: { size: 11 }, callback: function (v) { return compact(v); } }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#6b6b6b', font: { size: 12, weight: '600' } }
                    }
                }
            }
        });
    })();
    </script>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Sales Report</h2>
        <span class="panel-sub">Filter sales by date range, vehicle, or buyer</span>
    </div>

    <div class="rf-presets">
        <span class="rf-presets-label">Quick range:</span>
        <?php foreach ($presets as $label => $range): ?>
        <a class="rf-chip<?php echo $active_preset === $label ? ' rf-chip-active' : ''; ?>"
           href="reports.php?<?php echo e(http_build_query(array_merge($preset_qs, [
               'date_from' => $range['from'], 'date_to' => $range['to'],
           ]))); ?>"><?php echo e($label); ?></a>
        <?php endforeach; ?>
        <a class="rf-chip" href="reports.php">All Time</a>
    </div>

    <form method="get" action="reports.php" class="report-filters">
        <div class="rf-field">
            <label for="date_from">Date From</label>
            <input type="date" id="date_from" name="date_from" value="<?php echo e($date_from); ?>" max="<?php echo e($today); ?>">
        </div>
        <div class="rf-field">
            <label for="date_to">Date To</label>
            <input type="date" id="date_to" name="date_to" value="<?php echo e($date_to); ?>" max="<?php echo e($today); ?>">
        </div>
        <div class="rf-field">
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
        <div class="rf-field">
            <label for="buyer">Buyer</label>
            <input type="text" id="buyer" name="buyer" value="<?php echo e($buyer_filter); ?>" placeholder="Buyer name">
        </div>
        <div class="rf-actions">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="reports.php" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="rf-meta">
        <span>Showing <strong><?php echo count($sales); ?></strong> sale<?php echo count($sales) === 1 ? '' : 's'; ?></span>
        <?php if ($has_filters): ?>
            <span>matching:</span>
            <?php foreach ($active_filters as $af): ?>
                <span class="rf-tag"><?php echo e($af); ?></span>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="text-muted">— all recorded sales</span>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table report-table">
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
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>