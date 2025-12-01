<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Customer/Guest Details</h5>
        <div>
            <?php if (isset($can_edit) && $can_edit): ?>
            <a href="<?php echo base_url('customers/edit/' . $customer->id); ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo base_url('customers'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Personal Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">ID:</th>
                            <td><?php echo $customer->id; ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><strong><?php echo htmlspecialchars($customer->first_name . ' ' . $customer->last_name); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($customer->email); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($customer->phone ? $customer->phone : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td><?php echo $customer->date_of_birth ? date('M d, Y', strtotime($customer->date_of_birth)) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td><?php echo $customer->gender ? ucfirst($customer->gender) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Nationality:</th>
                            <td><?php echo htmlspecialchars($customer->nationality ? $customer->nationality : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?php echo $customer->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($customer->status); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Address Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Address:</th>
                            <td><?php echo htmlspecialchars($customer->address ? $customer->address : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td><?php echo htmlspecialchars($customer->city ? $customer->city : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Province:</th>
                            <td><?php echo htmlspecialchars($customer->province ? $customer->province : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Postal Code:</th>
                            <td><?php echo htmlspecialchars($customer->postal_code ? $customer->postal_code : '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td><?php echo htmlspecialchars($customer->country ? $customer->country : 'Philippines'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-card-text"></i> Identification</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">ID Type:</th>
                            <td><?php echo $customer->id_type ? ucfirst(str_replace('_', ' ', $customer->id_type)) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>ID Number:</th>
                            <td><?php echo htmlspecialchars($customer->id_number ? $customer->id_number : '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Record Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Created:</th>
                            <td><?php echo isset($customer->created_at) ? date('M d, Y H:i', strtotime($customer->created_at)) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?php echo isset($customer->updated_at) ? date('M d, Y H:i', strtotime($customer->updated_at)) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($customer->notes): ?>
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-sticky"></i> Notes</h6>
        </div>
        <div class="card-body">
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($customer->notes)); ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($bookings)): ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Booking History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Room</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($booking->room_name); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking->check_in)); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking->check_out)); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    if ($booking->status == 'confirmed') $badge_class = 'success';
                                    if ($booking->status == 'cancelled') $badge_class = 'danger';
                                    if ($booking->status == 'pending') $badge_class = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($booking->status); ?></span>
                                </td>
                                <td>₱<?php echo number_format($booking->total_amount, 2); ?></td>
                                <td>
                                    <a href="<?php echo base_url('bookings/' . $booking->id); ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Booking History</h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">No bookings found for this customer/guest.</p>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <?php if (isset($can_delete) && $can_delete): ?>
        <a href="<?php echo base_url('customers/delete/' . $customer->id); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this customer/guest?');">
            <i class="bi bi-trash"></i> Delete
        </a>
        <?php endif; ?>
        <a href="<?php echo base_url('customers'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

