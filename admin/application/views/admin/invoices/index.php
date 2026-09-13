<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Room Billing Invoices</h5>
        <?php if (!empty($can_add) || !empty($can_delete)): ?>
        <div class="d-flex gap-2">
            <?php if (!empty($can_delete)): ?>
            <button type="submit" form="invoices-batch-form" id="batch-delete-invoices-btn" class="btn btn-danger" disabled>
                <i class="bi bi-trash"></i> <span>Delete Selected<span data-bd-count></span></span>
            </button>
            <?php endif; ?>
            <?php if (!empty($can_add)): ?>
            <a href="<?php echo base_url('invoices/add'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Invoice
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $this->session->flashdata('error'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="btn-group mob-filter-chips" role="group">
            <a href="<?php echo base_url('invoices'); ?>" class="btn btn-sm <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <?php foreach (array('draft','issued','partial','paid','overdue','void') as $st): ?>
            <a href="<?php echo base_url('invoices?status=' . $st); ?>" class="btn btn-sm <?php echo ($filter_status === $st) ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo ucfirst($st); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="invoices-batch-form" method="post" action="<?php echo base_url('invoices/batch_delete'); ?>">
    <div class="table-responsive mob-desktop-table">
        <table class="table table-hover">
            <thead>
                <tr>
                    <?php if (!empty($can_delete)): ?>
                    <th style="width: 42px;">
                        <input type="checkbox" class="form-check-input" data-bd-select-all="invoices-batch-form" title="Select all">
                    </th>
                    <?php endif; ?>
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
                    <?php if (!empty($can_delete)): ?>
                    <td>
                        <input type="checkbox" class="form-check-input invoice-select-checkbox" name="invoice_ids[]" value="<?php echo (int) $inv->id; ?>">
                    </td>
                    <?php endif; ?>
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
                        <?php if (!empty($can_delete)): ?>
                        <a href="<?php echo base_url('invoices/delete/' . $inv->id); ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete invoice <?php echo htmlspecialchars($inv->invoice_number); ?>? This cannot be undone. If it has payments, void it instead.');"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="<?php echo !empty($can_delete) ? '10' : '9'; ?>" class="text-center text-muted">No invoices found. Run <code>admin/sql/create_billing_module.sql</code> if this is a new install.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mob-card-list d-lg-none">
        <?php if (!empty($invoices) && !empty($can_delete)): ?>
        <div class="d-flex align-items-center gap-2 mb-2 px-1">
            <input type="checkbox" class="form-check-input" id="invoices-mobile-select-all" data-bd-select-all="invoices-batch-form" title="Select all">
            <label class="form-check-label small text-muted" for="invoices-mobile-select-all">Select all</label>
        </div>
        <?php endif; ?>
        <?php if (!empty($invoices)): foreach ($invoices as $inv):
            $badge = 'secondary';
            if ($inv->status === 'paid') $badge = 'success';
            elseif ($inv->status === 'partial') $badge = 'warning';
            elseif ($inv->status === 'overdue') $badge = 'danger';
            elseif ($inv->status === 'issued') $badge = 'primary';
            elseif ($inv->status === 'void') $badge = 'dark';
        ?>
        <div class="mob-list-card">
            <div class="mob-list-card-header">
                <?php if (!empty($can_delete)): ?>
                <input type="checkbox" class="form-check-input invoice-select-checkbox me-2" name="invoice_ids[]" value="<?php echo (int) $inv->id; ?>" aria-label="Select invoice">
                <?php endif; ?>
                <div class="mob-list-card-title"><?php echo htmlspecialchars($inv->invoice_number); ?></div>
                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($inv->status); ?></span>
            </div>
            <div class="mob-list-card-meta"><i class="bi bi-person"></i> <?php echo htmlspecialchars($inv->guest_name); ?></div>
            <?php if ($inv->booking_number || !empty($inv->event_name)): ?>
            <div class="mob-list-card-meta"><i class="bi bi-tag"></i>
                <?php echo $inv->booking_number ? htmlspecialchars($inv->booking_number) : ''; ?>
                <?php echo !empty($inv->event_name) ? htmlspecialchars($inv->event_name) : ''; ?>
            </div>
            <?php endif; ?>
            <div class="mob-list-card-meta"><i class="bi bi-cash"></i> Total ₱<?php echo number_format($inv->total_amount, 2); ?> · Balance <strong>₱<?php echo number_format($inv->balance_due, 2); ?></strong></div>
            <div class="mob-list-card-meta"><i class="bi bi-calendar3"></i> Due <?php echo $inv->due_date ? date('M d, Y', strtotime($inv->due_date)) : '—'; ?></div>
            <div class="mob-list-card-actions">
                <a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> View</a>
                <?php if (!empty($can_edit)): ?>
                <a href="<?php echo base_url('invoices/edit/' . $inv->id); ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
                <a href="<?php echo base_url('invoices/print/' . $inv->id); ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-printer"></i></a>
                <?php if (!empty($can_delete)): ?>
                <a href="<?php echo base_url('invoices/delete/' . $inv->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete invoice <?php echo htmlspecialchars($inv->invoice_number); ?>? This cannot be undone.');"><i class="bi bi-trash"></i> Delete</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="mob-list-card text-muted text-center">No invoices found</div>
        <?php endif; ?>
    </div>
    </form>

    <?php
    if (!empty($can_delete)) {
        $this->load->view('admin/layout/batch_delete', array(
            'bd_form_id' => 'invoices-batch-form',
            'bd_checkbox_class' => 'invoice-select-checkbox',
            'bd_button_id' => 'batch-delete-invoices-btn',
            'bd_label' => 'invoice'
        ));
    }
    ?>
</div>
