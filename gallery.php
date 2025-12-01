<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View our photo gallery showcasing BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Gallery - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

    <header class="header">
        <nav class="navbar">
            <a href="https://bodarempc.com/" class="nav-logo">
                <img src="img/logo.png" alt="Bodare Logo" class="logo-img">
            </a>
            <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php#about" class="nav-link">About</a></li>
                <li class="nav-item"><a href="rooms.php" class="nav-link">Rooms</a></li>
                <li class="nav-item"><a href="amenities.php" class="nav-link">Amenities</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            <div class="header-actions">
                <a href="cart.php" id="cart-link" class="cart-link" style="display: flex;" title="Shopping Cart">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </a>
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header" style="background-image: url('img/ambassador.jpg');">
        <div class="page-header-content">
            <h1>Our Gallery</h1>
            <p>A glimpse into the comfort and style that awaits you.</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="gallery-grid">
                <img src="img/dormitory.jpg" alt="Dormitory" class="gallery-image">
                <img src="img/standard.jpg" alt="Standard Room" class="gallery-image">
                <img src="img/deluxeb.jpg" alt="Deluxe B Room" class="gallery-image">
                <img src="img/deluxea.jpg" alt="Deluxe A Room" class="gallery-image">
                <img src="img/ambassador.jpg" alt="Ambassador Room" class="gallery-image">
                <img src="img/executive.jpg" alt="Executive Room" class="gallery-image">
                </div>
        </div>
    </main>

    <footer id="contact" class="site-footer">
        </footer>
    
    <div id="lightbox-modal" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-image">
    </div>

<footer id="contact" class="site-footer">
    <div class="container">
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
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact</h4>
                <p>
                    123 Luxury Lane<br>
                    Tagbilaran City, Bohol 6300<br>
                    <a href="tel:+63384110000">(038) 411-0000</a><br>
                    <a href="mailto:reservations@bodarecoop.com">reservations@bodarecoop.com</a>
                </p>
            </div>
            <div class="footer-column">
                <h4>Get Social</h4>
                <p>Follow us on social platforms and keep in touch.</p>
                <div class="social-icons">
                    <a href="#">F</a>
                    <a href="#">T</a>
                    <a href="#">I</a>
                    <a href="#">Y</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
            <div class="payment-methods">
                <span>Payment methods:</span>
                <span>Visa</span>
                <span>Cash</span>
                <span>GCash</span>
            </div>
        </div>
    </div>
</footer>
    <script src="api-config.js"></script>
    <script src="script.js"></script>
    <script>
        // Check login status and update header button
        async function updateLoginButton() {
            const loginBtn = document.getElementById('login-account-btn');
            const accountBtn = document.getElementById('my-account-btn');
            
            if (!loginBtn || !accountBtn) return;
            
            try {
                if (typeof API !== 'undefined') {
                    const response = await API.auth.check();
                    if (response.success && response.logged_in) {
                        loginBtn.style.display = 'none';
                        accountBtn.style.display = 'inline-block';
                    } else {
                        loginBtn.style.display = 'inline-block';
                        accountBtn.style.display = 'none';
                    }
                } else {
                    loginBtn.style.display = 'inline-block';
                    accountBtn.style.display = 'none';
                }
            } catch (error) {
                loginBtn.style.display = 'inline-block';
                accountBtn.style.display = 'none';
            }
        }
        
        if (typeof API !== 'undefined') {
            updateLoginButton();
        } else {
            window.addEventListener('load', () => {
                if (typeof API !== 'undefined') {
                    updateLoginButton();
                }
            });
        }
        
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('ServiceWorker registration successful:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>