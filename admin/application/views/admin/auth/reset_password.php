<?php
$header_icon = 'bi-shield-lock';
$header_title = 'Reset Password';
$this->load->view('admin/auth/_header', array('title' => $title, 'header_icon' => $header_icon, 'header_title' => $header_title));
?>
            <p class="text-muted" style="font-size: 0.92rem;">
                Create a new password for your admin account.
            </p>
            <?php echo form_open('reset-password/' . $token, array('autocomplete' => 'off')); ?>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control with-icon" id="password" name="password" minlength="6" autocomplete="new-password" required autofocus>
                    </div>
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control with-icon" id="confirm_password" name="confirm_password" minlength="6" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-check-circle"></i> Reset Password
                </button>
            <?php echo form_close(); ?>
            <div class="auth-links">
                <a href="<?php echo site_url('login'); ?>"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
<?php $this->load->view('admin/auth/_footer'); ?>
