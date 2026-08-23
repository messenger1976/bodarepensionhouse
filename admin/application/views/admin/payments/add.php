<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Record Payment</h5>
        <a href="<?php echo base_url('payments'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Invoice (optional)</label>
            <select name="invoice_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($invoices as $inv): ?>
                <option value="<?php echo $inv->id; ?>" <?php echo set_select('invoice_id', $inv->id, (isset($prefill_invoice_id) && $prefill_invoice_id == $inv->id)); ?>>
                    <?php echo htmlspecialchars($inv->invoice_number . ' - ' . $inv->guest_name . ' (Bal: ₱' . number_format($inv->balance_due, 2) . ')'); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Booking (optional)</label>
            <select name="booking_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($bookings as $b): ?>
                <option value="<?php echo $b->id; ?>" <?php echo set_select('booking_id', $b->id, (isset($prefill_booking_id) && $prefill_booking_id == $b->id)); ?>>
                    <?php echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount (₱) *</label>
            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required value="<?php echo set_value('amount', isset($prefill_amount) ? $prefill_amount : ''); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" class="form-select" required>
                <?php foreach (array('cash','card','gcash','bank_transfer') as $m): ?>
                <option value="<?php echo $m; ?>" <?php echo set_select('payment_method', $m, $m === 'cash'); ?>><?php echo ucfirst(str_replace('_', ' ', $m)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Status *</label>
            <select name="payment_status" class="form-select" required>
                <?php foreach (array('paid','pending','refunded','failed') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('payment_status', $s, $s === 'paid'); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Date</label>
            <input type="datetime-local" name="payment_date" class="form-control" value="<?php echo set_value('payment_date', date('Y-m-d\TH:i')); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Reference Number</label>
            <input type="text" name="reference_number" class="form-control" value="<?php echo set_value('reference_number'); ?>" placeholder="GCash ref, check #, etc.">
        </div>
        <div class="col-md-4">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" value="<?php echo set_value('transaction_id'); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?php echo set_value('notes'); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Payment</button>
        </div>
    </form>
</div>
