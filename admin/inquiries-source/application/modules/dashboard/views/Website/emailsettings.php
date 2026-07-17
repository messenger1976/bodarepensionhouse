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
<div class="content website">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <div class="card">
                    <div class="card-header" data-background-color="purple">
                        <h4 class="title"><em class="icon ni ni-mail"></em> Email/SMTP Settings &mdash; <?php echo $profile_labels[$profile]; ?></h4>
                        <p class="category"><?php echo $profile_descriptions[$profile]; ?></p>
                    </div>
                    <div class="card-content">
                        <div style="margin-bottom:20px;">
                            <a href="<?php echo base_url('dashboard/website/emailsettings?profile=contact'); ?>"
                               class="btn btn-sm <?php echo $profile === 'contact' ? 'btn-primary' : 'btn-default'; ?>">
                                <em class="icon ni ni-mail"></em> Contact Us Mailer
                            </a>
                            <a href="<?php echo base_url('dashboard/website/emailsettings?profile=account'); ?>"
                               class="btn btn-sm <?php echo $profile === 'account' ? 'btn-primary' : 'btn-default'; ?>">
                                <em class="icon ni ni-user"></em> Account Mailer
                            </a>
                        </div>

                        <form method="post" action="<?php echo base_url('dashboard/website/updateemailsettings'); ?>">
                            <input type="hidden" name="profile" value="<?php echo $profile; ?>">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_host">SMTP Host (*)</label>
                                        <input type="text" class="form-control form-control-lg" id="smtp_host" name="smtp_host"
                                               value="<?php echo $value('smtp_host'); ?>" placeholder="smtp.example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_port">SMTP Port (*)</label>
                                        <input type="number" min="1" max="65535" class="form-control form-control-lg" id="smtp_port"
                                               name="smtp_port" value="<?php echo $value('smtp_port', '587'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_user">SMTP Username (*)</label>
                                        <input type="text" class="form-control form-control-lg" id="smtp_user" name="smtp_user"
                                               value="<?php echo $value('smtp_user'); ?>" autocomplete="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_pass">
                                            SMTP Password <?php echo $password_is_set ? '(leave blank to keep current password)' : '(*)'; ?>
                                        </label>
                                        <div class="form-control-wrap">
                                            <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch" data-target="smtp_pass">
                                                <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                            </a>
                                            <input type="password" class="form-control form-control-lg" id="smtp_pass" name="smtp_pass"
                                                   autocomplete="new-password" <?php echo $password_is_set ? '' : 'required'; ?>>
                                        </div>
                                        <?php if ($password_is_set) { ?>
                                            <span class="form-note">An encrypted SMTP password is currently saved.</span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_crypto">Encryption</label>
                                        <select class="form-select form-control form-control-lg" id="smtp_crypto" name="smtp_crypto">
                                            <option value="tls" <?php echo $value('smtp_crypto', 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (usually port 587)</option>
                                            <option value="ssl" <?php echo $value('smtp_crypto') === 'ssl' ? 'selected' : ''; ?>>SSL (usually port 465)</option>
                                            <option value="" <?php echo $settings && $value('smtp_crypto') === '' ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="smtp_timeout">Timeout (seconds)</label>
                                        <input type="number" min="1" max="120" class="form-control form-control-lg" id="smtp_timeout"
                                               name="smtp_timeout" value="<?php echo $value('smtp_timeout', '30'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="mailtype">Email Format</label>
                                        <select class="form-select form-control form-control-lg" id="mailtype" name="mailtype">
                                            <option value="html" <?php echo $value('mailtype', 'html') === 'html' ? 'selected' : ''; ?>>HTML</option>
                                            <option value="text" <?php echo $value('mailtype') === 'text' ? 'selected' : ''; ?>>Plain text</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="from_email">From Email (*)</label>
                                        <input type="email" class="form-control form-control-lg" id="from_email" name="from_email"
                                               value="<?php echo $value('from_email'); ?>" placeholder="noreply@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="from_name">From Name (*)</label>
                                        <input type="text" class="form-control form-control-lg" id="from_name" name="from_name"
                                               value="<?php echo $value('from_name'); ?>" placeholder="BODARE &amp; COMMUNITY MPC" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           <?php echo !$settings || !empty($settings->is_active) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="is_active">Enable SMTP email sending</label>
                                </div>
                            </div>

                            <?php if ($profile === 'contact') { ?>
                                <hr class="preview-hr">
                                <h6 class="title">Inbound Email (IMAP)</h6>
                                <p class="category">Fetch guest email replies back into the Inquiries tool from the contact mailbox.</p>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="form-label" for="imap_host">IMAP Host</label>
                                            <input type="text" class="form-control form-control-lg" id="imap_host" name="imap_host"
                                                   value="<?php echo $value('imap_host', $value('smtp_host')); ?>" placeholder="mail.example.com">
                                            <span class="form-note">Leave blank to use the same host as SMTP.</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="imap_port">IMAP Port</label>
                                            <input type="number" min="1" max="65535" class="form-control form-control-lg" id="imap_port"
                                                   name="imap_port" value="<?php echo $value('imap_port', '993'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="imap_crypto">IMAP Encryption</label>
                                            <select class="form-select form-control form-control-lg" id="imap_crypto" name="imap_crypto">
                                                <option value="ssl" <?php echo $value('imap_crypto', 'ssl') === 'ssl' ? 'selected' : ''; ?>>SSL (usually port 993)</option>
                                                <option value="tls" <?php echo $value('imap_crypto') === 'tls' ? 'selected' : ''; ?>>TLS (usually port 143)</option>
                                                <option value="" <?php echo $settings && $value('imap_crypto') === '' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group" style="margin-top:28px;">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="imap_enabled" name="imap_enabled" value="1"
                                                       <?php echo !$settings || !empty($settings->imap_enabled) ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="imap_enabled">Enable inbound email fetching for inquiries</label>
                                            </div>
                                            <span class="form-note">Uses the same mailbox username/password as SMTP above. Requires PHP IMAP extension on the server.</span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="alert alert-light alert-icon">
                                <em class="icon ni ni-info"></em>
                                Use your hosting SMTP details here (for SSL, usually port <strong>465</strong>).
                                Save settings first, then send a test email below.
                                If you get a <strong>535 authentication</strong> error, the mailbox password is wrong or SMTP is disabled for that account — reset it in cPanel and save it again here.
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-lg btn-primary">
                                    <em class="icon ni ni-save"></em><span>Save Email Settings</span>
                                </button>
                            </div>
                        </form>

                        <hr class="preview-hr">

                        <h6 class="title">Send Test Email</h6>
                        <p class="category">Uses the currently saved <?php echo strtolower($profile_labels[$profile]); ?> settings (not unsaved form changes).</p>
                        <form method="post" action="<?php echo base_url('dashboard/website/testemail'); ?>" class="mt-3">
                            <input type="hidden" name="profile" value="<?php echo $profile; ?>">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label" for="test_email">Recipient Email (*)</label>
                                        <input type="email" class="form-control form-control-lg" id="test_email" name="test_email"
                                               value="<?php echo $value('from_email'); ?>" placeholder="you@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-lg btn-outline-primary btn-block" <?php echo $password_is_set ? '' : 'disabled'; ?>>
                                            <em class="icon ni ni-send"></em><span>Send Test Email</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if (!$password_is_set) { ?>
                                <span class="form-note text-danger">Save SMTP settings with a password before sending a test.</span>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
