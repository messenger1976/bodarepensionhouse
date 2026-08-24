<?php
$header_icon = 'bi-person-plus';
$header_title = 'Create Account';
$this->load->view('admin/auth/_header', array('title' => $title, 'header_icon' => $header_icon, 'header_title' => $header_title));

$honeypot_field = isset($honeypot_field) ? $honeypot_field : 'company_url';
$recaptcha_enabled = !empty($recaptcha_enabled) && !empty($recaptcha_site_key);
$recaptcha_action = isset($recaptcha_action) ? $recaptcha_action : 'admin_register';
?>
            <p class="text-muted" style="font-size: 0.92rem;">
                Fill in your details below. We'll email you an activation link, and your account will be activated with the Staff role.
            </p>
            <?php echo form_open('register', array('autocomplete' => 'off', 'id' => 'admin-register-form')); ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="form-control with-icon" id="name" name="name" maxlength="100" value="<?php echo set_value('name'); ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control with-icon" id="username" name="username" minlength="3" maxlength="50" value="<?php echo set_value('username'); ?>" required>
                    </div>
                    <div class="form-text">Letters, numbers, dashes and underscores only.</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control with-icon" id="email" name="email" maxlength="100" value="<?php echo set_value('email'); ?>" required>
                    </div>
                    <div class="form-text">The activation link will be sent to this address.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control with-icon" id="password" name="password" minlength="6" autocomplete="new-password" required>
                    </div>
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control with-icon" id="confirm_password" name="confirm_password" minlength="6" autocomplete="new-password" required>
                    </div>
                </div>
                <!-- Honeypot: leave empty (hidden from humans) -->
                <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="<?php echo html_escape($honeypot_field); ?>">Company URL</label>
                    <input type="text" id="<?php echo html_escape($honeypot_field); ?>" name="<?php echo html_escape($honeypot_field); ?>" value="" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
                <button type="submit" class="btn btn-login" id="admin-register-submit">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
                <?php if ($recaptcha_enabled): ?>
                <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem; text-align: center;">
                    This site is protected by reCAPTCHA and the Google
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a> and
                    <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Terms of Service</a> apply.
                </p>
                <?php endif; ?>
            <?php echo form_close(); ?>
            <div class="auth-links">
                Already have an account? <a href="<?php echo site_url('login'); ?>">Login</a>
            </div>
<?php if ($recaptcha_enabled): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo rawurlencode($recaptcha_site_key); ?>"></script>
<script>
(function () {
    var form = document.getElementById('admin-register-form');
    var tokenInput = document.getElementById('recaptcha_token');
    var submitBtn = document.getElementById('admin-register-submit');
    if (!form || !tokenInput) return;

    var siteKey = <?php echo json_encode($recaptcha_site_key); ?>;
    var action = <?php echo json_encode($recaptcha_action); ?>;
    var submitting = false;

    form.addEventListener('submit', function (e) {
        if (submitting) return;
        e.preventDefault();

        if (!window.grecaptcha || !window.grecaptcha.execute) {
            alert('CAPTCHA could not load. Please refresh the page and try again.');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        window.grecaptcha.ready(function () {
            window.grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                tokenInput.value = token || '';
                submitting = true;
                form.submit();
            }).catch(function () {
                if (submitBtn) submitBtn.disabled = false;
                alert('CAPTCHA verification failed. Please try again.');
            });
        });
    });
})();
</script>
<?php endif; ?>
<?php $this->load->view('admin/auth/_footer'); ?>
