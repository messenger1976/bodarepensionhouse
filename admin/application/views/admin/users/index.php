<div class="nk-block">
    <div class="nk-block-head">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title"><i class="bi bi-people"></i> Manage Admin Users</h3>
                <div class="nk-block-des text-soft">
                    <p>View and manage all admin user accounts</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <?php if (isset($can_delete) && $can_delete): ?>
                            <li>
                                <button type="submit" form="users-batch-form" id="batch-delete-btn" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i> <span>Delete Selected<span data-bd-count></span></span>
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($can_add) && $can_add): ?>
                            <li>
                                <a href="<?php echo base_url('users/add'); ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> <span>Add New User</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
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
    
    <form id="users-batch-form" method="post" action="<?php echo base_url('users/batch_delete'); ?>">
    <div class="card card-bordered mob-desktop-table mob-desktop-table--xl">
        <div class="card-inner">
                <div class="table-responsive dt-fit-wrap">
                    <table class="table table-hover dt-fit-width" id="usersTable" style="width:100%">
                        <thead>
                            <tr>
                                <?php if (isset($can_delete) && $can_delete): ?>
                                <th class="col-select" data-orderable="false">
                                    <input type="checkbox" class="form-check-input" data-bd-select-all="users-batch-form" title="Select all">
                                </th>
                                <?php endif; ?>
                                <th>ID</th>
                                <th class="col-name">User</th>
                                <th class="col-name">Groups</th>
                                <th>Status</th>
                                <th class="col-actions" data-orderable="false">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php $is_current_user = ($user->id == $this->session->userdata('admin_id')); ?>
                                    <tr>
                                        <?php if (isset($can_delete) && $can_delete): ?>
                                        <td class="col-select">
                                            <?php if (!$is_current_user): ?>
                                            <input type="checkbox" class="form-check-input user-select-checkbox" name="user_ids[]" value="<?php echo (int) $user->id; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="text-muted"><?php echo (int) $user->id; ?></td>
                                        <td class="col-name">
                                            <div class="dt-cell-title">
                                                <?php echo htmlspecialchars($user->name); ?>
                                                <?php if ($is_current_user): ?><span class="badge bg-light text-dark ms-1">You</span><?php endif; ?>
                                            </div>
                                            <div class="dt-cell-sub text-muted">@<?php echo htmlspecialchars($user->username); ?></div>
                                            <div class="dt-cell-sub text-muted" title="<?php echo htmlspecialchars($user->email); ?>"><?php echo htmlspecialchars($user->email); ?></div>
                                        </td>
                                        <td class="col-name">
                                            <?php if (!empty($user->groups)): ?>
                                                <?php foreach ($user->groups as $group): ?>
                                                    <span class="badge bg-info me-1"><?php echo htmlspecialchars($group->name); ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No groups</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user->status == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user->status); ?>
                                            </span>
                                        </td>
                                        <td class="col-actions">
                                            <?php if (isset($can_edit) && $can_edit): ?>
                                            <a href="<?php echo base_url('users/view/' . $user->id); ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (isset($can_delete) && $can_delete && !$is_current_user): ?>
                                            <a href="<?php echo base_url('users/delete/' . $user->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo (isset($can_delete) && $can_delete) ? '6' : '5'; ?>" class="text-center text-muted">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($users) && isset($can_delete) && $can_delete): ?>
        <div class="d-flex align-items-center gap-2 mb-2 px-1">
            <input type="checkbox" class="form-check-input" id="users-mobile-select-all" data-bd-select-all="users-batch-form" title="Select all">
            <label class="form-check-label small text-muted" for="users-mobile-select-all">Select all</label>
        </div>
        <?php endif; ?>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <?php $is_current_user = ($user->id == $this->session->userdata('admin_id')); ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <?php if (isset($can_delete) && $can_delete && !$is_current_user): ?>
                        <input type="checkbox" class="form-check-input user-select-checkbox me-2 flex-shrink-0" name="user_ids[]" value="<?php echo (int) $user->id; ?>" aria-label="Select user">
                        <?php endif; ?>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title">
                                <?php echo htmlspecialchars($user->name); ?>
                                <?php if ($is_current_user): ?><span class="badge bg-light text-dark ms-1">You</span><?php endif; ?>
                            </div>
                            <div class="mob-list-card-meta text-truncate">#<?php echo (int) $user->id; ?> &middot; @<?php echo htmlspecialchars($user->username); ?></div>
                        </div>
                        <span class="badge bg-<?php echo $user->status == 'active' ? 'success' : 'secondary'; ?> flex-shrink-0">
                            <?php echo ucfirst($user->status); ?>
                        </span>
                    </div>
                    <div class="mob-list-card-body">
                        <div class="mob-list-card-meta text-truncate" title="<?php echo htmlspecialchars($user->email); ?>">
                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user->email); ?>
                        </div>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-shield-lock"></i>
                            <?php if (!empty($user->groups)): ?>
                                <?php foreach ($user->groups as $group): ?>
                                    <span class="badge bg-info me-1"><?php echo htmlspecialchars($group->name); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No groups</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ((isset($can_edit) && $can_edit) || (isset($can_delete) && $can_delete && !$is_current_user)): ?>
                    <div class="mob-list-card-actions">
                        <?php if (isset($can_edit) && $can_edit): ?>
                        <a href="<?php echo base_url('users/view/' . $user->id); ?>" class="btn btn-sm btn-info" title="View">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <?php endif; ?>
                        <?php if (isset($can_delete) && $can_delete && !$is_current_user): ?>
                        <a href="<?php echo base_url('users/delete/' . $user->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No users found</div>
        <?php endif; ?>
    </div>
    </form>
</div>

<?php
if (!empty($can_delete)) {
    $this->load->view('admin/layout/batch_delete', array(
        'bd_form_id' => 'users-batch-form',
        'bd_checkbox_class' => 'user-select-checkbox',
        'bd_button_id' => 'batch-delete-btn',
        'bd_label' => 'user'
    ));
}
?>
