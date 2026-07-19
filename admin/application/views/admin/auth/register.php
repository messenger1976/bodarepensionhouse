<?php
$header_icon = 'bi-person-plus';
$header_title = 'Create Account';
$this->load->view('admin/auth/_header', array('title' => $title, 'header_icon' => $header_icon, 'header_title' => $header_title));
?>
            <p class="text-muted" style="font-size: 0.92rem;">
                Fill in your details below. We'll email you an activation link, and your account will be activated with the Staff role.
            </p>
            <?php echo form_open('register', array('autocomplete' => 'off')); ?>
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
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            <?php echo form_close(); ?>
            <div class="auth-links">
                Already have an account? <a href="<?php echo site_url('login'); ?>">Login</a>
            </div>
<?php $this->load->view('admin/auth/_footer'); ?>
