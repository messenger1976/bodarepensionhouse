<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-receipt-cutoff"></i> Invoice <?php echo htmlspecialchars($invoice->invoice_number); ?></h5>
        <div>
            <a href="<?php echo base_url('invoices'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="<?php echo base_url('invoices/print/' . $invoice->id); ?>" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-printer"></i> Print</a>
            <?php if (!empty($can_edit) && $invoice->status !== 'void' && !empty($invoice->guest_email)): ?>
            <a href="<?php echo base_url('invoices/send_email/' . $invoice->id); ?>" class="btn btn-info" onclick="return confirm('Send this invoice to <?php echo htmlspecialchars($invoice->guest_email); ?>?');"><i class="bi bi-envelope"></i> Email Invoice</a>
            <?php endif; ?>
            <?php if (!empty($can_edit) && $invoice->status === 'draft'): ?>
            <a href="<?php echo base_url('invoices/issue/' . $invoice->id); ?>" class="btn btn-success" onclick="return confirm('Issue this invoice to the guest?');"><i class="bi bi-send"></i> Issue</a>
            <?php endif; ?>
            <?php if (!empty($can_add_payment) && $invoice->balance_due > 0 && $invoice->status !== 'void'): ?>
            <a href="<?php echo base_url('payments/add?invoice_id=' . $invoice->id); ?>" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Record Payment</a>
            <?php endif; ?>
            <?php if (!empty($can_edit)): ?>
            <a href="<?php echo base_url('invoices/edit/' . $invoice->id); ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="text-muted">Bill To</h6>
            <p class="mb-1"><strong><?php echo htmlspecialchars($invoice->guest_name); ?></strong></p>
            <?php if ($invoice->guest_email): ?><p class="mb-1"><?php echo htmlspecialchars($invoice->guest_email); ?></p><?php endif; ?>
            <?php if ($invoice->guest_phone): ?><p class="mb-0"><?php echo htmlspecialchars($invoice->guest_phone); ?></p><?php endif; ?>
        </div>
        <div class="col-md-6 text-md-end">
            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($invoice->status); ?></span></p>
            <?php if ($invoice->booking_number): ?><p class="mb-1"><strong>Booking:</strong> <?php echo htmlspecialchars($invoice->booking_number); ?></p><?php endif; ?>
            <?php if (!empty($invoice->event_name)): ?><p class="mb-1"><strong>Event:</strong> <?php echo htmlspecialchars($invoice->event_name); ?></p><?php endif; ?>
            <p class="mb-1"><strong>Due:</strong> <?php echo $invoice->due_date ? date('F d, Y', strtotime($invoice->due_date)) : '—'; ?></p>
            <p class="mb-0"><strong>Issued:</strong> <?php echo $invoice->issued_at ? date('F d, Y h:i A', strtotime($invoice->issued_at)) : 'Not yet issued'; ?></p>
            <?php if (!empty($invoice->emailed_at)): ?><p class="mb-0 mt-1 text-muted"><small>Last emailed: <?php echo date('F d, Y h:i A', strtotime($invoice->emailed_at)); ?></small></p><?php endif; ?>
        </div>
    </div>

    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): foreach ($items as $item): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $item->item_type)); ?></span></td>
                    <td><?php echo htmlspecialchars($item->description); ?></td>
                    <td class="text-end"><?php echo number_format($item->quantity, 2); ?></td>
                    <td class="text-end">₱<?php echo number_format($item->unit_price, 2); ?></td>
                    <td class="text-end">₱<?php echo number_format($item->total_price, 2); ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center text-muted">No line items</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">₱<?php echo number_format($invoice->subtotal, 2); ?></td></tr>
                <?php if ($invoice->discount_amount > 0): ?>
                <tr><td colspan="4" class="text-end">Discount</td><td class="text-end text-danger">-₱<?php echo number_format($invoice->discount_amount, 2); ?></td></tr>
                <?php endif; ?>
                <?php if ($invoice->tax_amount > 0): ?>
                <tr><td colspan="4" class="text-end">Tax (<?php echo number_format($invoice->tax_rate, 2); ?>%)</td><td class="text-end">₱<?php echo number_format($invoice->tax_amount, 2); ?></td></tr>
                <?php endif; ?>
                <?php if ($invoice->service_charge_amount > 0): ?>
                <tr><td colspan="4" class="text-end">Service Charge (<?php echo number_format($invoice->service_charge_rate, 2); ?>%)</td><td class="text-end">₱<?php echo number_format($invoice->service_charge_amount, 2); ?></td></tr>
                <?php endif; ?>
                <tr class="table-light"><td colspan="4" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>₱<?php echo number_format($invoice->total_amount, 2); ?></strong></td></tr>
                <tr><td colspan="4" class="text-end">Amount Paid</td><td class="text-end text-success">₱<?php echo number_format($invoice->amount_paid, 2); ?></td></tr>
                <tr><td colspan="4" class="text-end"><strong>Balance Due</strong></td><td class="text-end"><strong class="text-danger">₱<?php echo number_format($invoice->balance_due, 2); ?></strong></td></tr>
            </tfoot>
        </table>
    </div>

    <?php if (!empty($can_edit) && $invoice->status !== 'void'): ?>
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-plus-circle"></i> Add Additional Charge</div>
        <div class="card-body">
            <form method="post" action="<?php echo base_url('invoices/add_charge/' . $invoice->id); ?>" class="row g-3">
                <div class="col-md-3">
                    <select name="item_type" class="form-select">
                        <option value="extra_service">Extra Service</option>
                        <option value="minibar">Minibar</option>
                        <option value="laundry">Laundry</option>
                        <option value="damage">Damage Fee</option>
                        <option value="food">Food & Beverage</option>
                        <option value="room">Room Charge</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="description" class="form-control" placeholder="Description" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="quantity" class="form-control" value="1" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                    <input type="number" name="unit_price" class="form-control" placeholder="Amount" min="0" step="0.01" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <h6 class="text-muted mb-3">Payment History</h6>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): foreach ($payments as $p): ?>
                <tr>
                    <td><?php echo $p->payment_date ? date('M d, Y h:i A', strtotime($p->payment_date)) : '—'; ?></td>
                    <td><?php echo ucfirst($p->payment_method); ?></td>
                    <td><?php echo htmlspecialchars($p->reference_number ?: $p->transaction_id ?: '—'); ?></td>
                    <td><span class="badge bg-<?php echo $p->payment_status === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($p->payment_status); ?></span></td>
                    <td class="text-end">₱<?php echo number_format($p->amount, 2); ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center text-muted">No payments recorded yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($invoice->notes): ?>
    <div class="mt-3"><h6 class="text-muted">Notes</h6><p><?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p></div>
    <?php endif; ?>
</div>
