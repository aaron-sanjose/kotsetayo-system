<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$car = fetch_one(
    "SELECT * FROM cars WHERE id = ?",
    [$id]
);

if (!$car) {
    include __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><h2>Vehicle Not Found</h2><p>The vehicle you are looking for does not exist or may have been removed.</p><a class="btn btn-primary" href="cars.php">Browse Cars</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$images = fetch_all("SELECT image_path FROM car_images WHERE car_id = ? ORDER BY id", [$id]);
if (empty($images)) {
    $images = [['image_path' => 'assets/images/car-placeholder.svg']];
}

// Related vehicles: same brand or same fuel type, not sold, excluding current.
$related = fetch_all(
    "SELECT c.*, (SELECT ci.image_path FROM car_images ci WHERE ci.car_id = c.id ORDER BY ci.id LIMIT 1) AS image_path
     FROM cars c
     WHERE c.id != ? AND c.status != 'Sold'
     ORDER BY (c.brand = ?) DESC, c.created_at DESC
     LIMIT 3",
    [$id, $car['brand']]
);

include __DIR__ . '/includes/header.php';
?>

<section class="section container">
    <nav class="breadcrumb"><a href="index.php">Home</a> &rsaquo; <a href="cars.php">Cars</a> &rsaquo; <?php echo e($car['brand'] . ' ' . $car['model']); ?></nav>

    <div class="details-grid">
        <div class="details-gallery">
            <?php if (count($images) > 1): ?>
            <div class="gallery-main">
                <img id="mainImage" src="<?php echo e(car_image(['image_path' => $images[0]['image_path']])); ?>" alt="<?php echo e($car['brand'] . ' ' . $car['model']); ?>">
            </div>
            <div class="gallery-thumbs">
                <?php foreach ($images as $i => $img): ?>
                <button type="button" class="gallery-thumb <?php echo $i === 0 ? 'active' : ''; ?>" data-src="<?php echo e(car_image(['image_path' => $img['image_path']])); ?>">
                    <img src="<?php echo e(car_image(['image_path' => $img['image_path']])); ?>" alt="Thumbnail <?php echo $i + 1; ?>">
                </button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="gallery-main">
                <img src="<?php echo e(car_image(['image_path' => $images[0]['image_path']])); ?>" alt="<?php echo e($car['brand'] . ' ' . $car['model']); ?>">
            </div>
            <?php endif; ?>
        </div>

        <div class="details-info">
            <div class="details-head">
                <h1><?php echo e($car['brand']); ?> <?php echo e($car['model']); ?></h1>
                <span class="badge <?php echo e(status_class($car['status'])); ?>"><?php echo e($car['status']); ?></span>
            </div>
            <div class="details-price"><?php echo format_money($car['price']); ?></div>
            <ul class="details-specs">
                <li><span>Year</span><strong><?php echo e($car['year']); ?></strong></li>
                <li><span>Mileage</span><strong><?php echo number_format($car['mileage']); ?> km</strong></li>
                <li><span>Transmission</span><strong><?php echo e($car['transmission']); ?></strong></li>
                <li><span>Fuel Type</span><strong><?php echo e($car['fuel_type']); ?></strong></li>
                <li><span>Color</span><strong><?php echo e($car['color']); ?></strong></li>
                <li><span>Engine</span><strong><?php echo e($car['engine']); ?></strong></li>
            </ul>
            <div class="details-actions">
                <a href="inquiry.php?car_id=<?php echo (int)$car['id']; ?>" class="btn btn-primary btn-lg">Send Inquiry</a>
                <a href="purchase-request.php?car_id=<?php echo (int)$car['id']; ?>" class="btn btn-outline btn-lg">Request to Buy</a>
            </div>
        </div>
    </div>

    <div class="details-description">
        <h2>Vehicle Description</h2>
        <p><?php echo nl2br(e($car['description'])); ?></p>
    </div>
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2>You May Also Like</h2>
        <div class="car-grid">
            <?php foreach ($related as $rel): ?>
            <article class="car-card">
                <div class="car-card-img">
                    <img src="<?php echo e(car_image($rel)); ?>" alt="<?php echo e($rel['brand'] . ' ' . $rel['model']); ?>" loading="lazy">
                    <span class="car-card-status <?php echo e(status_class($rel['status'])); ?>"><?php echo e($rel['status']); ?></span>
                </div>
                <div class="car-card-body">
                    <h3><?php echo e($rel['brand']); ?> <?php echo e($rel['model']); ?></h3>
                    <div class="car-card-specs">
                        <span><?php echo e($rel['year']); ?></span>
                        <span><?php echo number_format($rel['mileage']); ?> km</span>
                        <span><?php echo e($rel['transmission']); ?></span>
                    </div>
                    <div class="car-card-price"><?php echo format_money($rel['price']); ?></div>
                    <a href="car-details.php?id=<?php echo (int)$rel['id']; ?>" class="btn btn-primary btn-block">View Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>