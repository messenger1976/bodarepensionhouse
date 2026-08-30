<?php
$defaultHeaderConfig = [
    'logo_href' => 'index.php',
    'logo_alt' => 'BODARE Pension House logo',
    'about_href' => 'index.php#about',
    'show_cart' => true,
    'cart_style' => 'display: flex;',
    'cart_icon_style' => '',
    'show_login_button' => true,
    'show_account_button' => true,
    'show_book_button' => true,
    'book_button_href' => 'rooms.php',
    'book_button_text' => 'Book Now',
];

$headerConfig = isset($headerConfig) && is_array($headerConfig)
    ? array_merge($defaultHeaderConfig, $headerConfig)
    : $defaultHeaderConfig;

$scriptName = basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '');
$navActive = static function ($file) use ($scriptName) {
    return $scriptName === $file ? ' is-active' : '';
};
$h = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<header class="header app-header pt-safe">
    <div class="app-header-inner">
        <a href="<?php echo $h($headerConfig['logo_href']); ?>" class="app-brand nav-logo">
            <img src="img/logo.png" alt="<?php echo $h($headerConfig['logo_alt']); ?>" class="app-brand-logo logo-img">
            <div class="app-brand-text">
                <p class="app-brand-name">BODARE Pension House</p>
                <p class="app-brand-loc">
                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                    J.A. Clarin St., Tagbilaran
                </p>
            </div>
        </a>

        <ul class="nav-menu app-nav-desktop">
            <li class="nav-item"><a href="<?php echo $h($headerConfig['about_href']); ?>" class="nav-link<?php echo $navActive('index.php'); ?>">About</a></li>
            <li class="nav-item"><a href="rooms.php" class="nav-link<?php echo $navActive('rooms.php'); ?>">Rooms</a></li>
            <li class="nav-item"><a href="amenities.php" class="nav-link<?php echo $navActive('amenities.php'); ?>">Amenities</a></li>
            <li class="nav-item"><a href="gallery.php" class="nav-link<?php echo $navActive('gallery.php'); ?>">Gallery</a></li>
            <li class="nav-item"><a href="contact.php" class="nav-link<?php echo $navActive('contact.php'); ?>">Contact</a></li>
        </ul>

        <div class="header-actions app-header-actions">
            <?php if ($headerConfig['show_cart']): ?>
                <a href="cart.php" id="cart-link" class="cart-link app-icon-btn" style="<?php echo $h($headerConfig['cart_style']); ?>" title="Shopping Cart">
                    <i class="bi bi-cart3" style="<?php echo $h($headerConfig['cart_icon_style']); ?>"></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
            <?php endif; ?>

            <button type="button" class="mobile-menu-toggle app-mobile-menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="app-mobile-nav">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="app-header-auth-desktop">
                <?php if ($headerConfig['show_login_button']): ?>
                    <a href="login.php" id="login-account-btn" class="cta-button-secondary app-btn-login app-auth-login"><i class="bi bi-person-circle" aria-hidden="true"></i> Login</a>
                <?php endif; ?>

                <?php if ($headerConfig['show_account_button']): ?>
                    <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary app-btn-account app-auth-account" style="display: none;"><i class="bi bi-person-circle" aria-hidden="true"></i> Account</a>
                <?php endif; ?>

                <button type="button" id="logout-btn" class="cta-button-secondary app-btn-login app-auth-logout" style="display: none;">Logout</button>

                <?php if ($headerConfig['show_book_button']): ?>
                    <a href="<?php echo $h($headerConfig['book_button_href']); ?>" class="cta-button app-btn-book"><?php echo $h($headerConfig['book_button_text']); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <span id="user-name-display" class="app-user-name-sr" aria-hidden="true"></span>
</header>

<div id="app-mobile-nav" class="app-mobile-nav" aria-hidden="true">
    <button type="button" class="app-mobile-nav-backdrop" aria-label="Close menu" tabindex="-1"></button>
    <div class="app-mobile-nav-panel">
        <div class="app-mobile-nav-head">
            <p class="app-mobile-nav-title">Menu</p>
            <button type="button" class="app-mobile-nav-close" aria-label="Close menu">&times;</button>
        </div>
        <ul class="app-mobile-nav-links">
            <li><a href="<?php echo $h($headerConfig['about_href']); ?>" class="app-mobile-nav-link<?php echo $navActive('index.php'); ?>"><i class="bi bi-house-door" aria-hidden="true"></i> About</a></li>
            <li><a href="rooms.php" class="app-mobile-nav-link<?php echo $navActive('rooms.php'); ?>"><i class="bi bi-door-open" aria-hidden="true"></i> Rooms</a></li>
            <li><a href="amenities.php" class="app-mobile-nav-link<?php echo $navActive('amenities.php'); ?>"><i class="bi bi-stars" aria-hidden="true"></i> Amenities</a></li>
            <li><a href="gallery.php" class="app-mobile-nav-link<?php echo $navActive('gallery.php'); ?>"><i class="bi bi-images" aria-hidden="true"></i> Gallery</a></li>
            <li><a href="contact.php" class="app-mobile-nav-link<?php echo $navActive('contact.php'); ?>"><i class="bi bi-geo-alt" aria-hidden="true"></i> Contact</a></li>
        </ul>
        <div class="app-mobile-nav-auth">
            <?php if ($headerConfig['show_login_button']): ?>
                <a href="login.php" class="app-mobile-nav-btn app-auth-login app-auth-login-mobile"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Login</a>
            <?php endif; ?>
            <?php if ($headerConfig['show_account_button']): ?>
                <a href="customer-dashboard.php" class="app-mobile-nav-btn app-auth-account app-auth-account-mobile" style="display: none;"><i class="bi bi-person-circle" aria-hidden="true"></i> Account</a>
            <?php endif; ?>
            <button type="button" class="app-mobile-nav-btn app-auth-logout app-auth-logout-mobile" style="display: none;"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Logout</button>
        </div>
    </div>
</div>
