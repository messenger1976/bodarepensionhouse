<?php
$pm = set_value('payment_method', $payment->payment_method);
$qrph_meta = array();
if (!empty($payment->notes)) {
    $decoded = json_decode($payment->notes, true);
    if (is_array($decoded)) {
        $qrph_meta = $decoded;
    }
}
$has_qrph_meta = ($payment->payment_method === 'qrph') || !empty($payment->paymongo_intent_id) || !empty($qrph_meta['qr_image_url']);
?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Payment #<?php echo $payment->id; ?></h5>
        <a href="<?php echo base_url('payments/view/' . $payment->id); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3" id="payment-form">
        <div class="col-md-6">
            <label class="form-label">Invoice</label>
            <select name="invoice_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($invoices as $inv): ?>
                <option value="<?php echo $inv->id; ?>" <?php echo set_select('invoice_id', $inv->id, $payment->invoice_id == $inv->id); ?>>
                    <?php echo htmlspecialchars($inv->invoice_number . ' - ' . $inv->guest_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Booking</label>
            <select name="booking_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($bookings as $b): ?>
                <option value="<?php echo $b->id; ?>" <?php echo set_select('booking_id', $b->id, $payment->booking_id == $b->id); ?>>
                    <?php echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount (₱) *</label>
            <input type="number" name="amount" class="form-control" step="0.01" required value="<?php echo set_value('amount', $payment->amount); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <?php foreach (array('cash','card','gcash','qrph','bank_transfer') as $m): ?>
                <option value="<?php echo $m; ?>" <?php echo set_select('payment_method', $m, $payment->payment_method === $m); ?>><?php echo ucfirst(str_replace('_', ' ', $m)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 method-standard-field" id="field-payment-status">
            <label class="form-label">Payment Status *</label>
            <select name="payment_status" id="payment_status" class="form-select" required>
                <?php foreach (array('paid','pending','refunded','failed') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('payment_status', $s, $payment->payment_status === $s); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 method-standard-field" id="field-payment-date">
            <label class="form-label">Payment Date</label>
            <input type="datetime-local" name="payment_date" class="form-control" value="<?php echo set_value('payment_date', $payment->payment_date ? date('Y-m-d\TH:i', strtotime($payment->payment_date)) : ''); ?>">
        </div>
        <div class="col-md-4 method-ref-field" id="field-reference">
            <label class="form-label" id="label-reference">Reference Number</label>
            <input type="text" name="reference_number" id="reference_number" class="form-control" value="<?php echo set_value('reference_number', $payment->reference_number); ?>">
        </div>
        <div class="col-md-4 method-ref-field" id="field-transaction">
            <label class="form-label" id="label-transaction">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="<?php echo set_value('transaction_id', $payment->transaction_id); ?>">
        </div>

        <div class="col-12 method-panel d-none" id="panel-card">
            <div class="border rounded p-3 bg-light">
                <h6 class="mb-3">Card details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Card last 4 digits *</label>
                        <input type="text" name="card_last4" id="card_last4" class="form-control" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" autocomplete="off" value="<?php echo set_value('card_last4', isset($payment->card_last4) ? $payment->card_last4 : ''); ?>" placeholder="1234">
                        <small class="form-text text-muted">Enter last 4 only. Do not enter the full card number or CVV.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry (MM/YY) *</label>
                        <input type="text" name="card_exp" id="card_exp" class="form-control" maxlength="5" placeholder="MM/YY" value="<?php echo set_value('card_exp', isset($payment->card_exp) ? $payment->card_exp : ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 method-panel d-none" id="panel-bank">
            <div class="border rounded p-3 bg-light">
                <h6 class="mb-3">Bank transfer details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bank name *</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-control" value="<?php echo set_value('bank_name', isset($payment->bank_name) ? $payment->bank_name : ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account name *</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" value="<?php echo set_value('bank_account_name', isset($payment->bank_account_name) ? $payment->bank_account_name : ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account number *</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="<?php echo set_value('bank_account_number', isset($payment->bank_account_number) ? $payment->bank_account_number : ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transfer date *</label>
                        <input type="date" name="bank_transfer_date" id="bank_transfer_date" class="form-control" value="<?php echo set_value('bank_transfer_date', !empty($payment->bank_transfer_date) ? $payment->bank_transfer_date : ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reference # *</label>
                        <input type="text" name="bank_reference" id="bank_reference" class="form-control" value="<?php echo set_value('bank_reference', $payment->reference_number); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 method-panel d-none" id="panel-qrph">
            <div class="alert alert-secondary mb-0">
                <strong>QRPH / PayMongo</strong>
                <?php if ($has_qrph_meta): ?>
                    <ul class="mb-0 mt-2">
                        <?php if (!empty($payment->paymongo_intent_id)): ?>
                        <li>Intent: <code><?php echo htmlspecialchars($payment->paymongo_intent_id); ?></code></li>
                        <?php endif; ?>
                        <?php if (!empty($payment->qrph_expires_at)): ?>
                        <li>QR expires: <?php echo date('M d, Y h:i A', strtotime($payment->qrph_expires_at)); ?></li>
                        <?php elseif (!empty($qrph_meta['expires_at'])): ?>
                        <li>QR expires: <?php echo htmlspecialchars($qrph_meta['expires_at']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($qrph_meta['qr_image_url'])): ?>
                        <li class="mt-2"><img src="<?php echo htmlspecialchars($qrph_meta['qr_image_url']); ?>" alt="QRPH" style="max-width:160px;height:auto;"></li>
                        <?php endif; ?>
                    </ul>
                    <p class="mb-0 mt-2 text-muted small">Regenerating QRPH is done from the guest portal or by creating a new QRPH payment from Record Payment.</p>
                <?php else: ?>
                    <p class="mb-0 mt-1">No PayMongo QR metadata on this payment. Use Record Payment with method QRPH to generate and email a new code.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?php echo set_value('notes', $payment->notes); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Payment</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var methodSelect = document.getElementById('payment_method');
    var labelRef = document.getElementById('label-reference');
    var labelTxn = document.getElementById('label-transaction');
    var refInput = document.getElementById('reference_number');

    function setVisible(el, show) {
        if (!el) return;
        el.classList.toggle('d-none', !show);
    }

    function syncMethodPanels() {
        var method = methodSelect.value;
        var isQrph = method === 'qrph';
        var isCard = method === 'card';
        var isGcash = method === 'gcash';
        var isBank = method === 'bank_transfer';

        // Keep status/date editable for QRPH; only swap detail panels
        document.querySelectorAll('.method-ref-field').forEach(function(el) {
            setVisible(el, !isQrph && !isCard && !isBank);
        });

        setVisible(document.getElementById('panel-card'), isCard);
        setVisible(document.getElementById('panel-bank'), isBank);
        setVisible(document.getElementById('panel-qrph'), isQrph);

        if (isGcash) {
            labelRef.textContent = 'GCash Ref # *';
            labelTxn.textContent = 'Transaction ID *';
            refInput.required = true;
            document.getElementById('transaction_id').required = true;
        } else {
            labelRef.textContent = 'Reference Number';
            labelTxn.textContent = 'Transaction ID';
            refInput.required = false;
            document.getElementById('transaction_id').required = false;
        }

        document.getElementById('card_last4').required = isCard;
        document.getElementById('card_exp').required = isCard;

        ['bank_name', 'bank_account_name', 'bank_account_number', 'bank_transfer_date', 'bank_reference'].forEach(function(id) {
            document.getElementById(id).required = isBank;
        });
    }

    methodSelect.addEventListener('change', syncMethodPanels);
    syncMethodPanels();
});
</script>
