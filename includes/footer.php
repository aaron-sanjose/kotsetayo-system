</main>

<?php
/**
 * KotseTayo - Public Footer
 */
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col footer-about">
            <a href="<?php echo asset_uri('index.php'); ?>" class="brand brand-footer">
                <img src="<?php echo asset_uri('assets/images/KotseTayo.png'); ?>" alt="KotseTayo logo" class="brand-mark">
            </a>
            <p>Your most trusted online marketplace for high-quality pre-owned vehicles in the Philippines. Transparent deals, verified sellers, and smooth transitions.</p>
            <div class="socials">

            </div>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?php echo asset_uri('index.php'); ?>">Home</a></li>
                <li><a href="<?php echo asset_uri('cars.php'); ?>">Cars</a></li>
                <li><a href="<?php echo asset_uri('about.php'); ?>">About</a></li>
                <li><a href="<?php echo asset_uri('contact.php'); ?>">Contact</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <ul class="footer-contact">
                <li>P Burgos St, Concepcion, Baras, Rizal</li>
                <li>+63 (2) 8911-2233 / +63 (917) 555-4321</li>
                <li>sales@kotsetayo.example.com</li>
                <li>Mon - Sat: 9:00 AM - 6:00 PM</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> KotseTayo. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?php echo asset_uri('assets/js/script.js'); ?>"></script>
</body>
</html>