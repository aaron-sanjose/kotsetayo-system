<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch 6 featured available vehicles with their first image.
$featured = fetch_all(
    "SELECT c.*, (SELECT ci.image_path FROM car_images ci WHERE ci.car_id = c.id ORDER BY ci.id LIMIT 1) AS image_path
     FROM cars c
     WHERE c.status = 'Available'
     ORDER BY c.created_at DESC
     LIMIT 6"
);

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <h1>Find Your Next Car</h1>
            <p>Browse quality vehicles and find the right car for your budget and lifestyle.</p>
            <div class="hero-actions">
                <a href="cars.php" class="btn btn-primary btn-lg">Browse Cars</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">Sell Your Car / Contact Us</a>
            </div>
        </div>
        <div class="hero-media">
            <img src="assets/images/hero-car.svg" alt="Featured car" class="hero-img">
        </div>
    </div>
</section>

<!-- Featured Cars -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Featured Cars</h2>
            <p>Hand-picked vehicles ready for your next adventure.</p>
        </div>

        <?php if (empty($featured)): ?>
            <p class="empty-state">No available vehicles right now. Please check back soon.</p>
        <?php else: ?>
        <div class="car-grid">
            <?php foreach ($featured as $car): ?>
            <article class="car-card">
                <div class="car-card-img">
                    <img src="<?php echo e(car_image($car)); ?>" alt="<?php echo e($car['brand'] . ' ' . $car['model']); ?>" loading="lazy">
                    <span class="car-card-status status-available"><?php echo e($car['status']); ?></span>
                </div>
                <div class="car-card-body">
                    <h3><?php echo e($car['brand']); ?> <?php echo e($car['model']); ?></h3>
                    <div class="car-card-specs">
                        <span><?php echo e($car['year']); ?></span>
                        <span><?php echo number_format($car['mileage']); ?> km</span>
                        <span><?php echo e($car['transmission']); ?></span>
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

<!-- Why Choose CarBuy -->
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Why Choose CarBuy</h2>
            <p>We make buying your next car simple and stress-free.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card"><div class="feature-icon">&#9889;</div><h3>Quality Vehicles</h3><p>Every car is inspected and quality-checked before it reaches you.</p></div>
            <div class="feature-card"><div class="feature-icon">&#128176;</div><h3>Affordable Prices</h3><p>Competitive and transparent pricing with honest value for money.</p></div>
            <div class="feature-card"><div class="feature-icon">&#10084;</div><h3>Trusted Service</h3><p>A dedicated team that guides you through every step of the process.</p></div>
            <div class="feature-card"><div class="feature-icon">&#128666;</div><h3>Easy Buying Process</h3><p>From browsing to purchase, the entire experience is quick and easy.</p></div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>How It Works</h2>
            <p>Getting behind the wheel of your new car is only four simple steps away.</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Browse Cars</h3><p>Explore our wide range of quality used and brand-new vehicles.</p></div>
            <div class="step"><span class="step-num">2</span><h3>Choose Your Vehicle</h3><p>Pick the car that best fits your budget and lifestyle.</p></div>
            <div class="step"><span class="step-num">3</span><h3>Send an Inquiry</h3><p>Reach out to our team and get all your questions answered.</p></div>
            <div class="step"><span class="step-num">4</span><h3>Complete the Purchase</h3><p>Finish the paperwork and drive home in your new car.</p></div>
        </div>
    </div>
</section>

<!-- Call To Action -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to find your next car?</h2>
        <p>Join hundreds of satisfied customers who found their perfect vehicle with CarBuy.</p>
        <a href="cars.php" class="btn btn-primary btn-lg">Browse Available Cars</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>