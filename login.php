<?php
$pageSeo = [
    'title' => 'Login | BODARE Pension House',
    'description' => 'Sign in to your BODARE Pension House guest account to manage bookings and inquiries.',
    'canonical_path' => 'login.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_cart' => true,
        'login_button_style' => 'display: none !important;'
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Login to Your Account</h1>
            <p>Access your bookings and manage your reservations</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container">
            <div class="registration-container app-card" style="max-width: 500px; margin: 0 auto; padding: 1.5rem;">
                <h2 class="text-emerald-950 font-black text-lg">Login to Your Account</h2>
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
                        <a href="forgot-password.php" style="color: #065f46; font-weight: 500; font-size: 0.95em;">Forgot your password?</a>
                    </p>

                    <p class="form-subtext" style="text-align: center; margin-top: 1.5rem;">
                        Don't have an account? <a href="registration.php" style="color: #065f46; font-weight: 500;">Create one here</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js?v=<?php echo filemtime(__DIR__ . '/api-config.js'); ?>"></script>
    <script src="booking-api.js?v=<?php echo filemtime(__DIR__ . '/booking-api.js'); ?>"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
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
                    // Session missing — clear the local account so login can proceed cleanly
                    if (typeof API.auth.clearLocalSession === 'function') {
                        API.auth.clearLocalSession();
                    } else {
                        localStorage.removeItem('user');
                    }
                } catch (error) {
                    // Session check failed, clear local account state
                    if (typeof API.auth.clearLocalSession === 'function') {
                        API.auth.clearLocalSession();
                    } else {
                        localStorage.removeItem('user');
                    }
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
                    const cartSnapshot = typeof getCartStorageSnapshot === 'function'
                        ? getCartStorageSnapshot()
                        : null;

                    const response = await API.auth.login(email, password);
                    
                    if (response.success) {
                        // Show success message
                        showMessage('Welcome back! You have successfully logged in. Redirecting...', 'success');
                        
                        // Store user info
                        localStorage.setItem('user', JSON.stringify(response.user));

                        // Ensure cart survives login transitions.
                        if (typeof restoreCartStorageSnapshot === 'function') {
                            restoreCartStorageSnapshot(cartSnapshot);
                        }
                        
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
</body>
</html>





