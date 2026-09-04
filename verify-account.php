<?php
$pageSeo = [
    'title' => 'Verify Your Account | BODARE Pension House',
    'description' => 'Enter the 6-digit verification code emailed to you to activate your BODARE Pension House guest account.',
    'canonical_path' => 'verify-account.php',
    'robots' => 'noindex,nofollow',
];
$enableAds = false;
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'logo_href' => 'index.php',
        'show_cart' => false,
    ];
    include __DIR__ . '/includes/site-header.php';
?>

    <section class="app-page-hero">
        <div class="page-header-content app-section" style="padding-top:0;padding-bottom:0;">
            <h1>Verify Your Account</h1>
            <p>Enter the 6-digit code we emailed you</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container">
            <div class="registration-container app-card" style="max-width: 480px; margin: 0 auto; padding: 1.5rem;">
                <h2 class="text-emerald-950 font-black text-lg" style="margin-bottom: 0.25rem;">Account Activation</h2>
                <p style="text-align: center; color: #666; margin-bottom: 1.25rem;" id="verify-intro-text">We sent a 6-digit code to your email. Enter it below to activate your account.</p>

                <form id="verify-otp-form" class="minimal-form" autocomplete="off">
                    <div class="form-group-contact">
                        <label for="verify-email-input">Email Address</label>
                        <input type="email" id="verify-email-input" placeholder="Enter your registered email" required autocomplete="email">
                    </div>
                    <div class="form-group-contact">
                        <label for="verify-code-input">6-Digit Verification Code</label>
                        <input type="text" id="verify-code-input" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                               placeholder="••••••" required
                               style="text-align:center; font-size:1.5rem; letter-spacing:0.6em; font-weight:600;"
                               aria-describedby="code-help">
                        <p id="code-help" style="font-size:0.8rem;color:#888;margin-top:0.35rem;text-align:center;">The code expires in 15 minutes.</p>
                    </div>

                    <button type="submit" id="verify-submit-btn" class="cta-button" style="width: 100%;">Activate My Account</button>

                    <div style="text-align:center; margin-top: 1.25rem;">
                        <span style="color:#666; font-size:0.95rem;">Didn't receive the code?</span>
                        <button type="button" id="resend-otp-btn" class="text-button" style="background:none;border:none;color:#b2945b;font-weight:600;cursor:pointer;padding:0;margin-left:0.25rem;font-size:0.95rem;">Resend code</button>
                        <span id="resend-countdown" style="display:none;color:#888;font-size:0.9rem;"></span>
                    </div>

                    <p class="form-subtext" style="text-align: center; margin-top: 1rem;">
                        Already activated? <a href="login.php" style="color: #065f46; font-weight: 500;">Log in here</a>
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
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        (function () {
            const emailInput = document.getElementById('verify-email-input');
            const codeInput = document.getElementById('verify-code-input');
            const form = document.getElementById('verify-otp-form');
            const submitBtn = document.getElementById('verify-submit-btn');
            const resendBtn = document.getElementById('resend-otp-btn');
            const countdownEl = document.getElementById('resend-countdown');
            const introEl = document.getElementById('verify-intro-text');

            let statusEl = null;
            let resendTimer = null;

            function esc(value) {
                const div = document.createElement('div');
                div.textContent = String(value == null ? '' : value);
                return div.innerHTML;
            }

            function setStatus(message, type) {
                if (statusEl) statusEl.remove();
                const box = document.createElement('div');
                box.className = 'api-message ' + type;
                box.style.cssText = 'padding:0.9rem 1rem;margin:0 0 1rem 0;border-radius:6px;' +
                    (type === 'success'
                        ? 'background:#d4edda;color:#155724;border:1px solid #c3e6cb;'
                        : type === 'error'
                            ? 'background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;'
                            : 'background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;');
                box.textContent = message;
                form.insertBefore(box, form.firstChild);
                statusEl = box;
            }

            function startResendCountdown(seconds) {
                if (resendTimer) clearInterval(resendTimer);
                resendBtn.style.display = 'none';
                countdownEl.style.display = 'inline';
                let left = Math.max(0, Math.floor(seconds));
                const tick = () => {
                    if (left <= 0) {
                        countdownEl.style.display = 'none';
                        resendBtn.style.display = 'inline';
                        clearInterval(resendTimer);
                        return;
                    }
                    countdownEl.textContent = '(wait ' + left + 's)';
                    left -= 1;
                };
                tick();
                resendTimer = setInterval(tick, 1000);
            }

            function currentEmail() {
                return emailInput ? emailInput.value.trim().toLowerCase() : '';
            }

            function refreshIntro() {
                if (!introEl) return;
                const email = currentEmail();
                if (email) {
                    introEl.textContent = 'We sent a 6-digit code to ' + email + '. Enter it below to activate your account.';
                } else {
                    introEl.textContent = 'Enter your email and the 6-digit code we sent to activate your account.';
                }
            }

            async function resendCode() {
                if (typeof API === 'undefined') {
                    setStatus('API configuration not loaded. Please refresh the page.', 'error');
                    return;
                }
                const email = currentEmail();
                if (!email) {
                    setStatus('Please enter your email address first so we can resend the code.', 'error');
                    emailInput.focus();
                    return;
                }
                resendBtn.disabled = true;
                try {
                    const response = await API.auth.sendActivationOtp(email);
                    if (response.success) {
                        setStatus('A new code has been sent to ' + email + '.', 'success');
                        const wait = (response.resend_after || 60);
                        startResendCountdown(wait);
                    } else {
                        setStatus(response.message || 'We could not send the code right now. Please try again.', 'error');
                        if (response.resend_after) {
                            startResendCountdown(response.resend_after);
                        } else {
                            resendBtn.disabled = false;
                        }
                    }
                } catch (error) {
                    if (error.response && error.response.resend_after) {
                        setStatus(error.response.message || 'Please wait before requesting another code.', 'error');
                        startResendCountdown(error.response.resend_after);
                    } else {
                        setStatus((error.response && error.response.message) || error.message || 'We could not send the code right now. Please try again.', 'error');
                        resendBtn.disabled = false;
                    }
                }
            }

            resendBtn.addEventListener('click', resendCode);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (typeof API === 'undefined') {
                    setStatus('API configuration not loaded. Please refresh the page.', 'error');
                    return;
                }

                const email = currentEmail();
                const code = codeInput ? codeInput.value.trim() : '';

                if (!email) {
                    setStatus('Please enter the email address you registered with.', 'error');
                    emailInput.focus();
                    return;
                }
                if (!/^\d{6}$/.test(code)) {
                    setStatus('Please enter the 6-digit code from the email.', 'error');
                    codeInput.focus();
                    return;
                }

                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Verifying...';

                try {
                    const response = await API.auth.verifyOtp(email, code);
                    if (response.success) {
                        // Already active -> go sign in normally.
                        if (response.already_active) {
                            setStatus('Your account is already active. Taking you to the login page...', 'success');
                            setTimeout(() => { window.location.href = 'login.php?email=' + encodeURIComponent(email); }, 2000);
                            return;
                        }
                        setStatus(response.message || 'Your account has been activated. Welcome!', 'success');
                        codeInput.value = '';
                        // Auto sign-in completed server-side (token stored).
                        setTimeout(() => { window.location.href = 'customer-dashboard.php'; }, 1800);
                    } else {
                        setStatus(response.message || 'That code is invalid. Please try again.', 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                } catch (error) {
                    const data = error.response || {};
                    if (data.code_locked) {
                        setStatus(data.message || 'Too many incorrect attempts. Please request a new code.', 'error');
                    } else {
                        setStatus(data.message || error.message || 'We could not verify the code. Please try again.', 'error');
                    }
                    codeInput.value = '';
                    codeInput.focus();
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });

            // Pre-fill email from ?email= and start the resend cooldown when the
            // guest has just registered (a code was sent moments ago).
            document.addEventListener('DOMContentLoaded', () => {
                const params = new URLSearchParams(window.location.search);
                const emailParam = (params.get('email') || '').trim();
                if (emailParam && emailInput) {
                    emailInput.value = emailParam.toLowerCase();
                    refreshIntro();
                }
                if (emailInput) {
                    emailInput.addEventListener('input', refreshIntro);
                }
                if (params.get('registered') === '1') {
                    startResendCountdown(60);
                }
                codeInput.focus();
            });
        })();
    </script>
</body>
</html>
