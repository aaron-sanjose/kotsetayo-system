<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch 6 featured available vehicles with their first image.
$featured = fetch_all(
    "SELECT c.*, (SELECT ci.image_path FROM car_images ci WHERE ci.car_id = c.id ORDER BY ci.sort_order, ci.id LIMIT 1) AS image_path
     FROM cars c
     WHERE c.status = 'Available'
     ORDER BY c.created_at DESC
     LIMIT 6"
);

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-content">
            <h1>Find Your Perfect Car</h1>
            <p>Browse quality pre-owned vehicles at the best prices in the market. Guaranteed fully inspected, transparent history, and ready for transfer.</p>
            <form class="hero-search" action="cars.php" method="get" role="search">
                <label class="sr-only" for="hero-search-query">Search vehicles</label>
                <span class="hero-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                </span>
                <input class="hero-search-input" id="hero-search-query" name="q" type="search" placeholder="Enter keyword, brand, model..." autocomplete="off">
                <button class="hero-search-submit" type="submit">Search</button>
            </form>
        </div>
    </div>
</section>

<!-- Featured Cars -->
<section class="section">
    <div class="container">
        <div class="section-head section-head-left">
            <h2>Featured Vehicles</h2>
            <p>Handpicked quality cars newly arrived on our lot.</p>
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

