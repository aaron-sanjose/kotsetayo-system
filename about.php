<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>About CarBuy</h1>
        <p>Your trusted partner in finding the perfect vehicle.</p>
    </div>
</section>

<section class="section">
    <div class="container about-grid">
        <div class="about-text">
            <h2>Who We Are</h2>
            <p>CarBuy is a trusted car dealership focused on making vehicle buying simple, transparent, and convenient. We offer a carefully selected range of quality used and brand-new vehicles that meet the needs and budgets of modern drivers.</p>
            <p>From your very first search to driving off the lot, our experienced team is here to guide you with honest advice and reliable service.</p>
        </div>
        <div class="about-media">
            <img src="assets/images/about-car.svg" alt="About CarBuy">
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container mission-vision">
        <div class="mv-card">
            <h3>Our Mission</h3>
            <p>To deliver quality vehicles and a stress-free buying experience that our customers can trust and recommend to others.</p>
        </div>
        <div class="mv-card">
            <h3>Our Vision</h3>
            <p>To become the leading local name in vehicle sales, known for transparency, value, and outstanding customer care.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><h2>Why Choose Us</h2></div>
        <div class="feature-grid">
            <div class="feature-card"><div class="feature-icon">&#9989;</div><h3>Verified Quality</h3><p>Every vehicle passes a thorough inspection before it is listed.</p></div>
            <div class="feature-card"><div class="feature-icon">&#128272;</div><h3>Transparent Pricing</h3><p>Honest prices with no hidden fees or surprises.</p></div>
            <div class="feature-card"><div class="feature-icon">&#129309;</div><h3>Friendly Support</h3><p>A dedicated team that answers your questions quickly.</p></div>
            <div class="feature-card"><div class="feature-icon">&#128176;</div><h3>Easy Financing Help</h3><p>We guide you through a simple, flexible buying process.</p></div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container stats-grid">
        <div class="stat-card"><strong><?php echo (int)fetch_one("SELECT COUNT(*) AS c FROM cars")['c']; ?></strong><span>Vehicles</span></div>
        <div class="stat-card"><strong><?php echo (int)fetch_one("SELECT COUNT(*) AS c FROM cars WHERE status = 'Sold'")['c']; ?></strong><span>Cars Sold</span></div>
        <div class="stat-card"><strong><?php echo (int)fetch_one("SELECT COUNT(*) AS c FROM customers")['c']; ?></strong><span>Happy Customers</span></div>
        <div class="stat-card"><strong><?php echo (int)fetch_one("SELECT COUNT(*) AS c FROM sales")['c']; ?></strong><span>Completed Sales</span></div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>