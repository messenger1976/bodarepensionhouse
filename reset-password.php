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
    <title>Reset Password - BODARE Pension House</title>
    
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
            <h1>Reset Your Password</h1>
            <p>Enter your new password below</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="registration-container" style="max-width: 500px; margin: 0 auto;">
                <h2>Set New Password</h2>
                
                <div id="token-error" style="display: none; padding: 1rem; margin: 1rem 0; border-radius: 4px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                    <p id="token-error-message"></p>
                </div>
                
                <form id="reset-password-form" class="minimal-form" style="display: none;">
                    <div class="form-group-contact">
                        <label for="reset-password">New Password</label>
                        <input type="password" id="reset-password" placeholder="Enter your new password" required autocomplete="new-password" minlength="6">
                        <small style="color: #666; font-size: 0.9em;">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="form-group-contact">
                        <label for="reset-confirm-password">Confirm New Password</label>
                        <input type="password" id="reset-confirm-password" placeholder="Confirm your new password" required autocomplete="new-password" minlength="6">
                    </div>
                    
                    <button type="submit" class="cta-button" style="width: 100%;">Reset Password</button>

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
            
            const form = document.getElementById('reset-password-form');
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
        
        function showTokenError(message) {
            const errorDiv = document.getElementById('token-error');
            const errorMessage = document.getElementById('token-error-message');
            if (errorDiv && errorMessage) {
                errorMessage.textContent = message;
                errorDiv.style.display = 'block';
            }
        }
        
        document.addEventListener('DOMContentLoaded', async () => {
            // Get token from URL
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            
            if (!token) {
                showTokenError('Invalid or missing reset token. Please request a new password reset link.');
                return;
            }
            
            // Verify token is valid
            try {
                const response = await API.auth.verifyResetToken(token);
                
                if (response.success) {
                    // Show form
                    document.getElementById('reset-password-form').style.display = 'block';
                } else {
                    showTokenError(response.message || 'This reset link is invalid or has expired. Please request a new password reset link.');
                }
            } catch (error) {
                showTokenError(error.message || 'Unable to verify reset token. Please request a new password reset link.');
            }
            
            // Setup form submission
            const form = document.getElementById('reset-password-form');
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Clear previous messages
                const existing = document.querySelector('.api-message');
                if (existing) existing.remove();
                
                const password = document.getElementById('reset-password').value;
                const confirmPassword = document.getElementById('reset-confirm-password').value;
                
                // Validation
                if (password.length < 6) {
                    showMessage('Password must be at least 6 characters long.', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    showMessage('Passwords do not match. Please try again.', 'error');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Resetting...';
                
                try {
                    const response = await API.auth.resetPassword(token, password);
                    
                    if (response.success) {
                        showMessage('Your password has been reset successfully! Redirecting to login...', 'success');
                        
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    }
                } catch (error) {
                    let errorMessage = 'We couldn\'t reset your password. Please try again.';
                    
                    if (error.message) {
                        if (error.message.includes('expired') || error.message.includes('invalid')) {
                            errorMessage = 'This reset link has expired or is invalid. Please request a new password reset link.';
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

