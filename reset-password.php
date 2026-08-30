<?php
$pageSeo = [
    'title' => 'Reset Password | BODARE Pension House',
    'description' => 'Choose a new password for your BODARE Pension House guest account.',
    'canonical_path' => 'reset-password.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_cart' => false
    ];
    include __DIR__ . '/includes/site-header.php';
?>


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

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js"></script>
    <script src="booking-api.js"></script>
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
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
</body>
</html>





