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
            <p>KotseTayo is a trusted car dealership focused on making vehicle buying simple, transparent, and convenient.</p>
            <div class="socials">
                <a href="#" aria-label="Facebook">F</a>
                <a href="#" aria-label="Instagram">I</a>
                <a href="#" aria-label="Twitter">T</a>
                <a href="#" aria-label="YouTube">Y</a>
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
                <li>1234 Auto Plaza, Business District, City</li>
                <li>(02) 1234 5678 / +63 912 345 6789</li>
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