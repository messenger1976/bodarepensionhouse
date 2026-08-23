<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Payment #<?php echo $payment->id; ?></h5>
        <a href="<?php echo base_url('payments/view/' . $payment->id); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

    <form method="post" class="row g-3">
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
            <select name="payment_method" class="form-select" required>
                <?php foreach (array('cash','card','gcash','bank_transfer') as $m): ?>
                <option value="<?php echo $m; ?>" <?php echo set_select('payment_method', $m, $payment->payment_method === $m); ?>><?php echo ucfirst(str_replace('_', ' ', $m)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Status *</label>
            <select name="payment_status" class="form-select" required>
                <?php foreach (array('paid','pending','refunded','failed') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo set_select('payment_status', $s, $payment->payment_status === $s); ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Date</label>
            <input type="datetime-local" name="payment_date" class="form-control" value="<?php echo set_value('payment_date', $payment->payment_date ? date('Y-m-d\TH:i', strtotime($payment->payment_date)) : ''); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Reference Number</label>
            <input type="text" name="reference_number" class="form-control" value="<?php echo set_value('reference_number', $payment->reference_number); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" value="<?php echo set_value('transaction_id', $payment->transaction_id); ?>">
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
