<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Record Payment</h5>
        <a href="<?php echo base_url('payments'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Link payments to an <strong>invoice</strong> whenever possible. This tags the booking and prevents double payment once the invoice is fully paid.
    </div>

    <form method="post" class="row g-3" id="payment-form">
        <div class="col-md-6">
            <label class="form-label">Invoice (recommended)</label>
            <select name="invoice_id" id="invoice_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($invoices as $inv): ?>
                    <?php
                    $is_paid = ($inv->status === 'paid' || ((float) $inv->balance_due <= 0 && (float) $inv->amount_paid > 0));
                    $is_void = ($inv->status === 'void');
                    $disabled = $is_paid || $is_void;
                    $label = $inv->invoice_number . ' - ' . $inv->guest_name . ' (Bal: ₱' . number_format($inv->balance_due, 2) . ')';
                    if ($is_paid) {
                        $label .= ' — PAID';
                    } elseif ($is_void) {
                        $label .= ' — VOID';
                    }
                    ?>
                <option value="<?php echo $inv->id; ?>"
                        data-booking-id="<?php echo (int) $inv->booking_id; ?>"
                        data-balance="<?php echo (float) $inv->balance_due; ?>"
                        data-paid="<?php echo $is_paid ? '1' : '0'; ?>"
                        data-void="<?php echo $is_void ? '1' : '0'; ?>"
                        <?php echo $disabled ? 'disabled' : ''; ?>
                        <?php echo set_select('invoice_id', $inv->id, (isset($prefill_invoice_id) && $prefill_invoice_id == $inv->id && !$disabled)); ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Paid and void invoices cannot receive new payments.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Booking (auto-tagged from invoice)</label>
            <select name="booking_id" id="booking_id" class="form-select">
                <option value="">— None —</option>
                <?php
                $booking_invoice_map = isset($booking_invoice_map) ? $booking_invoice_map : array();
                foreach ($bookings as $b):
                    $map = isset($booking_invoice_map[$b->id]) ? $booking_invoice_map[$b->id] : null;
                ?>
                <option value="<?php echo $b->id; ?>"
                        data-invoice-id="<?php echo $map ? (int) $map['invoice_id'] : ''; ?>"
                        data-balance="<?php echo $map ? (float) $map['balance_due'] : ''; ?>"
                        data-invoice-status="<?php echo $map ? htmlspecialchars($map['status']) : ''; ?>"
                        <?php echo set_select('booking_id', $b->id, (isset($prefill_booking_id) && $prefill_booking_id == $b->id)); ?>>
                    <?php
                    echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name);
                    if ($map) {
                        echo ' [' . strtoupper($map['status']) . ']';
                    }
                    ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted" id="booking-hint">If a booking already has an invoice, payment must go through that invoice.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount (₱) *</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required value="<?php echo set_value('amount', isset($prefill_amount) ? $prefill_amount : ''); ?>">
            <small class="form-text text-muted" id="balance-hint"></small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <?php foreach (array('cash','card','gcash','qrph','bank_transfer') as $m): ?>
                <option value="<?php echo $m; ?>" <?php echo set_select('payment_method', $m, $m === 'cash'); ?>><?php echo ucfirst(str_replace('_', ' ', $m)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 method-standard-field" id="field-payment-status">
            <label class="form-label">Payment Status *</label>
            <select name="payment_status" id="payment_status" class="form-select" required>
                <?php foreach (array('paid','pending','refunded','failed') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('payment_status', $s, $s === 'paid'); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 method-standard-field" id="field-payment-date">
            <label class="form-label">Payment Date</label>
            <input type="datetime-local" name="payment_date" class="form-control" value="<?php echo set_value('payment_date', date('Y-m-d\TH:i')); ?>">
        </div>
        <div class="col-md-4 method-ref-field" id="field-reference">
            <label class="form-label" id="label-reference">Reference Number</label>
            <input type="text" name="reference_number" id="reference_number" class="form-control" value="<?php echo set_value('reference_number'); ?>" placeholder="GCash ref, check #, etc.">
        </div>
        <div class="col-md-4 method-ref-field" id="field-transaction">
            <label class="form-label" id="label-transaction">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="<?php echo set_value('transaction_id'); ?>">
        </div>

        <!-- Card details -->
        <div class="col-12 method-panel d-none" id="panel-card">
            <div class="border rounded p-3 bg-light">
                <h6 class="mb-3">Card details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Card last 4 digits *</label>
                        <input type="text" name="card_last4" id="card_last4" class="form-control" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" autocomplete="off" value="<?php echo set_value('card_last4'); ?>" placeholder="1234">
                        <small class="form-text text-muted">Enter last 4 only. Do not enter the full card number or CVV.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry (MM/YY) *</label>
                        <input type="text" name="card_exp" id="card_exp" class="form-control" maxlength="5" placeholder="MM/YY" value="<?php echo set_value('card_exp'); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank transfer details -->
        <div class="col-12 method-panel d-none" id="panel-bank">
            <div class="border rounded p-3 bg-light">
                <h6 class="mb-3">Bank transfer details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bank name *</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-control" value="<?php echo set_value('bank_name'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account name *</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" value="<?php echo set_value('bank_account_name'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account number *</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="<?php echo set_value('bank_account_number'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transfer date *</label>
                        <input type="date" name="bank_transfer_date" id="bank_transfer_date" class="form-control" value="<?php echo set_value('bank_transfer_date'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reference # *</label>
                        <input type="text" name="bank_reference" id="bank_reference" class="form-control" value="<?php echo set_value('bank_reference', set_value('reference_number')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- QRPH info -->
        <div class="col-12 method-panel d-none" id="panel-qrph">
            <div class="alert alert-warning mb-0">
                <i class="bi bi-qr-code"></i>
                <strong>Generate QRPH &amp; email guest.</strong>
                A pending PayMongo QR Ph payment will be created and emailed to the guest.
                Payment status stays <em>pending</em> until the guest pays (or the webhook confirms).
                Link an invoice or booking with a guest email address.
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?php echo set_value('notes'); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary" id="submit-payment-btn">
                <i class="bi bi-save"></i> <span id="submit-payment-label">Save Payment</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var invoiceSelect = document.getElementById('invoice_id');
    var bookingSelect = document.getElementById('booking_id');
    var amountInput = document.getElementById('amount');
    var balanceHint = document.getElementById('balance-hint');
    var methodSelect = document.getElementById('payment_method');
    var labelRef = document.getElementById('label-reference');
    var labelTxn = document.getElementById('label-transaction');
    var refInput = document.getElementById('reference_number');
    var submitLabel = document.getElementById('submit-payment-label');
    var statusSelect = document.getElementById('payment_status');

    function updateBalanceHint(balance) {
        if (balance === '' || balance === null || isNaN(balance)) {
            balanceHint.textContent = '';
            return;
        }
        balanceHint.textContent = 'Remaining balance: ₱' + Number(balance).toFixed(2);
        if (!amountInput.value || Number(amountInput.value) <= 0) {
            amountInput.value = Number(balance).toFixed(2);
        }
    }

    function setVisible(el, show) {
        if (!el) return;
        el.classList.toggle('d-none', !show);
        var inputs = el.querySelectorAll('input, select, textarea');
        inputs.forEach(function(inp) {
            if (inp.name === 'payment_status') {
                inp.disabled = !show;
                return;
            }
            if (!show) {
                inp.removeAttribute('required');
            }
        });
    }

    function syncMethodPanels() {
        var method = methodSelect.value;
        var isQrph = method === 'qrph';
        var isCard = method === 'card';
        var isGcash = method === 'gcash';
        var isBank = method === 'bank_transfer';

        document.querySelectorAll('.method-standard-field').forEach(function(el) {
            setVisible(el, !isQrph);
        });
        document.querySelectorAll('.method-ref-field').forEach(function(el) {
            setVisible(el, !isQrph && !isCard && !isBank);
        });

        setVisible(document.getElementById('panel-card'), isCard);
        setVisible(document.getElementById('panel-bank'), isBank);
        setVisible(document.getElementById('panel-qrph'), isQrph);

        if (isGcash) {
            labelRef.textContent = 'GCash Ref # *';
            labelTxn.textContent = 'Transaction ID *';
            refInput.placeholder = 'GCash reference number';
            refInput.required = true;
            document.getElementById('transaction_id').required = true;
        } else {
            labelRef.textContent = 'Reference Number';
            labelTxn.textContent = 'Transaction ID';
            refInput.placeholder = 'GCash ref, check #, etc.';
            refInput.required = false;
            document.getElementById('transaction_id').required = false;
        }

        if (isCard) {
            document.getElementById('card_last4').required = true;
            document.getElementById('card_exp').required = true;
        } else {
            document.getElementById('card_last4').required = false;
            document.getElementById('card_exp').required = false;
        }

        if (isBank) {
            ['bank_name', 'bank_account_name', 'bank_account_number', 'bank_transfer_date', 'bank_reference'].forEach(function(id) {
                document.getElementById(id).required = true;
            });
        } else {
            ['bank_name', 'bank_account_name', 'bank_account_number', 'bank_transfer_date', 'bank_reference'].forEach(function(id) {
                document.getElementById(id).required = false;
            });
        }

        if (isQrph) {
            submitLabel.textContent = 'Generate QRPH & Email Guest';
            if (statusSelect) {
                statusSelect.value = 'pending';
                statusSelect.disabled = true;
                statusSelect.required = false;
            }
        } else {
            submitLabel.textContent = 'Save Payment';
            if (statusSelect) {
                statusSelect.disabled = false;
                statusSelect.required = true;
            }
        }
    }

    invoiceSelect.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            balanceHint.textContent = '';
            return;
        }
        var bookingId = opt.getAttribute('data-booking-id');
        if (bookingId) {
            bookingSelect.value = bookingId;
        }
        updateBalanceHint(opt.getAttribute('data-balance'));
    });

    bookingSelect.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }
        var invoiceId = opt.getAttribute('data-invoice-id');
        var status = opt.getAttribute('data-invoice-status');
        if (invoiceId) {
            if (status === 'paid') {
                alert('This booking\'s invoice is already paid. No additional payment needed.');
                this.value = '';
                return;
            }
            invoiceSelect.value = invoiceId;
            var invOpt = invoiceSelect.options[invoiceSelect.selectedIndex];
            if (invOpt) {
                updateBalanceHint(invOpt.getAttribute('data-balance'));
            }
        }
    });

    methodSelect.addEventListener('change', syncMethodPanels);
    syncMethodPanels();

    if (invoiceSelect.value) {
        invoiceSelect.dispatchEvent(new Event('change'));
    }
});
</script>
