<?php
$pageSeo = [
    'title' => 'Activate Account | BODARE Pension House',
    'description' => 'Confirm your email address to activate your BODARE Pension House guest account.',
    'canonical_path' => 'activate-account.php',
    'robots' => 'noindex,nofollow',
    'include_bootstrap_icons' => false,
];
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
            <h1>Activate Your Account</h1>
            <p>Confirming your email address</p>
        </div>
    </section>

    <main class="content-section">
        <div class="container">
            <div class="registration-container" style="max-width: 500px; margin: 0 auto;">
                <h2>Email Confirmation</h2>
                <div id="activation-status" style="padding: 1rem; margin: 1rem 0; border-radius: 4px; background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">
                    Activating your account, please wait...
                </div>
                <p class="form-subtext" style="text-align: center; margin-top: 1.5rem;">
                    <a href="login.php" style="color: #b2945b; font-weight: 500;">Go to Login</a>
                </p>
            </div>
        </div>
    </main>

    <?php
    $footerConfig = ['variant' => 'minimal'];
    include __DIR__ . '/includes/site-footer.php';
?>
    
    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        function setStatus(message, type) {
            const el = document.getElementById('activation-status');
            if (!el) return;
            el.textContent = message;
            el.style.background = type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1';
            el.style.color = type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460';
            el.style.borderColor = type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb';
        }

        document.addEventListener('DOMContentLoaded', async () => {
            if (typeof API === 'undefined') {
                setStatus('API configuration failed to load. Please refresh the page.', 'error');
                return;
            }

            const token = new URLSearchParams(window.location.search).get('token');
            if (!token) {
                setStatus('Invalid or missing activation link. Please use the link from your confirmation email.', 'error');
                return;
            }

            try {
                const response = await API.auth.activateAccount(token);
                if (response.success) {
                    setStatus(response.message || 'Your email has been confirmed. You can now log in.', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2500);
                } else {
                    setStatus(response.message || 'Unable to activate your account.', 'error');
                }
            } catch (error) {
                setStatus(error.message || 'Unable to activate your account. The link may be invalid or expired.', 'error');
            }
        });
    </script>
</body>
</html>
