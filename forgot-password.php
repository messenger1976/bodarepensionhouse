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

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_cart' => false
    ];
    include __DIR__ . '/includes/site-header.php';
?>


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
                        showMessage(response.message || 'If an account exists with this email, a password reset link has been sent. Please check your inbox and spam folder.', 'success');
                        form.reset();
                    } else {
                        showMessage(response.message || 'We couldn\'t process your request. Please try again later.', 'error');
                    }
                } catch (error) {
                    showMessage(error.message || 'We couldn\'t process your request. Please try again later.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    </script>
</body>
</html>





