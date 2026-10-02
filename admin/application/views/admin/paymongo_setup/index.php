<?php $ro = $can_manage ? '' : 'disabled'; ?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-wallet2"></i> PayMongo Setup</h5>
        <div>
            <span class="badge <?php echo $paymongo_enabled ? 'bg-success' : 'bg-secondary'; ?>">
                <?php echo $paymongo_enabled ? 'Enabled' : 'Disabled'; ?>
            </span>
            <?php if ($key_mode === 'live'): ?>
                <span class="badge bg-danger">Live mode</span>
            <?php elseif ($key_mode === 'test'): ?>
                <span class="badge bg-info text-dark">Test mode</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">No secret key</span>
            <?php endif; ?>
        </div>
    </div>

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

    <?php if (!$can_manage): ?>
        <div class="alert alert-info">You can view this page but not change it. Ask a Super Admin for the <strong>Manage PayMongo Setup</strong> permission.</div>
    <?php endif; ?>

    <?php if ($paymongo_enabled && !$has_webhook_secret): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            No webhook secret is set, so webhook calls are accepted without a signature check. Add the signing secret from your PayMongo webhook.
        </div>
    <?php endif; ?>

    <?php echo form_open('paymongo_setup/update', array('autocomplete' => 'off')); ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-wallet2"></i> PayMongo (QR Ph / Card)</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">When guests choose <strong>GCash / QR Ph</strong> at checkout, a dynamic QR Ph code is shown on the thank-you page and again under My Invoices. From <strong>Record Payment</strong>, admins can also email a <strong>QR Ph</strong> code or a <strong>Card</strong> Hosted Checkout link. Guests never enter full card numbers in the admin panel.</p>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="paymongo_enabled" name="paymongo_enabled" value="1"
                            <?php echo $paymongo_enabled ? 'checked' : ''; ?> <?php echo $ro; ?>>
                        <label class="form-check-label" for="paymongo_enabled">
                            Enable PayMongo QR Ph payments
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="paymongo_secret_key" class="form-label">Secret Key</label>
                    <input type="password" class="form-control" id="paymongo_secret_key" name="paymongo_secret_key" autocomplete="new-password" value=""
                        placeholder="<?php echo $has_secret_key ? 'Saved - leave blank to keep' : 'sk_test_... or sk_live_...'; ?>" <?php echo $ro; ?>>
                    <small class="form-text text-muted">From PayMongo Dashboard → Developers. Never share this key. Hosting must allow outbound HTTPS to api.paymongo.com.</small>
                    <?php if ($can_manage && $has_secret_key): ?>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="clear_paymongo_secret_key" name="clear_paymongo_secret_key" value="1">
                        <label class="form-check-label small" for="clear_paymongo_secret_key">Clear saved secret key</label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="paymongo_public_key" class="form-label">Public Key (optional)</label>
                    <input type="text" class="form-control" id="paymongo_public_key" name="paymongo_public_key" autocomplete="off"
                        value="<?php echo htmlspecialchars((string) $paymongo_public_key); ?>"
                        placeholder="pk_test_... or pk_live_..." <?php echo $ro; ?>>
                </div>
                <div class="mb-3">
                    <label for="paymongo_webhook_secret" class="form-label">Webhook Secret (optional)</label>
                    <input type="password" class="form-control" id="paymongo_webhook_secret" name="paymongo_webhook_secret" autocomplete="new-password" value=""
                        placeholder="<?php echo $has_webhook_secret ? 'Saved - leave blank to keep' : 'Webhook signing secret'; ?>" <?php echo $ro; ?>>
                    <?php if ($can_manage && $has_webhook_secret): ?>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="clear_paymongo_webhook_secret" name="clear_paymongo_webhook_secret" value="1">
                        <label class="form-check-label small" for="clear_paymongo_webhook_secret">Clear saved webhook secret</label>
                    </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">
                        Webhook URL: <code><?php echo rtrim(base_url(), '/'); ?>/api/payment/webhook</code><br>
                        Subscribe to <code>payment.paid</code> and <code>checkout_session.payment.paid</code> (for card Hosted Checkout). Optionally <code>qrph.expired</code>. Enable <strong>QR Ph</strong> and <strong>Cards</strong> in your PayMongo payment methods.
                    </small>
                </div>
                <div class="mb-0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="paymongo_confirm_on_paid" name="paymongo_confirm_on_paid" value="1"
                            <?php echo $paymongo_confirm_on_paid ? 'checked' : ''; ?> <?php echo $ro; ?>>
                        <label class="form-check-label" for="paymongo_confirm_on_paid">
                            Auto-confirm booking when QR Ph payment succeeds
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($can_manage): ?>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('dashboard'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save PayMongo Settings
            </button>
        </div>
        <?php endif; ?>
    <?php echo form_close(); ?>
</div>
