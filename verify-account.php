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
<style>
    .otp-boxes {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin: 0.25rem 0 0.35rem;
    }
    .otp-digit {
        width: 3rem;
        height: 3.4rem;
        text-align: center;
        font-size: 1.6rem;
        font-weight: 600;
        color: #1a2238;
        border: 2px solid #d5d9e2;
        border-radius: 8px;
        background: #fff;
        padding: 0;
        caret-color: #b2945b;
    }
    .otp-digit:focus {
        outline: none;
        border-color: #b2945b;
        box-shadow: 0 0 0 3px rgba(178, 148, 91, 0.25);
    }
    @media (max-width: 420px) {
        .otp-boxes { gap: 0.35rem; }
        .otp-digit { width: 2.6rem; height: 3rem; font-size: 1.4rem; }
    }
</style>
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
            <p>We emailed a 6-digit OTP to your email address</p>
        </div>
    </section>

    <main class="content-section app-section">
        <div class="container">
            <div class="registration-container app-card" style="max-width: 480px; margin: 0 auto; padding: 1.5rem;">
                <h2 class="text-emerald-950 font-black text-lg" style="margin-bottom: 0.25rem;">Enter Your OTP</h2>
                <p style="text-align: center; color: #666; margin-bottom: 1.25rem;" id="verify-intro-text">We sent a 6-digit OTP to your email. Enter it in the boxes below to activate your account.</p>

                <form id="verify-otp-form" class="minimal-form" autocomplete="off">
                    <div class="form-group-contact">
                        <label for="verify-email-input">Email Address</label>
                        <input type="email" id="verify-email-input" placeholder="Enter your registered email" required autocomplete="email">
                    </div>
                    <div class="form-group-contact">
                        <label>6-Digit OTP</label>
                        <div class="otp-boxes" id="otp-boxes" role="group" aria-label="6-digit verification code">
                            <input type="tel" inputmode="numeric" autocomplete="one-time-code" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="0" aria-label="Digit 1">
                            <input type="tel" inputmode="numeric" autocomplete="off" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="1" aria-label="Digit 2">
                            <input type="tel" inputmode="numeric" autocomplete="off" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="2" aria-label="Digit 3">
                            <input type="tel" inputmode="numeric" autocomplete="off" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="3" aria-label="Digit 4">
                            <input type="tel" inputmode="numeric" autocomplete="off" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="4" aria-label="Digit 5">
                            <input type="tel" inputmode="numeric" autocomplete="off" maxlength="1"
                                   pattern="[0-9]*" class="otp-digit" data-idx="5" aria-label="Digit 6">
                        </div>
                        <p id="code-help" style="font-size:0.8rem;color:#888;margin-top:0.35rem;text-align:center;">The OTP expires in 15 minutes.</p>
                    </div>

                    <button type="submit" id="verify-submit-btn" class="cta-button" style="width: 100%;">Verify &amp; Activate My Account</button>

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
            const otpBoxes = Array.from(document.querySelectorAll('.otp-digit'));
            const form = document.getElementById('verify-otp-form');
            const submitBtn = document.getElementById('verify-submit-btn');
            const resendBtn = document.getElementById('resend-otp-btn');
            const countdownEl = document.getElementById('resend-countdown');
            const introEl = document.getElementById('verify-intro-text');

            let statusEl = null;
            let resendTimer = null;

            // Seconds shown/waited before sending the guest to the dashboard
            // after a successful OTP. Easy to tweak.
            const REDIRECT_COUNTDOWN_SECONDS = 3;

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

            function getCode() {
                return otpBoxes.map(b => b.value).join('');
            }

            function clearBoxes() {
                otpBoxes.forEach(b => { b.value = ''; });
                if (otpBoxes.length) otpBoxes[0].focus();
            }

            function refreshIntro() {
                if (!introEl) return;
                const email = currentEmail();
                if (email) {
                    introEl.textContent = 'We emailed a 6-digit OTP to ' + email + '. Enter it below to activate your account.';
                } else {
                    introEl.textContent = 'Enter your email and the 6-digit OTP we emailed to activate your account.';
                }
            }

            // ---- 6-box OTP input behaviour ----
            otpBoxes.forEach((box, i) => {
                box.addEventListener('input', (e) => {
                    const digits = box.value.replace(/\D/g, '');
                    box.value = digits.slice(0, 1);
                    if (box.value && i < otpBoxes.length - 1) {
                        otpBoxes[i + 1].focus();
                    }
                    // Auto-submit when the last digit is typed.
                    if (i === otpBoxes.length - 1 && getCode().length === otpBoxes.length) {
                        form.requestSubmit();
                    }
                });

                box.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace') {
                        if (!box.value && i > 0) {
                            e.preventDefault();
                            otpBoxes[i - 1].value = '';
                            otpBoxes[i - 1].focus();
                        }
                    } else if (e.key === 'ArrowLeft' && i > 0) {
                        e.preventDefault();
                        otpBoxes[i - 1].focus();
                    } else if (e.key === 'ArrowRight' && i < otpBoxes.length - 1) {
                        e.preventDefault();
                        otpBoxes[i + 1].focus();
                    } else if (e.key.length === 1 && !/[0-9]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                        e.preventDefault();
                    }
                });

                box.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text') || '';
                    const digits = text.replace(/\D/g, '').slice(0, otpBoxes.length).split('');
                    otpBoxes.forEach((b, idx) => { b.value = digits[idx] || ''; });
                    const next = digits.length < otpBoxes.length ? digits.length : otpBoxes.length - 1;
                    otpBoxes[Math.min(next, otpBoxes.length - 1)].focus();
                    if (digits.length === otpBoxes.length) {
                        form.requestSubmit();
                    }
                });
            });

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
                        setStatus('A new OTP has been sent to ' + email + '.', 'success');
                        const wait = (response.resend_after || 60);
                        startResendCountdown(wait);
                        clearBoxes();
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
                const code = getCode();

                if (!email) {
                    setStatus('Please enter the email address you registered with.', 'error');
                    emailInput.focus();
                    return;
                }
                if (!/^\d{6}$/.test(code)) {
                    setStatus('Please enter the 6-digit code from the email.', 'error');
                    otpBoxes[0].focus();
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
                        clearBoxes();
                        // Token + session were persisted -> straight to the
                        // dashboard, logged in, after a short visible countdown.
                        const successBase = response.message || 'Your account has been activated. Welcome!';
                        let countdownLeft = REDIRECT_COUNTDOWN_SECONDS;
                        const paintCountdown = () => {
                            setStatus(countdownLeft > 0
                                ? successBase + ' Redirecting to your dashboard in ' + countdownLeft + '…'
                                : successBase, 'success');
                        };
                        paintCountdown();
                        const cdTimer = setInterval(() => {
                            countdownLeft -= 1;
                            if (countdownLeft <= 0) {
                                clearInterval(cdTimer);
                                window.location.href = 'customer-dashboard.php';
                                return;
                            }
                            paintCountdown();
                        }, 1000);
                    } else {
                        setStatus(response.message || 'That code is invalid. Please try again.', 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        clearBoxes();
                    }
                } catch (error) {
                    const data = error.response || {};
                    if (data.code_locked) {
                        setStatus(data.message || 'Too many incorrect attempts. Please request a new code.', 'error');
                    } else {
                        setStatus(data.message || error.message || 'We could not verify the code. Please try again.', 'error');
                    }
                    clearBoxes();
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
                    if (emailParam) {
                        setStatus('Your account was created! We emailed a 6-digit OTP to ' + emailParam.toLowerCase() + '. Enter it in the boxes above to activate your account.', 'success');
                    }
                }
                if (otpBoxes.length) otpBoxes[0].focus();
            });
        })();
    </script>
</body>
</html>
