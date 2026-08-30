<?php
$defaultFooterConfig = [
    'variant' => 'full',
];

$footerConfig = isset($footerConfig) && is_array($footerConfig)
    ? array_merge($defaultFooterConfig, $footerConfig)
    : $defaultFooterConfig;

if (!function_exists('adsense_render_unit')) {
    require_once __DIR__ . '/adsense.php';
}
adsense_render_unit('footer', 'adsense-footer container my-4');

$scriptName = basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '');
$tabActive = static function ($files) use ($scriptName) {
    $files = (array) $files;
    return in_array($scriptName, $files, true) ? ' is-active' : '';
};
?>
<footer id="contact" class="site-footer hidden md:block<?php echo ($footerConfig['variant'] === 'minimal') ? ' site-footer--minimal' : ''; ?>">
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
                    <h4>Get Social</h4>
                    <p>Follow us on Facebook for updates, offers, and news.</p>
                    <div class="social-icons">
                        <?php
                        if (!function_exists('bodare_site_config')) {
                            require_once __DIR__ . '/site-config.php';
                        }
                        $footerSite = bodare_site_config();
                        $facebookUrl = !empty($footerSite['facebook_url'])
                            ? $footerSite['facebook_url']
                            : 'https://www.facebook.com/bodarepensionhouse';
                        ?>
                        <a href="<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="BODARE Pension House on Facebook" title="Facebook">F</a>
                    </div>
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

<nav class="app-tabbar pb-safe md:hidden" aria-label="Primary">
    <div class="app-tabbar-inner">
        <a href="index.php" class="app-tab<?php echo $tabActive(['index.php', '']); ?>" data-tab="explore">
            <i class="bi bi-compass"></i>
            <span>Explore</span>
        </a>
        <a href="rooms.php" id="nav-tab-bookings" class="app-tab<?php echo $tabActive(['rooms.php', 'room-detail.php', 'cart.php', 'checkout.php', 'booking-confirmation.php', 'customer-dashboard.php']); ?>" data-tab="bookings">
            <i class="bi bi-calendar3"></i>
            <span>Bookings</span>
        </a>
        <a href="contact.php#location" class="app-tab<?php echo $tabActive(['contact.php']); ?>" data-tab="location">
            <i class="bi bi-geo-alt"></i>
            <span>Location</span>
        </a>
        <a href="login.php" id="nav-tab-profile" class="app-tab<?php echo $tabActive(['login.php', 'registration.php', 'customer-dashboard.php', 'forgot-password.php']); ?>" data-tab="profile">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>
    </div>
</nav>
<?php
if (!function_exists('bodare_site_config')) {
    require_once __DIR__ . '/site-config.php';
}
$messengerSite = bodare_site_config();
$messengerUrl = !empty($messengerSite['messenger_url'])
    ? $messengerSite['messenger_url']
    : 'https://m.me/bodarepensionhouse';
?>
<a href="<?php echo htmlspecialchars($messengerUrl, ENT_QUOTES, 'UTF-8'); ?>"
   class="floating-messenger-btn"
   aria-label="Chat with us on Messenger"
   target="_blank"
   rel="noopener noreferrer">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M12 0C5.373 0 0 4.975 0 11.111c0 3.497 1.745 6.616 4.472 8.652V24l4.086-2.242c1.09.301 2.246.464 3.442.464 6.627 0 12-4.974 12-11.111C24 4.975 18.627 0 12 0zm1.193 14.963l-3.056-3.259-5.963 3.259L10.732 8.1l3.13 3.259L19.752 8.1l-6.559 6.863z"/>
    </svg>
</a>
