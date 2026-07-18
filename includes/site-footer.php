<?php
$defaultFooterConfig = [
    'variant' => 'full',
];

$footerConfig = isset($footerConfig) && is_array($footerConfig)
    ? array_merge($defaultFooterConfig, $footerConfig)
    : $defaultFooterConfig;
?>
<footer id="contact" class="site-footer">
    <div class="container">
        <?php if ($footerConfig['variant'] === 'minimal'): ?>
            <div class="footer-bottom">
                <p>&copy; 2026 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
            </div>
        <?php else: ?>
            <div class="newsletter-section">
                <h3>Join Our Newsletter</h3>
                <p>Sign up to our newsletter to receive our latest news about offers & promotions.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email address">
                    <button type="submit">Subscribe</button>
                </form>
            </div>

            <div class="footer-grid">
                <div class="footer-column">
                    <h4>About Us</h4>
                    <p>Bodare and Community Multi-Purpose Cooperative offers comfortable and affordable lodging in the heart of Tagbilaran City, providing a welcoming stay for all our guests.</p>
                </div>
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="rooms.php">Rooms</a></li>
                        <li><a href="amenities.php">Amenities</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="admin/login">Portal Admin</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <p>
                        BODARE MPC &amp; Community Bldg<br>
                        J.A. Clarin St., Dao District<br>
                        Tagbilaran City, Bohol<br>
                        Philippines 6300<br>
                        <a href="tel:+639505337480">0950 533 7480</a><br>
                        <a href="mailto:bodarepensionhouse@yahoo.com">bodarepensionhouse@yahoo.com</a>
                    </p>
                </div>
                <div class="footer-column">
                    <h4>Stay Connected</h4>
                    <p>For reservations and updates, contact us by phone or email. Social profile links will appear here once available.</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
                <div class="payment-methods">
                    <span>Payment methods:</span>
                    <span>Visa</span>
                    <span>Cash</span>
                    <span>GCash</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</footer>
