<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Login to your BODARE Pension House account">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Login - BODARE Pension House</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400&display=swap" rel="stylesheet">
</head>
<body>

    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="nav-logo">
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
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none !important;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1>Login to Your Account</h1>
            <p>Access your bookings and manage your reservations</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="registration-container" style="max-width: 500px; margin: 0 auto;">
                <h2>Login to Your Account</h2>
                <p style="text-align: center; color: #666; margin-bottom: 2rem;">Enter your credentials to access your dashboard</p>
                <form id="login-form" class="minimal-form">
                    <div class="form-group-contact">
                        <label for="login-email-input">Email Address</label>
                        <input type="email" id="login-email-input" placeholder="Enter your email address" required autocomplete="email">
                    </div>
                    <div class="form-group-contact">
                        <label for="login-password-input">Password</label>
                        <input type="password" id="login-password-input" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    
                    <button type="submit" class="cta-button" style="width: 100%;">Login</button>

                    <p class="form-subtext" style="text-align: center; margin-top: 1rem;">
                        <a href="forgot-password.php" style="color: #b2945b; font-weight: 500; font-size: 0.95em;">Forgot your password?</a>
                    </p>

                    <p class="form-subtext" style="text-align: center; margin-top: 1.5rem;">
                        Don't have an account? <a href="registration.php" style="color: #b2945b; font-weight: 500;">Create one here</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

    <footer id="contact" class="site-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Bodare and Community Multi-Purpose Cooperative. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="api-config.js"></script>
    <script src="booking-api.js"></script>
    <script src="script.js"></script>
    <script>
        // Check if API is loaded
        if (typeof API === 'undefined') {
            console.error('API configuration not loaded! Check if api-config.js is accessible.');
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('login-form');
                if (form) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'api-message error';
                    errorMsg.textContent = 'API configuration failed to load. Please check your internet connection and refresh the page.';
                    errorMsg.style.cssText = 'padding: 1rem; margin: 1rem 0; border-radius: 4px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;';
                    form.insertBefore(errorMsg, form.firstChild);
                }
            });
        }
        
        // Check if user is already logged in
        document.addEventListener('DOMContentLoaded', async () => {
            // Ensure API is loaded before proceeding
            if (typeof API === 'undefined') {
                console.error('API is not defined. Cannot proceed with login check.');
                return;
            }
            
            // Check if user is already logged in
            const userStr = localStorage.getItem('user');
            if (userStr) {
                try {
                    // Verify session is still valid
                    const response = await API.auth.check();
                    if (response.success && response.logged_in) {
                        // User is logged in, redirect to dashboard
                        const redirect = new URLSearchParams(window.location.search).get('redirect') || 'customer-dashboard.php';
                        window.location.href = redirect;
                        return;
                    }
                } catch (error) {
                    // Session expired, clear localStorage
                    localStorage.removeItem('user');
                }
            }
            
            // Setup login form
            const form = document.getElementById('login-form');
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Check if API is loaded
                if (typeof API === 'undefined') {
                    showMessage('API configuration not loaded. Please refresh the page and try again.', 'error');
                    return;
                }
                
                // Clear previous error messages
                const existing = document.querySelector('.api-message');
                if (existing) existing.remove();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Logging in...';
                
                const email = document.getElementById('login-email-input').value.trim().toLowerCase();
                const password = document.getElementById('login-password-input').value;
                
                // Basic validation
                if (!email || !password) {
                    showMessage('Please enter both your email address and password to continue.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                try {
                    const response = await API.auth.login(email, password);
                    
                    if (response.success) {
                        // Show success message
                        showMessage('Welcome back! You have successfully logged in. Redirecting...', 'success');
                        
                        // Store user info
                        localStorage.setItem('user', JSON.stringify(response.user));
                        
                        // Redirect to dashboard or previous page
                        const redirect = new URLSearchParams(window.location.search).get('redirect') || 'customer-dashboard.php';
                        setTimeout(() => {
                            window.location.href = redirect;
                        }, 1000);
                    }
                } catch (error) {
                    // Handle API errors
                    let errorMessage = 'We couldn\'t log you in. Please check your email and password and try again.';
                    
                    if (error.message) {
                        if (error.message.includes('No account found') || error.message.includes('email')) {
                            errorMessage = 'No account found with this email address. Please register first or check your email.';
                        } else if (error.message.includes('password') || error.message.includes('incorrect')) {
                            errorMessage = 'The password you entered is incorrect. Please try again.';
                        } else if (error.message.includes('not active')) {
                            errorMessage = 'Your account is not active. Please contact support for assistance.';
                        } else {
                            errorMessage = error.message;
                        }
                    }
                    
                    showMessage(errorMessage, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
        
        // Message display helper
        function showMessage(message, type = 'info') {
            const existing = document.querySelector('.api-message');
            if (existing) existing.remove();
            
            const form = document.getElementById('login-form');
            if (!form) return;
            
            const messageEl = document.createElement('div');
            messageEl.className = `api-message ${type}`;
            messageEl.textContent = message;
            messageEl.style.cssText = `
                padding: 1rem;
                margin: 1rem 0;
                border-radius: 4px;
                background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            `;
            
            form.insertBefore(messageEl, form.firstChild);
            
            // Auto remove after timeout
            const timeout = type === 'error' ? 8000 : 5000;
            setTimeout(() => {
                messageEl.remove();
            }, timeout);
        }
    </script>
    <script>
        // Check login status and update header button
        async function updateLoginButton() {
            const loginBtn = document.getElementById('login-account-btn');
            const accountBtn = document.getElementById('my-account-btn');
            
            if (!loginBtn || !accountBtn) return;
            
            // Hide login button if we're on the login page
            if (window.location.pathname.includes('login.php')) {
                loginBtn.style.display = 'none';
                return;
            }
            
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

