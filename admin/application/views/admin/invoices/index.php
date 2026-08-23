<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Room Billing Invoices</h5>
        <?php if (!empty($can_add)): ?>
        <a href="<?php echo base_url('invoices/add'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Invoice
        </a>
        <?php endif; ?>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $this->session->flashdata('error'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="<?php echo base_url('invoices'); ?>" class="btn btn-sm <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <?php foreach (array('draft','issued','partial','paid','overdue','void') as $st): ?>
            <a href="<?php echo base_url('invoices?status=' . $st); ?>" class="btn btn-sm <?php echo ($filter_status === $st) ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo ucfirst($st); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Guest</th>
                    <th>Booking / Event</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($inv->invoice_number); ?></strong></td>
                    <td><?php echo htmlspecialchars($inv->guest_name); ?></td>
                    <td>
                        <?php if ($inv->booking_number): ?>
                            <span class="badge bg-info"><?php echo htmlspecialchars($inv->booking_number); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($inv->event_name)): ?>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($inv->event_name); ?></span>
                        <?php endif; ?>
                        <?php if (!$inv->booking_number && empty($inv->event_name)): ?>—<?php endif; ?>
                    </td>
                    <td>₱<?php echo number_format($inv->total_amount, 2); ?></td>
                    <td>₱<?php echo number_format($inv->amount_paid, 2); ?></td>
                    <td><strong>₱<?php echo number_format($inv->balance_due, 2); ?></strong></td>
                    <td>
                        <?php
                        $badge = 'secondary';
                        if ($inv->status === 'paid') $badge = 'success';
                        elseif ($inv->status === 'partial') $badge = 'warning';
                        elseif ($inv->status === 'overdue') $badge = 'danger';
                        elseif ($inv->status === 'issued') $badge = 'primary';
                        elseif ($inv->status === 'void') $badge = 'dark';
                        ?>
                        <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($inv->status); ?></span>
                    </td>
                    <td><?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></td>
                    <td>
                        <a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                        <?php if (!empty($can_edit)): ?>
                        <a href="<?php echo base_url('invoices/edit/' . $inv->id); ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <a href="<?php echo base_url('invoices/print/' . $inv->id); ?>" class="btn btn-sm btn-secondary" target="_blank" title="Print"><i class="bi bi-printer"></i></a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9" class="text-center text-muted">No invoices found. Run <code>admin/sql/create_billing_module.sql</code> if this is a new install.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
