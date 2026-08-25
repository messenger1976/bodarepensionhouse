<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-eye"></i> Booking Details</h5>
        <div>
            <a href="<?php echo base_url('bookings'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <?php if (!empty($can_create_invoice)): ?>
                <?php if (!empty($payment_status['primary_invoice_id'])): ?>
                <a href="<?php echo base_url('invoices/view/' . $payment_status['primary_invoice_id']); ?>" class="btn btn-outline-success">
                    <i class="bi bi-receipt"></i> View Invoice
                </a>
                <a href="<?php echo base_url('invoices/from_booking/' . $booking->id . '?force=1'); ?>" class="btn btn-outline-secondary" onclick="return confirm('This booking already has an invoice. Create another one anyway?');">
                    <i class="bi bi-plus-circle"></i> New Invoice
                </a>
                <?php else: ?>
                <a href="<?php echo base_url('invoices/from_booking/' . $booking->id); ?>" class="btn btn-success">
                    <i class="bi bi-receipt"></i> Create Invoice
                </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($can_add_payment) && !empty($payment_status['primary_invoice_id']) && $payment_status['label'] !== 'paid'): ?>
            <a href="<?php echo base_url('payments/add?invoice_id=' . $payment_status['primary_invoice_id']); ?>" class="btn btn-primary">
                <i class="bi bi-cash-coin"></i> Record Payment
            </a>
            <?php endif; ?>
            <?php if (isset($can_edit) && $can_edit): ?>
                <?php if ($booking->status === 'confirmed'): ?>
                <a href="<?php echo base_url('bookings/check_in/' . $booking->id); ?>" class="btn btn-success" onclick="return confirm('Check in this guest now?');">
                    <i class="bi bi-box-arrow-in-right"></i> Check In
                </a>
                <?php endif; ?>
                <?php if ($booking->status === 'checked_in' || $booking->status === 'confirmed'): ?>
                <a href="<?php echo base_url('bookings/check_out/' . $booking->id); ?>" class="btn btn-warning" onclick="return confirm('Check out this guest and close the booking?');">
                    <i class="bi bi-box-arrow-right"></i> Check Out
                </a>
                <?php endif; ?>
            <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Guest Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th width="40%">Name:</th>
                    <td><?php echo htmlspecialchars($booking->guest_name); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($booking->guest_email); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($booking->guest_phone); ?></td>
                </tr>
                <?php if (isset($booking->guest_address) && !empty($booking->guest_address)): ?>
                <tr>
                    <th>Address:</th>
                    <td><?php echo htmlspecialchars($booking->guest_address); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($booking->guest_city) && !empty($booking->guest_city)): ?>
                <tr>
                    <th>City:</th>
                    <td><?php echo htmlspecialchars($booking->guest_city); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($booking->guest_province) && !empty($booking->guest_province)): ?>
                <tr>
                    <th>Province:</th>
                    <td><?php echo htmlspecialchars($booking->guest_province); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($booking->guest_country) && !empty($booking->guest_country)): ?>
                <tr>
                    <th>Country:</th>
                    <td><?php echo htmlspecialchars($booking->guest_country); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($booking->guest_zipcode) && !empty($booking->guest_zipcode)): ?>
                <tr>
                    <th>Zip Code:</th>
                    <td><?php echo htmlspecialchars($booking->guest_zipcode); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Booking Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th width="40%">Booking Number:</th>
                    <td>#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <th>Booking Date:</th>
                    <td><?php echo date('F d, Y', strtotime($booking->created_at)); ?></td>
                </tr>
                <tr>
                    <th>Rooms:</th>
                    <td>
                        <span class="badge bg-info">
                            <?php 
                            // Count total rooms from booking items
                            if (isset($booking_items) && !empty($booking_items)) {
                                echo count($booking_items) . ' room(s)';
                            } else {
                                echo (isset($booking->rooms) ? $booking->rooms : 1) . ' room(s)';
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Check-In:</th>
                    <td>
                        <?php echo date('F d, Y', strtotime($booking->check_in)); ?>
                        <?php if (!empty($booking->check_in_time)): ?>
                            <span class="text-muted">at <?php echo date('g:i A', strtotime($booking->check_in_time)); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Check-Out:</th>
                    <td>
                        <?php echo date('F d, Y', strtotime($booking->check_out)); ?>
                        <?php if (!empty($booking->check_out_time)): ?>
                            <span class="text-muted">at <?php echo date('g:i A', strtotime($booking->check_out_time)); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Guests:</th>
                    <td>
                        <?php 
                        // Count total guests from booking items (if available) or use booking guests
                        if (isset($booking_items) && !empty($booking_items)) {
                            // Since guests are not stored per item, use booking guests
                            echo $booking->guests . ' person(s)';
                        } else {
                            echo $booking->guests . ' person(s)';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php
                        $badge_class = 'secondary';
                        if ($booking->status == 'confirmed') $badge_class = 'success';
                        if ($booking->status == 'checked_in') $badge_class = 'info';
                        if ($booking->status == 'checked_out' || $booking->status == 'completed') $badge_class = 'primary';
                        if ($booking->status == 'cancelled') $badge_class = 'danger';
                        if ($booking->status == 'pending') $badge_class = 'warning';
                        ?>
                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Payment:</th>
                    <td>
                        <?php if (!empty($payment_status)): ?>
                            <span class="badge bg-<?php echo $payment_status['badge']; ?>"><?php echo htmlspecialchars($payment_status['display']); ?></span>
                            <?php if ($payment_status['label'] !== 'no_invoice'): ?>
                                <small class="text-muted ms-2">
                                    Paid ₱<?php echo number_format($payment_status['amount_paid'], 2); ?>
                                    · Balance ₱<?php echo number_format($payment_status['balance'], 2); ?>
                                </small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary">No Invoice</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td><strong>₱<?php echo number_format($booking->total_amount, 2); ?></strong></td>
                </tr>
                <?php
                $extra_services_list = array();
                if (!empty($booking->extra_services)) {
                    $decoded_services = json_decode($booking->extra_services, true);
                    if (is_array($decoded_services)) {
                        $extra_services_list = $decoded_services;
                    }
                }
                ?>
                <?php if (!empty($extra_services_list)): ?>
                <tr>
                    <th>Extra Services:</th>
                    <td>
                        <?php foreach ($extra_services_list as $service): ?>
                            <?php if (empty($service['name'])) continue; ?>
                            <div class="d-flex justify-content-between gap-3">
                                <span><?php echo htmlspecialchars($service['name']); ?></span>
                                <strong>₱<?php echo number_format(isset($service['cost']) ? (float) $service['cost'] : 0, 2); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (!empty($booking_guests)): ?>
    <div class="mt-4">
        <h6 class="text-muted mb-3"><i class="bi bi-people"></i> Guests Names List</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Contact No.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking_guests as $index => $guest): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($guest->full_name); ?></td>
                        <td><?php echo $guest->age !== null && $guest->age !== '' ? (int) $guest->age : '—'; ?></td>
                        <td><?php echo !empty($guest->gender) ? htmlspecialchars($guest->gender) : '—'; ?></td>
                        <td><?php echo !empty($guest->date_of_birth) ? date('M d, Y', strtotime($guest->date_of_birth)) : '—'; ?></td>
                        <td><?php echo !empty($guest->contact_no) ? htmlspecialchars($guest->contact_no) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($booking_invoices)): ?>
    <div class="mt-4">
        <h6 class="text-muted mb-3"><i class="bi bi-receipt"></i> Linked Invoices</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking_invoices as $inv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                        <td>
                            <?php
                            $inv_badge = 'secondary';
                            if ($inv->status === 'paid') $inv_badge = 'success';
                            elseif ($inv->status === 'partial') $inv_badge = 'warning';
                            elseif ($inv->status === 'issued' || $inv->status === 'overdue') $inv_badge = 'danger';
                            elseif ($inv->status === 'draft') $inv_badge = 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $inv_badge; ?>"><?php echo ucfirst($inv->status); ?></span>
                        </td>
                        <td>₱<?php echo number_format($inv->total_amount, 2); ?></td>
                        <td>₱<?php echo number_format($inv->balance_due, 2); ?></td>
                        <td><a href="<?php echo base_url('invoices/view/' . $inv->id); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($booking->notes)): ?>
        <div class="mt-4">
            <h6 class="text-muted mb-3">Notes</h6>
            <div class="alert alert-info">
                <?php echo nl2br(htmlspecialchars($booking->notes)); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($booking_items) && !empty($booking_items)): ?>
        <div class="mt-4">
            <h6 class="text-muted mb-3">Room Details (Itemized) - <?php echo count($booking_items); ?> item(s)</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="booking-items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Room Name</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Nights</th>
                            <th>Guests</th>
                            <th>Price/Night</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($booking_items as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($item->room_name); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item->check_in)); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item->check_out)); ?></td>
                                <td><?php echo $item->nights; ?></td>
                                <td><?php echo $booking->guests; ?> person(s)</td>
                                <td>₱<?php echo number_format($item->price_per_night, 2); ?></td>
                                <td><strong>₱<?php echo number_format($item->subtotal, 2); ?></strong></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    if ($item->status == 'confirmed') $badge_class = 'success';
                                    if ($item->status == 'checked_in') $badge_class = 'info';
                                    if ($item->status == 'checked_out' || $item->status == 'completed') $badge_class = 'primary';
                                    if ($item->status == 'cancelled') $badge_class = 'danger';
                                    if ($item->status == 'pending') $badge_class = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $item->status)); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php
                        $rooms_items_subtotal = 0;
                        if (!empty($booking_items)) {
                            foreach ($booking_items as $item) {
                                $rooms_items_subtotal += (float) $item->subtotal;
                            }
                        }
                        $view_extra_services = array();
                        if (!empty($booking->extra_services)) {
                            $decoded = json_decode($booking->extra_services, true);
                            if (is_array($decoded)) {
                                $view_extra_services = $decoded;
                            }
                        }
                        ?>
                        <tr>
                            <td colspan="7" class="text-end"><strong>Rooms Subtotal:</strong></td>
                            <td colspan="2"><strong>₱<?php echo number_format($rooms_items_subtotal, 2); ?></strong></td>
                        </tr>
                        <?php foreach ($view_extra_services as $service): ?>
                            <?php if (empty($service['name'])) continue; ?>
                            <tr>
                                <td colspan="7" class="text-end"><?php echo htmlspecialchars($service['name']); ?>:</td>
                                <td colspan="2">₱<?php echo number_format(isset($service['cost']) ? (float) $service['cost'] : 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <td colspan="7" class="text-end"><strong>Total Amount:</strong></td>
                            <td colspan="2"><strong>₱<?php echo number_format($booking->total_amount, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php else: ?>
        <?php if (isset($booking_items)): ?>
            <div class="mt-4">
                <div class="alert alert-warning">
                    <strong>Note:</strong> No itemized room details found for this booking. 
                    <?php if (!$this->db->table_exists('booking_items')): ?>
                        <br><small>The booking_items table may not exist. Please run the SQL migration: <code>admin/sql/create_booking_items_table.sql</code></small>
                    <?php else: ?>
                        <br><small>This booking may have been created before the itemization feature was implemented.</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

