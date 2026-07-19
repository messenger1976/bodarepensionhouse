<?php
$header_icon = 'bi-key';
$header_title = 'Forgot Password';
$this->load->view('admin/auth/_header', array('title' => $title, 'header_icon' => $header_icon, 'header_title' => $header_title));
?>
            <p class="text-muted" style="font-size: 0.92rem;">
                Enter the email address linked to your admin account and we'll send you a link to reset your password.
            </p>
            <?php echo form_open('forgot-password', array('autocomplete' => 'off')); ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control with-icon" id="email" name="email" value="<?php echo set_value('email'); ?>" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-send"></i> Send Reset Link
                </button>
            <?php echo form_close(); ?>
            <div class="auth-links">
                <a href="<?php echo site_url('login'); ?>"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
<?php $this->load->view('admin/auth/_footer'); ?>
