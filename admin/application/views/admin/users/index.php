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
    
    <div class="card card-bordered">
        <div class="card-inner">
            <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Groups</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user->id; ?></td>
                            <td><?php echo htmlspecialchars($user->username); ?></td>
                            <td><?php echo htmlspecialchars($user->name); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td>
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
                            <td>
                                <?php if (isset($can_edit) && $can_edit): ?>
                                <a href="<?php echo base_url('users/view/' . $user->id); ?>" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo base_url('users/edit/' . $user->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (isset($can_delete) && $can_delete && $user->id != $this->session->userdata('admin_id')): ?>
                                <a href="<?php echo base_url('users/delete/' . $user->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

