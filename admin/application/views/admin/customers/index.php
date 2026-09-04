<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-people"></i> Manage Customers/Guests</h5>
        <?php if (isset($can_add) && $can_add): ?>
        <a href="<?php echo base_url('customers/add'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Register New Customer/Guest
        </a>
        <?php endif; ?>
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
    
    <!-- Search Form -->
    <div class="mb-4">
        <form method="get" action="<?php echo base_url('customers'); ?>" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars(isset($search_term) ? $search_term : ''); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
        <?php if (isset($search_term) && $search_term): ?>
            <div class="mt-2">
                <a href="<?php echo base_url('customers'); ?>" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear Search
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo $customer->id; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($customer->first_name . ' ' . $customer->last_name); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($customer->email); ?></td>
                            <td><?php echo htmlspecialchars($customer->phone ? $customer->phone : '-'); ?></td>
                            <td>
                                <?php 
                                $address_parts = array_filter([
                                    $customer->address,
                                    $customer->city,
                                    $customer->province
                                ]);
                                echo htmlspecialchars(!empty($address_parts) ? implode(', ', $address_parts) : '-');
                                ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $customer->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($customer->status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($can_edit) && $can_edit): ?>
                                <a href="<?php echo base_url('customers/view/' . $customer->id); ?>" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo base_url('customers/edit/' . $customer->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <a href="<?php echo base_url('customers/delete/' . $customer->id); ?>"
                                   class="btn btn-sm btn-danger delete-customer-btn"
                                   data-name="<?php echo htmlspecialchars(trim($customer->first_name . ' ' . $customer->last_name), ENT_QUOTES, 'UTF-8'); ?>"
                                   data-email="<?php echo htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8'); ?>"
                                   title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No customers/guests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

