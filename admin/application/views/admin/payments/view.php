<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Payment #<?php echo $payment->id; ?></h5>
        <div>
            <a href="<?php echo base_url('payments'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <?php if (!empty($can_edit)): ?>
            <a href="<?php echo base_url('payments/edit/' . $payment->id); ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <?php endif; ?>
            <?php if ($payment->invoice_id): ?>
            <a href="<?php echo base_url('invoices/view/' . $payment->invoice_id); ?>" class="btn btn-primary"><i class="bi bi-receipt"></i> View Invoice</a>
            <?php endif; ?>
        </div>
    </div>

    <table class="table table-bordered">
        <tr><th width="30%">Amount</th><td><strong>₱<?php echo number_format($payment->amount, 2); ?></strong></td></tr>
        <tr><th>Payment Date</th><td><?php echo $payment->payment_date ? date('F d, Y h:i A', strtotime($payment->payment_date)) : '—'; ?></td></tr>
        <tr><th>Method</th><td><?php echo ucfirst($payment->payment_method); ?></td></tr>
        <tr><th>Status</th><td><span class="badge bg-<?php echo $payment->payment_status === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($payment->payment_status); ?></span></td></tr>
        <tr><th>Reference #</th><td><?php echo htmlspecialchars($payment->reference_number ?: '—'); ?></td></tr>
        <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($payment->transaction_id ?: '—'); ?></td></tr>
        <tr><th>Invoice</th><td><?php echo $payment->invoice_number ? htmlspecialchars($payment->invoice_number) : '—'; ?></td></tr>
        <tr><th>Booking</th><td><?php echo $payment->booking_number ? htmlspecialchars($payment->booking_number . ' - ' . $payment->guest_name) : '—'; ?></td></tr>
        <tr><th>Notes</th><td><?php echo $payment->notes ? nl2br(htmlspecialchars($payment->notes)) : '—'; ?></td></tr>
    </table>
</div>
