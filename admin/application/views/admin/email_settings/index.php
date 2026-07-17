<?php
$settings = isset($email_settings) ? $email_settings : null;
$profile = isset($email_profile) ? $email_profile : 'contact';
$value = function ($field, $default = '') use ($settings) {
    return htmlspecialchars($settings && isset($settings->$field) ? $settings->$field : $default, ENT_QUOTES, 'UTF-8');
};
$password_is_set = $settings && !empty($settings->smtp_pass);
$profile_labels = array(
    'contact' => 'Contact Us Mailer',
    'account' => 'Account Mailer',
);
$profile_descriptions = array(
    'contact' => 'Sender used for Contact Us inquiries: guest confirmations, admin notifications, and inquiry replies.',
    'account' => 'Sender used for account emails: registration, password resets, and other system messages.',
);
?>
<div class="content-card">
    <h5 class="mb-1"><i class="bi bi-envelope-gear"></i> Email/SMTP Settings — <?php echo $profile_labels[$profile]; ?></h5>
    <p class="text-muted mb-3"><?php echo $profile_descriptions[$profile]; ?></p>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo base_url('email_settings?profile=contact'); ?>" class="btn btn-sm <?php echo $profile === 'contact' ? 'btn-primary' : 'btn-outline-primary'; ?>">Contact Us Mailer</a>
        <a href="<?php echo base_url('email_settings?profile=account'); ?>" class="btn btn-sm <?php echo $profile === 'account' ? 'btn-primary' : 'btn-outline-primary'; ?>">Account Mailer</a>
    </div>

    <form method="post" action="<?php echo base_url('email_settings/update'); ?>">
        <input type="hidden" name="profile" value="<?php echo htmlspecialchars($profile, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="smtp_host">SMTP Host *</label>
                <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo $value('smtp_host'); ?>" placeholder="smtp.example.com" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="smtp_port">SMTP Port *</label>
                <input type="number" min="1" max="65535" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo $value('smtp_port', '587'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="smtp_user">SMTP Username *</label>
                <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo $value('smtp_user'); ?>" autocomplete="username" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="smtp_pass">SMTP Password <?php echo $password_is_set ? '(leave blank to keep current)' : '*'; ?></label>
                <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" autocomplete="new-password" <?php echo $password_is_set ? '' : 'required'; ?>>
                <?php if ($password_is_set) { ?><div class="form-text">An encrypted SMTP password is currently saved.</div><?php } ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="smtp_crypto">Encryption</label>
                <select class="form-select" id="smtp_crypto" name="smtp_crypto">
                    <option value="tls" <?php echo $value('smtp_crypto', 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (usually port 587)</option>
                    <option value="ssl" <?php echo $value('smtp_crypto') === 'ssl' ? 'selected' : ''; ?>>SSL (usually port 465)</option>
                    <option value="" <?php echo $settings && $value('smtp_crypto') === '' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="smtp_timeout">Timeout (seconds)</label>
                <input type="number" min="1" max="120" class="form-control" id="smtp_timeout" name="smtp_timeout" value="<?php echo $value('smtp_timeout', '30'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="mailtype">Email Format</label>
                <select class="form-select" id="mailtype" name="mailtype">
                    <option value="html" <?php echo $value('mailtype', 'html') === 'html' ? 'selected' : ''; ?>>HTML</option>
                    <option value="text" <?php echo $value('mailtype') === 'text' ? 'selected' : ''; ?>>Plain text</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="from_email">From Email *</label>
                <input type="email" class="form-control" id="from_email" name="from_email" value="<?php echo $value('from_email'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="from_name">From Name *</label>
                <input type="text" class="form-control" id="from_name" name="from_name" value="<?php echo $value('from_name', 'BODARE Pension House'); ?>" required>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo !$settings || !empty($settings->is_active) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Enable SMTP email sending</label>
                </div>
            </div>
        </div>

        <?php if ($profile === 'contact') { ?>
        <hr class="my-4">
        <h6>Inbound Email (IMAP)</h6>
        <p class="text-muted small">Fetch guest email replies into the Inquiries tool.</p>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="imap_host">IMAP Host</label>
                <input type="text" class="form-control" id="imap_host" name="imap_host" value="<?php echo $value('imap_host', $value('smtp_host')); ?>">
                <div class="form-text">Leave blank to use the same host as SMTP.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="imap_port">IMAP Port</label>
                <input type="number" min="1" max="65535" class="form-control" id="imap_port" name="imap_port" value="<?php echo $value('imap_port', '993'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="imap_crypto">IMAP Encryption</label>
                <select class="form-select" id="imap_crypto" name="imap_crypto">
                    <option value="ssl" <?php echo $value('imap_crypto', 'ssl') === 'ssl' ? 'selected' : ''; ?>>SSL (usually port 993)</option>
                    <option value="tls" <?php echo $value('imap_crypto') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="" <?php echo $settings && $value('imap_crypto') === '' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="imap_enabled" name="imap_enabled" value="1" <?php echo !$settings || !empty($settings->imap_enabled) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="imap_enabled">Enable inbound email fetching</label>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Email Settings</button>
        </div>
    </form>

    <hr class="my-4">
    <h6>Send Test Email</h6>
    <form method="post" action="<?php echo base_url('email_settings/test'); ?>" class="row g-3 align-items-end">
        <input type="hidden" name="profile" value="<?php echo htmlspecialchars($profile, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="col-md-8">
            <label class="form-label" for="test_email">Recipient Email *</label>
            <input type="email" class="form-control" id="test_email" name="test_email" value="<?php echo $value('from_email'); ?>" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-outline-primary w-100" <?php echo $password_is_set ? '' : 'disabled'; ?>>
                <i class="bi bi-send"></i> Send Test Email
            </button>
        </div>
        <?php if (!$password_is_set) { ?>
        <div class="col-12"><span class="text-danger small">Save SMTP settings with a password before sending a test.</span></div>
        <?php } ?>
    </form>
</div>
