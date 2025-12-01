<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Reset your password at BODARE Pension House">
    <meta name="theme-color" content="#b2945b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BODARE">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Forgot Password - BODARE Pension House</title>
    
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
                <a href="login.php" id="login-account-btn" class="cta-button-secondary" style="display: none;">Login</a>
                <a href="customer-dashboard.php" id="my-account-btn" class="cta-button-secondary" style="display: none;">My Account</a>
                <a href="rooms.php" class="cta-button">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="page-header">
        <div class="page-header-content">
            <h1>Forgot Your Password?</h1>
            <p>Enter your email address and we'll send you a link to reset your password</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="registration-container" style="max-width: 500px; margin: 0 auto;">
                <h2>Reset Password</h2>
                <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                    No worries! Enter your email address and we'll send you instructions to reset your password.
                </p>
                
                <form id="forgot-password-form" class="minimal-form">
                    <div class="form-group-contact">
                        <label for="reset-email">Email Address</label>
                        <input type="email" id="reset-email" placeholder="Enter your registered email address" required autocomplete="email">
                    </div>
                    
                    <button type="submit" class="cta-button" style="width: 100%;">Send Reset Link</button>

                    <p class="form-subtext" style="text-align: center; margin-top: 1.5rem;">
                        Remember your password? <a href="login.php" style="color: #b2945b; font-weight: 500;">Login here</a>
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
    <script src="api-config.js"></script>
    <script src="script.js"></script>
    <script>
        // Message display helper
        function showMessage(message, type = 'info') {
            const existing = document.querySelector('.api-message');
            if (existing) existing.remove();
            
            const form = document.getElementById('forgot-password-form');
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
            
            const timeout = type === 'error' ? 8000 : 5000;
            setTimeout(() => {
                messageEl.remove();
            }, timeout);
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('forgot-password-form');
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Clear previous messages
                const existing = document.querySelector('.api-message');
                if (existing) existing.remove();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                
                const email = document.getElementById('reset-email').value.trim().toLowerCase();
                
                if (!email) {
                    showMessage('Please enter your email address.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                try {
                    const response = await API.auth.forgotPassword(email);
                    
                    if (response.success) {
                        showMessage(response.message || 'Password reset instructions have been sent to your email address. Please check your inbox and follow the link to reset your password.', 'success');
                        form.reset();
                        
                        // Show additional info if token is returned (for development/testing)
                        if (response.token && response.reset_url) {
                            const infoEl = document.createElement('div');
                            infoEl.className = 'api-message info';
                            infoEl.style.cssText = `
                                padding: 1rem;
                                margin: 1rem 0;
                                border-radius: 4px;
                                background: #d1ecf1;
                                color: #0c5460;
                                border: 1px solid #bee5eb;
                            `;
                            infoEl.innerHTML = `
                                <strong>Development Mode:</strong><br>
                                <a href="${response.reset_url}" style="color: #0c5460; text-decoration: underline;">Click here to reset your password</a>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.9em;">(This link is only shown in development mode)</p>
                            `;
                            form.insertBefore(infoEl, form.firstChild);
                        }
                    }
                } catch (error) {
                    let errorMessage = 'We couldn\'t process your request. Please try again later.';
                    
                    if (error.message) {
                        if (error.message.includes('not found') || error.message.includes('No account')) {
                            errorMessage = 'No account found with this email address. Please check your email or register for a new account.';
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
    </script>
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

