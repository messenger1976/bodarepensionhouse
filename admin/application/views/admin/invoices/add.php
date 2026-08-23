<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create Invoice</h5>
        <a href="<?php echo base_url('invoices'); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

    <form method="post">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Guest Name *</label>
                <input type="text" name="guest_name" class="form-control" required value="<?php echo set_value('guest_name', isset($prefill['guest_name']) ? $prefill['guest_name'] : ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Guest Email</label>
                <input type="email" name="guest_email" class="form-control" value="<?php echo set_value('guest_email', isset($prefill['guest_email']) ? $prefill['guest_email'] : ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Guest Phone</label>
                <input type="text" name="guest_phone" class="form-control" value="<?php echo set_value('guest_phone', isset($prefill['guest_phone']) ? $prefill['guest_phone'] : ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Link to Booking</label>
                <select name="booking_id" class="form-select">
                    <option value="">— None —</option>
                    <?php foreach ($bookings as $b): ?>
                    <option value="<?php echo $b->id; ?>" <?php echo set_select('booking_id', $b->id, (isset($prefill['booking_id']) && $prefill['booking_id'] == $b->id)); ?>>
                        <?php echo htmlspecialchars(($b->booking_number ?: 'BK' . $b->id) . ' - ' . $b->guest_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Link to Event</label>
                <select name="event_id" class="form-select">
                    <option value="">— None —</option>
                    <?php foreach ($events as $ev): ?>
                    <option value="<?php echo $ev->id; ?>" <?php echo set_select('event_id', $ev->id); ?>>
                        <?php echo htmlspecialchars(($ev->event_number ?: 'EV' . $ev->id) . ' - ' . $ev->event_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tax Rate (%)</label>
                <input type="number" name="tax_rate" class="form-control" step="0.01" value="<?php echo set_value('tax_rate', $rates['tax_rate']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Service Charge (%)</label>
                <input type="number" name="service_charge_rate" class="form-control" step="0.01" value="<?php echo set_value('service_charge_rate', $rates['service_charge_rate']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Discount (₱)</label>
                <input type="number" name="discount_amount" class="form-control" step="0.01" value="<?php echo set_value('discount_amount', '0'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo set_value('due_date', date('Y-m-d', strtotime('+7 days'))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?php echo set_value('notes'); ?></textarea>
            </div>
        </div>

        <h6 class="mb-3">Line Items</h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered" id="line-items-table">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th width="100">Qty</th>
                        <th width="140">Unit Price</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $prefill_items = isset($prefill_items) ? $prefill_items : array(array('item_type' => 'room', 'description' => '', 'quantity' => 1, 'unit_price' => 0));
                    if (empty($prefill_items)) {
                        $prefill_items = array(array('item_type' => 'room', 'description' => '', 'quantity' => 1, 'unit_price' => 0));
                    }
                    foreach ($prefill_items as $item):
                    ?>
                    <tr class="line-item-row">
                        <td>
                            <select name="item_type[]" class="form-select form-select-sm">
                                <?php foreach (array('room','event','extra_service','minibar','laundry','damage','food','other') as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo (isset($item['item_type']) && $item['item_type'] === $t) ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $t)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="item_description[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars(isset($item['description']) ? $item['description'] : ''); ?>"></td>
                        <td><input type="number" name="item_quantity[]" class="form-control form-control-sm" step="0.01" min="0.01" value="<?php echo isset($item['quantity']) ? $item['quantity'] : 1; ?>"></td>
                        <td><input type="number" name="item_unit_price[]" class="form-control form-control-sm" step="0.01" min="0" value="<?php echo isset($item['unit_price']) ? $item['unit_price'] : 0; ?>"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-line-item">&times;</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-line-item"><i class="bi bi-plus"></i> Add Line Item</button>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="issue_now" value="1" id="issue_now">
            <label class="form-check-label" for="issue_now">Issue invoice immediately after creation</label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Invoice</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-line-item').addEventListener('click', function() {
        var tbody = document.querySelector('#line-items-table tbody');
        var row = tbody.querySelector('.line-item-row').cloneNode(true);
        row.querySelectorAll('input').forEach(function(el) { el.value = el.name.indexOf('quantity') >= 0 ? '1' : (el.name.indexOf('price') >= 0 ? '0' : ''); });
        tbody.appendChild(row);
    });
    document.getElementById('line-items-table').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-line-item')) {
            var rows = document.querySelectorAll('.line-item-row');
            if (rows.length > 1) e.target.closest('tr').remove();
        }
    });
});
</script>
