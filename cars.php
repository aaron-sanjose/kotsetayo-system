<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$q   = clean($_GET['q'] ?? '');
$brand = clean($_GET['brand'] ?? '');
$minP = clean($_GET['min_price'] ?? '');
$maxP = clean($_GET['max_price'] ?? '');
$year = clean($_GET['year'] ?? '');
$trans = clean($_GET['transmission'] ?? '');
$fuel = clean($_GET['fuel_type'] ?? '');
$status = clean($_GET['status'] ?? '');
$sort = clean($_GET['sort'] ?? 'newest');

$where = [];
$params = [];

// The public marketplace shows available vehicles by default, unless a
// specific status filter is chosen.
if ($status === '') {
    $where[] = "c.status = 'Available'";
} else {
    $where[] = "c.status = ?";
    $params[] = $status;
}

if ($q !== '') {
    $where[] = "(c.brand LIKE ? OR c.model LIKE ? OR c.description LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($brand !== '') {
    $where[] = "c.brand = ?";
    $params[] = $brand;
}
if ($minP !== '') {
    $where[] = "c.price >= ?";
    $params[] = (float)$minP;
}
if ($maxP !== '') {
    $where[] = "c.price <= ?";
    $params[] = (float)$maxP;
}
if ($year !== '') {
    $where[] = "c.year = ?";
    $params[] = (int)$year;
}
if ($trans !== '') {
    $where[] = "c.transmission = ?";
    $params[] = $trans;
}
if ($fuel !== '') {
    $where[] = "c.fuel_type = ?";
    $params[] = $fuel;
}

switch ($sort) {
    case 'price_asc':  $orderBy = 'c.price ASC';  break;
    case 'price_desc': $orderBy = 'c.price DESC'; break;
    case 'oldest':     $orderBy = 'c.year ASC, c.id ASC'; break;
    case 'newest':
    default:           $orderBy = 'c.year DESC, c.id DESC'; break;
}

$sql = "SELECT c.*, (SELECT ci.image_path FROM car_images ci WHERE ci.car_id = c.id ORDER BY ci.id LIMIT 1) AS image_path
        FROM cars c WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";
$cars = fetch_all($sql, $params);
$brands = fetch_all("SELECT DISTINCT brand FROM cars WHERE status = 'Available' ORDER BY brand");

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner page-banner-image">
    <div class="container">
        <h1>Browse Our Cars</h1>
        <p>Search, filter, and find the vehicle that's right for you.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filter-panel" method="get" action="cars.php" id="carFilters">
<div class="filter-row">
                <div class="filter-group">
                    <label for="q">Keyword</label>
                    <input type="text" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Brand, model, keyword...">
                </div>
                <div class="filter-group">
                    <label for="brand">Brand</label>
                    <select name="brand" id="brand">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?php echo e($b['brand']); ?>" <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>><?php echo e($b['brand']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="transmission">Transmission</label>
                    <select name="transmission" id="transmission">
                        <option value="">Any</option>
                        <option value="Automatic" <?php echo $trans === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                        <option value="Manual" <?php echo $trans === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select name="fuel_type" id="fuel_type">
                        <option value="">Any</option>
                        <option value="Gasoline" <?php echo $fuel === 'Gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                        <option value="Diesel" <?php echo $fuel === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="year">Year</label>
                    <input type="number" id="year" name="year" min="1990" max="<?php echo date('Y') + 1; ?>" value="<?php echo e($year); ?>" placeholder="e.g. 2020">
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" id="min_price" name="min_price" min="0" step="1000" value="<?php echo e($minP); ?>" placeholder="0">
                </div>
                <div class="filter-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" id="max_price" name="max_price" min="0" step="1000" value="<?php echo e($maxP); ?>" placeholder="5000000">
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="">Available Only</option>
                        <option value="Available" <?php echo $status === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Reserved" <?php echo $status === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                        <option value="Sold" <?php echo $status === 'Sold' ? 'selected' : ''; ?>>Sold</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort">Sort By</label>
                    <select name="sort" id="sort">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="cars.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>

        <div class="results-meta">
            <span><?php echo count($cars); ?> vehicle<?php echo count($cars) === 1 ? '' : 's'; ?> found</span>
        </div>
        <?php if (empty($cars)): ?>
            <p class="empty-state">No vehicles match your search criteria. Try adjusting your filters.</p>
        <?php else: ?>
        <div class="car-grid">
            <?php foreach ($cars as $car): ?>
            <article class="car-card">
                <div class="car-card-img">
                    <img src="<?php echo e(car_image($car)); ?>" alt="<?php echo e($car['brand'] . ' ' . $car['model']); ?>" loading="lazy">
                    <span class="car-card-status <?php echo e(status_class($car['status'])); ?>"><?php echo e($car['status']); ?></span>
                </div>
                <div class="car-card-body">
                    <h3><?php echo e($car['brand']); ?> <?php echo e($car['model']); ?></h3>
                    <div class="car-card-specs">
                        <span><?php echo e($car['year']); ?></span>
                        <span><?php echo number_format($car['mileage']); ?> km</span>
                        <span><?php echo e($car['transmission']); ?></span>
                        <span><?php echo e($car['fuel_type']); ?></span>
                    </div>
                    <div class="car-card-price"><?php echo format_money($car['price']); ?></div>
                    <a href="car-details.php?id=<?php echo (int)$car['id']; ?>" class="btn btn-primary btn-block">View Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
