<?php
$defaultHeaderConfig = [
    'logo_href' => 'https://bodarempc.com/',
    'logo_alt' => 'Bodare Logo',
    'about_href' => 'index.php#about',
    'show_cart' => true,
    'cart_style' => 'display: flex;',
    'cart_icon_style' => 'font-size: 1.5rem;',
    'show_login_button' => true,
    'login_button_style' => 'display: inline-block;',
    'show_account_button' => true,
    'show_book_button' => true,
    'book_button_href' => 'rooms.php',
    'book_button_text' => 'Book Now',
    'show_user_menu' => false,
];

$headerConfig = isset($headerConfig) && is_array($headerConfig)
    ? array_merge($defaultHeaderConfig, $headerConfig)
    : $defaultHeaderConfig;
?>
<header class="header">
    <nav class="navbar">
        <a href="<?php echo htmlspecialchars($headerConfig['logo_href'], ENT_QUOTES, 'UTF-8'); ?>" class="nav-logo">
            <img src="img/logo.png" alt="<?php echo htmlspecialchars($headerConfig['logo_alt'], ENT_QUOTES, 'UTF-8'); ?>" class="logo-img">
        </a>
        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-menu">
            <li class="nav-item"><a href="<?php echo htmlspecialchars($headerConfig['about_href'], ENT_QUOTES, 'UTF-8'); ?>" class="nav-link">About</a></li>
            <li class="nav-item"><a href="rooms.php" class="nav-link">Rooms</a></li>
            <li class="nav-item"><a href="amenities.php" class="nav-link">Amenities</a></li>
            <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
            <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
        </ul>

        <?php if ($headerConfig['show_user_menu']): ?>
            <div class="user-menu">
                <span id="user-name-display"></span>
                <?php if ($headerConfig['show_cart']): ?>
                    <a href="cart.php" id="cart-link" class="cart-link" title="Shopping Cart">
                        <i class="bi bi-cart" style="font-size: 1.25rem;"></i>
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>
                <button id="logout-btn" class="cta-button-secondary">Logout</button>
            </div>
        <?php else: ?>
            <div class="header-actions">
                <?php if ($headerConfig['show_cart']): ?>
                    <a href="cart.php" id="cart-link" class="cart-link" style="<?php echo htmlspecialchars($headerConfig['cart_style'], ENT_QUOTES, 'UTF-8'); ?>" title="Shopping Cart">
                        <i class="bi bi-cart" style="<?php echo htmlspecialchars($headerConfig['cart_icon_style'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>

                <?php if ($headerConfig['show_login_button']): ?>
                    <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="<?php echo htmlspecialchars($headerConfig['login_button_style'], ENT_QUOTES, 'UTF-8'); ?>">Login</a>
                <?php endif; ?>

                <?php if ($headerConfig['show_account_button']): ?>
                    <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <?php endif; ?>

                <?php if ($headerConfig['show_book_button']): ?>
                    <a href="<?php echo htmlspecialchars($headerConfig['book_button_href'], ENT_QUOTES, 'UTF-8'); ?>" class="cta-button"><?php echo htmlspecialchars($headerConfig['book_button_text'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </nav>
</header>
