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
                                    if ($booking->status == 'checked_in') $badge_class = 'info';
                                    if ($booking->status == 'checked_out' || $booking->status == 'completed') $badge_class = 'primary';
                                    if ($booking->status == 'cancelled') $badge_class = 'danger';
                                    if ($booking->status == 'pending') $badge_class = 'warning';
                                ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucwords(str_replace('_', ' ', $booking->status)); ?></span>
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
        <a href="<?php echo base_url('customers/delete/' . $customer->id); ?>"
           class="btn btn-danger delete-customer-btn"
           data-name="<?php echo htmlspecialchars(trim($customer->first_name . ' ' . $customer->last_name), ENT_QUOTES, 'UTF-8'); ?>"
           data-email="<?php echo htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8'); ?>">
            <i class="bi bi-trash"></i> Delete
        </a>
        <?php endif; ?>
        <a href="<?php echo base_url('customers'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Confirm-delete modal. Warns that the matching website account is removed too. -->
<div class="modal fade" id="confirmDeleteCustomerModal" tabindex="-1" aria-labelledby="confirmDeleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteCustomerModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger"></i> Delete customer/guest?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to permanently delete <strong id="deleteCustomerName">this customer/guest</strong>.</p>
                <div class="alert alert-warning mb-0">
                    <strong>Important:</strong> If this person has an online website account, it will
                    <strong>also be deleted</strong> and they will no longer be able to log in to the website.
                    Past bookings are kept but will be detached from the account. This action
                    <strong>cannot be undone</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteCustomerBtn" href="#" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Yes, delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function bindDeleteButtons() {
        var buttons = document.querySelectorAll('.delete-customer-btn');
        var modalEl = document.getElementById('confirmDeleteCustomerModal');
        var modalSupported = (typeof bootstrap !== 'undefined') && bootstrap.Modal && modalEl;

        if (!modalSupported) {
            // Fallback: native confirm() when the Bootstrap modal is unavailable.
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    var name = btn.getAttribute('data-name') || 'this customer/guest';
                    var ok = window.confirm('Delete ' + name + '?\n\nWarning: if this person has an online website account it will also be deleted and they will no longer be able to log in to the website. Past bookings are kept but detached. This cannot be undone.');
                    if (!ok) e.preventDefault();
                });
            });
            return;
        }

        var nameEl = document.getElementById('deleteCustomerName');
        var confirmBtn = document.getElementById('confirmDeleteCustomerBtn');
        var modal = new bootstrap.Modal(modalEl);

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var name = btn.getAttribute('data-name') || '';
                var email = btn.getAttribute('data-email') || '';
                if (nameEl) {
                    nameEl.textContent = name
                        ? name + (email ? ' (' + email + ')' : '')
                        : 'this customer/guest';
                }
                if (confirmBtn) {
                    confirmBtn.setAttribute('href', btn.getAttribute('href'));
                }
                modal.show();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindDeleteButtons);
    } else {
        bindDeleteButtons();
    }
})();
</script>