<!-- Why Choose KotseTayo -->
<section class="section section-alt why-section">
    <div class="container why-layout">
        <div class="why-intro">
            <span>Why KotseTayo</span>
            <h2>Buying a car shouldn&rsquo;t feel like a gamble.</h2>
            <p>We carefully source, inspect, and document every vehicle before it reaches our lot. This way, you can shop with confidence and focus on finding the car that’s right for you.</p>
            <ul class="why-points">
                <li>
                    <svg class="why-check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12.5 4.5 4.5L19 7"/></svg>
                    Inspection report provided for every listed vehicle
                </li>
                <li>
                    <svg class="why-check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12.5 4.5 4.5L19 7"/></svg>
                    Transfer and paperwork assistance from day one
                </li>
                <li>
                    <svg class="why-check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12.5 4.5 4.5L19 7"/></svg>
                    No hidden charges. The listed price is the price
                </li>
            </ul>
            <a href="<?php echo asset_uri('cars.php'); ?>" class="btn btn-primary btn-lg why-cta">
                Browse inventory
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h13"/><path d="m12.5 5.5 6.5 6.5-6.5 6.5"/></svg>
            </a>
        </div>

        <div class="why-cards">
            <article class="why-card why-card-highlight">
                <span class="why-card-index" aria-hidden="true">01</span>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.6 5.6 6.1v5.1c0 3.9 2.6 7.4 6.4 9.1 3.8-1.7 6.4-5.2 6.4-9.1V6.1L12 3.6Z"/><path d="m9.3 12.1 2 2 3.5-3.8"/></svg>
                </div>
                <h3>Quality Vehicles</h3>
                <p>Every car is inspected and quality-checked before it reaches you.</p>
            </article>
            <article class="why-card">
                <span class="why-card-index" aria-hidden="true">02</span>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M11.5 3.8H6.4a2.6 2.6 0 0 0-2.6 2.6v5.1c0 .7.3 1.4.8 1.9l6.4 6.4a2.6 2.6 0 0 0 3.7 0l3.9-3.9a2.6 2.6 0 0 0 0-3.7l-6.4-6.4a2.6 2.6 0 0 0-1.9-.8Z"/><circle cx="8.4" cy="8.4" r="1.4"/></svg>
                </div>
                <h3>Affordable Prices</h3>
                <p>Competitive and transparent pricing with honest value for money.</p>
            </article>
            <article class="why-card">
                <span class="why-card-index" aria-hidden="true">03</span>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 20.2s-7.2-4.4-7.2-9.3A4.2 4.2 0 0 1 12 8.2a4.2 4.2 0 0 1 7.2 2.7c0 4.9-7.2 9.3-7.2 9.3Z"/></svg>
                </div>
                <h3>Trusted Service</h3>
                <p>A dedicated team that guides you through every step of the process.</p>
            </article>
            <article class="why-card">
                <span class="why-card-index" aria-hidden="true">04</span>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9.4 4.8h5.2"/><path d="M9.9 3h4.2a1 1 0 0 1 1 1v2.2H8.9V4a1 1 0 0 1 1-1Z"/><path d="M6.6 5.6h-.9A1.8 1.8 0 0 0 3.9 7.4v11.1A1.8 1.8 0 0 0 5.7 20.3h12.6a1.8 1.8 0 0 0 1.8-1.8V7.4a1.8 1.8 0 0 0-1.8-1.8h-.9"/><path d="m9 13.4 2.1 2.1 4-4.3"/></svg>
                </div>
                <h3>Easy Buying Process</h3>
                <p>From browsing to purchase, the entire experience is quick and easy.</p>
            </article>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section">
    <div class="container">
        <div class="process-head">
            <div>
                <span>How It Works</span>
                <h2>From browsing to keys in hand, in four steps.</h2>
            </div>
            <p class="process-note">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.8V12l3.2 2"/></svg>
                Most buyers finish in 3&ndash;5 days
            </p>
        </div>

        <ol class="process-rail">
            <li class="process-step">
                <span class="process-marker" aria-hidden="true">1</span>
                <div class="process-body">
                    <h3>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="6.5"/><path d="m16.2 16.2 4.3 4.3"/></svg>
                        Browse Cars
                    </h3>
                    <p>Explore our wide range of quality used and brand-new vehicles.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="process-marker" aria-hidden="true">2</span>
                <div class="process-body">
                    <h3>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.5 16.5h17"/><path d="M5.5 16.5v-1.6c0-.3.1-.6.2-.9l1.7-3a2 2 0 0 1 1.8-1.1h5.6a2 2 0 0 1 1.8 1.1l1.7 3c.1.3.2.6.2.9v1.6"/><path d="M5.5 16.5v1.4a1 1 0 0 0 1 1h.7a1 1 0 0 0 1-1v-1.4"/><path d="M15.8 16.5v1.4a1 1 0 0 0 1 1h.7a1 1 0 0 0 1-1v-1.4"/><path d="M6.4 13.3h11.2"/></svg>
                        Choose Your Vehicle
                    </h3>
                    <p>Pick the car that best fits your budget and lifestyle.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="process-marker" aria-hidden="true">3</span>
                <div class="process-body">
                    <h3>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 12.3c0 4-3.6 7.2-8 7.2-1 0-2-.2-2.9-.5L5 20.5l1.1-3.3A6.9 6.9 0 0 1 4 12.3C4 8.2 7.6 5 12 5s8 3.2 8 7.3Z"/><path d="M9 12.2h.01"/><path d="M12 12.2h.01"/><path d="M15 12.2h.01"/></svg>
                        Send an Inquiry
                    </h3>
                    <p>Reach out to our team and get all your questions answered.</p>
                </div>
            </li>
            <li class="process-step">
                <span class="process-marker" aria-hidden="true">4</span>
                <div class="process-body">
                    <h3>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="8.2" cy="15.8" r="3.4"/><path d="m10.7 13.3 7.8-7.8"/><path d="m14.9 9.1 1.5 1.5"/><path d="m17.3 6.7 1.5 1.5"/></svg>
                        Complete the Purchase
                    </h3>
                    <p>Finish the paperwork and drive home in your new car.</p>
                </div>
            </li>
        </ol>
    </div>
</section>

<!-- Call To Action -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to find your next car?</h2>
        <p>Join hundreds of satisfied customers who found their perfect vehicle with KotseTayo.</p>
         <a href="cars.php" class="btn btn-accent btn-lg">Browse Available Cars</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
