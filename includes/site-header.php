<?php
$defaultHeaderConfig = [
    'logo_href' => 'index.php',
    'logo_alt' => 'BODARE Pension House logo',
    'about_href' => 'index.php#about',
    'show_cart' => true,
    'cart_style' => 'display: flex;',
    'cart_icon_style' => '',
    'show_login_button' => true,
    'login_button_style' => '',
    'show_account_button' => true,
    'show_book_button' => true,
    'book_button_href' => 'rooms.php',
    'book_button_text' => 'Book Now',
    'show_user_menu' => false,
    'show_logout' => false,
    'account_button_style' => 'display: none;',
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

        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="nav-menu app-nav-desktop">
            <li class="nav-item"><a href="<?php echo $h($headerConfig['about_href']); ?>" class="nav-link<?php echo $navActive('index.php'); ?>">About</a></li>
            <li class="nav-item"><a href="rooms.php" class="nav-link<?php echo $navActive('rooms.php'); ?>">Rooms</a></li>
            <li class="nav-item"><a href="amenities.php" class="nav-link<?php echo $navActive('amenities.php'); ?>">Amenities</a></li>
            <li class="nav-item"><a href="gallery.php" class="nav-link<?php echo $navActive('gallery.php'); ?>">Gallery</a></li>
            <li class="nav-item"><a href="contact.php" class="nav-link<?php echo $navActive('contact.php'); ?>">Contact</a></li>
        </ul>

        <?php if ($headerConfig['show_user_menu']): ?>
            <div class="user-menu app-header-actions">
                <span id="user-name-display"></span>
                <?php if ($headerConfig['show_cart']): ?>
                    <a href="cart.php" id="cart-link" class="cart-link app-icon-btn" title="Shopping Cart">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>
                <button id="logout-btn" class="cta-button-secondary app-btn-login">Logout</button>
            </div>
        <?php else: ?>
            <div class="header-actions app-header-actions">
                <?php if ($headerConfig['show_cart']): ?>
                    <a href="cart.php" id="cart-link" class="cart-link app-icon-btn" style="<?php echo $h($headerConfig['cart_style']); ?>" title="Shopping Cart">
                        <i class="bi bi-cart3" style="<?php echo $h($headerConfig['cart_icon_style']); ?>"></i>
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>

                <?php if ($headerConfig['show_login_button']): ?>
                    <a href="login.php" id="login-account-btn" class="cta-button-secondary app-btn-login" style="<?php echo $h($headerConfig['login_button_style']); ?>"><i class="bi bi-person-circle" aria-hidden="true"></i> Login</a>
                <?php endif; ?>

                <?php if ($headerConfig['show_account_button']): ?>
                    <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary app-btn-account" style="<?php echo $h($headerConfig['account_button_style']); ?>"><i class="bi bi-person-circle" aria-hidden="true"></i> Account</a>
                <?php endif; ?>

                <?php if (!empty($headerConfig['show_logout'])): ?>
                    <span id="user-name-display" class="app-user-name"></span>
                    <button type="button" id="logout-btn" class="cta-button-secondary app-btn-login">Logout</button>
                <?php endif; ?>

                <?php if ($headerConfig['show_book_button']): ?>
                    <a href="<?php echo $h($headerConfig['book_button_href']); ?>" class="cta-button app-btn-book"><?php echo $h($headerConfig['book_button_text']); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
