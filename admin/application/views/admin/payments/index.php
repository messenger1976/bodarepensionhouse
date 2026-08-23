<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Payment Records</h5>
        <?php if (!empty($can_add)): ?>
        <a href="<?php echo base_url('payments/add'); ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Record Payment</a>
        <?php endif; ?>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Guest / Booking</th>
                    <th>Invoice</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): foreach ($payments as $p): ?>
                <tr>
                    <td>#<?php echo $p->id; ?></td>
                    <td><?php echo $p->payment_date ? date('M d, Y', strtotime($p->payment_date)) : '—'; ?></td>
                    <td>
                        <?php echo htmlspecialchars($p->guest_name ?: '—'); ?>
                        <?php if ($p->booking_number): ?><br><small class="text-muted"><?php echo htmlspecialchars($p->booking_number); ?></small><?php endif; ?>
                    </td>
                    <td><?php echo $p->invoice_number ? htmlspecialchars($p->invoice_number) : '—'; ?></td>
                    <td><?php echo ucfirst($p->payment_method); ?></td>
                    <td><span class="badge bg-<?php echo $p->payment_status === 'paid' ? 'success' : ($p->payment_status === 'failed' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($p->payment_status); ?></span></td>
                    <td class="text-end"><strong>₱<?php echo number_format($p->amount, 2); ?></strong></td>
                    <td>
                        <a href="<?php echo base_url('payments/view/' . $p->id); ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                        <?php if (!empty($can_edit)): ?>
                        <a href="<?php echo base_url('payments/edit/' . $p->id); ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="8" class="text-center text-muted">No payments recorded yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
