<?php
$qrph_meta = array();
if (!empty($payment->notes)) {
    $decoded = json_decode($payment->notes, true);
    if (is_array($decoded)) {
        $qrph_meta = $decoded;
    }
}
$method = $payment->payment_method;
?>
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

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <tr><th width="30%">Amount</th><td><strong>₱<?php echo number_format($payment->amount, 2); ?></strong></td></tr>
        <tr><th>Payment Date</th><td><?php echo $payment->payment_date ? date('F d, Y h:i A', strtotime($payment->payment_date)) : '—'; ?></td></tr>
        <tr><th>Method</th><td><?php echo ucfirst(str_replace('_', ' ', $payment->payment_method)); ?></td></tr>
        <tr><th>Status</th><td><span class="badge bg-<?php echo $payment->payment_status === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($payment->payment_status); ?></span></td></tr>
        <tr><th>Reference #</th><td><?php echo htmlspecialchars($payment->reference_number ?: '—'); ?></td></tr>
        <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($payment->transaction_id ?: '—'); ?></td></tr>

        <?php if ($method === 'card'): ?>
        <tr><th>Card last 4</th><td><?php echo !empty($payment->card_last4) ? '•••• ' . htmlspecialchars($payment->card_last4) : '—'; ?></td></tr>
        <tr><th>Card expiry</th><td><?php echo !empty($payment->card_exp) ? htmlspecialchars($payment->card_exp) : '—'; ?></td></tr>
        <?php endif; ?>

        <?php if ($method === 'bank_transfer'): ?>
        <tr><th>Bank name</th><td><?php echo !empty($payment->bank_name) ? htmlspecialchars($payment->bank_name) : '—'; ?></td></tr>
        <tr><th>Account name</th><td><?php echo !empty($payment->bank_account_name) ? htmlspecialchars($payment->bank_account_name) : '—'; ?></td></tr>
        <tr><th>Account number</th><td><?php echo !empty($payment->bank_account_number) ? htmlspecialchars($payment->bank_account_number) : '—'; ?></td></tr>
        <tr><th>Transfer date</th><td><?php echo !empty($payment->bank_transfer_date) ? date('F d, Y', strtotime($payment->bank_transfer_date)) : '—'; ?></td></tr>
        <?php endif; ?>

        <?php if ($method === 'qrph' || !empty($payment->paymongo_intent_id) || !empty($qrph_meta['qr_image_url'])): ?>
        <tr><th>PayMongo intent</th><td><?php echo !empty($payment->paymongo_intent_id) ? '<code>' . htmlspecialchars($payment->paymongo_intent_id) . '</code>' : '—'; ?></td></tr>
        <tr><th>QR expires</th><td><?php
            if (!empty($payment->qrph_expires_at)) {
                echo date('F d, Y h:i A', strtotime($payment->qrph_expires_at));
            } elseif (!empty($qrph_meta['expires_at'])) {
                echo htmlspecialchars($qrph_meta['expires_at']);
            } else {
                echo '—';
            }
        ?></td></tr>
        <?php if (!empty($qrph_meta['qr_image_url'])): ?>
        <tr><th>QR Ph</th><td><img src="<?php echo htmlspecialchars($qrph_meta['qr_image_url']); ?>" alt="QRPH" style="max-width:200px;height:auto;"></td></tr>
        <?php endif; ?>
        <?php endif; ?>

        <tr><th>Invoice</th><td><?php echo $payment->invoice_number ? htmlspecialchars($payment->invoice_number) : '—'; ?></td></tr>
        <tr><th>Booking</th><td><?php echo $payment->booking_number ? htmlspecialchars($payment->booking_number . ' - ' . $payment->guest_name) : '—'; ?></td></tr>
        <tr><th>Notes</th><td><?php
            if ($payment->notes && !empty($qrph_meta) && isset($qrph_meta['provider'])) {
                echo '<em class="text-muted">PayMongo metadata stored</em>';
            } elseif ($payment->notes) {
                echo nl2br(htmlspecialchars($payment->notes));
            } else {
                echo '—';
            }
        ?></td></tr>
    </table>
</div>
