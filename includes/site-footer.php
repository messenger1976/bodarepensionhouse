<?php
$defaultFooterConfig = [
    'variant' => 'full',
    // Optional links for the minimal footer (login, forgot password, etc.)
    'links' => [],
];

$footerConfig = isset($footerConfig) && is_array($footerConfig)
    ? array_merge($defaultFooterConfig, $footerConfig)
    : $defaultFooterConfig;

if (!function_exists('adsense_render_unit')) {
    require_once __DIR__ . '/adsense.php';
}
adsense_render_unit('footer', 'adsense-footer container my-4');

if (!function_exists('bodare_site_config')) {
    require_once __DIR__ . '/site-config.php';
}
$footerSite = bodare_site_config();
$fh = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$footerCopyright = '&copy; ' . date('Y') . ' ' . $fh($footerSite['legal_name']) . '. All Rights Reserved.';

$scriptName = basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '');
$tabActive = static function ($files) use ($scriptName) {
    $files = (array) $files;
    return in_array($scriptName, $files, true) ? ' is-active' : '';
};
?>
<footer id="contact" class="site-footer hidden md:block<?php echo ($footerConfig['variant'] === 'minimal') ? ' site-footer--minimal' : ''; ?>">
    <div class="container">
        <?php if ($footerConfig['variant'] === 'minimal'): ?>
            <?php if (!empty($footerConfig['links']) && is_array($footerConfig['links'])): ?>
                <nav class="footer-minimal-links" aria-label="Footer">
                    <?php foreach ($footerConfig['links'] as $link): ?>
                        <?php
                        if (empty($link['href']) || empty($link['label'])) {
                            continue;
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <div class="footer-bottom">
                <p><?php echo $footerCopyright; ?></p>
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
                    <p><?php echo $fh($footerSite['about_text']); ?></p>
                </div>
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="rooms.php">Rooms</a></li>
                        <li><a href="amenities.php">Amenities</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="forgot-password.php">Forgot Password</a></li>
                        <li><a href="admin/login">Portal Admin</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <p>
                        <?php foreach (bodare_site_address_lines() as $addressLine): ?>
                        <?php echo $fh($addressLine); ?><br>
                        <?php endforeach; ?>
                        <?php if ($footerSite['phone_display'] !== ''): ?>
                        <a href="tel:<?php echo $fh($footerSite['phone_e164'] !== '' ? $footerSite['phone_e164'] : $footerSite['phone_display']); ?>"><?php echo $fh($footerSite['phone_display']); ?></a><br>
                        <?php endif; ?>
                        <?php if ($footerSite['phone_alt'] !== ''): ?>
                        <?php echo $fh($footerSite['phone_alt']); ?><br>
                        <?php endif; ?>
                        <?php if ($footerSite['email'] !== ''): ?>
                        <a href="mailto:<?php echo $fh($footerSite['email']); ?>"><?php echo $fh($footerSite['email']); ?></a>
                        <?php endif; ?>
                    </p>
                </div>
                <?php
                $socialMeta = [
                    'facebook' => ['Facebook', 'bi-facebook'],
                    'instagram' => ['Instagram', 'bi-instagram'],
                    'tiktok' => ['TikTok', 'bi-tiktok'],
                    'youtube' => ['YouTube', 'bi-youtube'],
                    'x' => ['X', 'bi-twitter-x'],
                    'linkedin' => ['LinkedIn', 'bi-linkedin'],
                ];
                $footerSocial = array_intersect_key((array) $footerSite['social'], $socialMeta);
                ?>
                <?php if (!empty($footerSocial)): ?>
                <div class="footer-column">
                    <h4>Get Social</h4>
                    <p>Follow us for updates, offers, and news.</p>
                    <div class="social-icons">
                        <?php foreach ($footerSocial as $network => $url): ?>
                        <a href="<?php echo $fh($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo $fh($footerSite['name'] . ' on ' . $socialMeta[$network][0]); ?>" title="<?php echo $fh($socialMeta[$network][0]); ?>"><i class="bi <?php echo $socialMeta[$network][1]; ?>" aria-hidden="true"></i></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="footer-bottom">
                <p><?php echo $footerCopyright; ?></p>
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
$messengerUrl = !empty($footerSite['messenger_url'])
    ? $footerSite['messenger_url']
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
