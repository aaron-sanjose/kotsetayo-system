<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
?>

<section class="page-banner page-banner-image">
    <div class="container">
        <h1>About KotseTayo</h1>
        <p>Your trusted partner in finding the perfect vehicle.</p>
    </div>
</section>

<section class="section">
    <div class="container about-grid">
        <div class="about-text">
            <h2>Who We Are</h2>
            <p>KotseTayo is a trusted car dealership focused on making vehicle buying simple, transparent, and convenient. We offer a carefully selected range of quality used and brand-new vehicles that meet the needs and budgets of modern drivers.</p>
            <p>From your very first search to driving off the lot, our experienced team is here to guide you with honest advice and reliable service.</p>
        </div>
        <div class="about-media">
            <img src="assets/images/Banner.png" alt="About KotseTayo">
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
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.6 5.6 6.1v5.1c0 3.9 2.6 7.4 6.4 9.1 3.8-1.7 6.4-5.2 6.4-9.1V6.1L12 3.6Z"/><path d="m9.3 12.1 2 2 3.5-3.8"/></svg>
                </div>
                <h3>Verified Quality</h3>
                <p>Every vehicle passes a thorough inspection before it is listed.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M11.5 3.8H6.4a2.6 2.6 0 0 0-2.6 2.6v5.1c0 .7.3 1.4.8 1.9l6.4 6.4a2.6 2.6 0 0 0 3.7 0l3.9-3.9a2.6 2.6 0 0 0 0-3.7l-6.4-6.4a2.6 2.6 0 0 0-1.9-.8Z"/><circle cx="8.4" cy="8.4" r="1.4"/></svg>
                </div>
                <h3>Transparent Pricing</h3>
                <p>Honest prices with no hidden fees or surprises.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 12.3c0 4-3.6 7.2-8 7.2-1 0-2-.2-2.9-.5L5 20.5l1.1-3.3A6.9 6.9 0 0 1 4 12.3C4 8.2 7.6 5 12 5s8 3.2 8 7.3Z"/><path d="M9 12.2h.01"/><path d="M12 12.2h.01"/><path d="M15 12.2h.01"/></svg>
                </div>
                <h3>Friendly Support</h3>
                <p>A dedicated team that answers your questions quickly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="12" rx="2.6"/><path d="M3 10.6h18"/><path d="M7 14.6h3.2"/></svg>
                </div>
                <h3>Easy Financing Help</h3>
                <p>We guide you through a simple, flexible buying process.</p>
            </div>
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

<section class="section">
    <div class="container">
        <div class="section-head"><h2>Meet Our Team</h2><p>The people behind KotseTayo, ready to serve you.</p></div>
        <div class="team-grid">
            <div class="team-card">
                <img class="team-avatar" src="assets/images/aaronsanjose.jpg" alt="Aaron San Jose">
                <div class="team-body">
                    <h3>Aaron San Jose</h3>
                    <span class="team-role">Owner</span>
                    <p>Leads KotseTayo with a passion for honest, customer-first car selling.</p>
                </div>
            </div>
            <div class="team-card">
                <img class="team-avatar" src="assets/images/vansarcauga.jpg" alt="Van Sarcauga">
                <div class="team-body">
                    <h3>Van Sarcauga</h3>
                    <span class="team-role">Quality Inspector</span>
                    <p>Ensures every vehicle passes a strict check before it is listed.</p>
                </div>
            </div>
            <div class="team-card">
                <img class="team-avatar" src="assets/images/jeancarpina.jpg" alt="Jean Emmanuel Carpina">
                <div class="team-body">
                    <h3>Jean Emmanuel Carpina</h3>
                    <span class="team-role">Sales Manager</span>
                    <p>Guides customers from first inquiry to driving home their new car.</p>
                </div>
            </div>
            <div class="team-card">
                <img class="team-avatar" src="assets/images/christopbatu.jpg" alt="Christopher Batu">
                <div class="team-body">
                    <h3>Christopher Batu</h3>
                    <span class="team-role">Customer Relations Officer</span>
                    <p>Answers your questions fast and makes every visit feel welcome.</p>
                </div>
            </div>
            <div class="team-card">
                <img class="team-avatar" src="assets/images/aaronmagdaraog.jpg" alt="Aaron James Magdaraog">
                <div class="team-body">
                    <h3>Aaron James Magdaraog</h3>
                    <span class="team-role">Marketing Specialist</span>
                    <p>Shares KotseTayo's best deals with drivers across Rizal.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
