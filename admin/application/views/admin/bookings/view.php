<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-eye"></i> Booking Details</h5>
        <div>
            <a href="<?php echo base_url('bookings'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <?php if (isset($can_manage) && $can_manage): ?>
            <a href="<?php echo base_url('bookings/edit/' . $booking->id); ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
        </div>
    </div>
    
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
                        if ($booking->status == 'cancelled') $badge_class = 'danger';
                        if ($booking->status == 'pending') $badge_class = 'warning';
                        ?>
                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($booking->status); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td><strong>₱<?php echo number_format($booking->total_amount, 2); ?></strong></td>
                </tr>
            </table>
        </div>
    </div>
    
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
                                    if ($item->status == 'cancelled') $badge_class = 'danger';
                                    if ($item->status == 'pending') $badge_class = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($item->status); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
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

