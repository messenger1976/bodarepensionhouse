<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-shield-check"></i> Manage Roles</h5>
        <?php if (isset($can_manage) && $can_manage): ?>
        <a href="<?php echo base_url('roles/add'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Role
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
    
    <?php
    // Ensure roles is an array and not empty
    $roles_display = isset($roles) ? $roles : array();
    if (!is_array($roles_display)) {
        $roles_display = array();
    }
    ?>
    <div class="mob-desktop-table mob-desktop-table--xl">
    <div class="table-responsive dt-fit-wrap">
        <table class="table table-hover dt-fit-width" id="rolesTable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th class="col-name">Role</th>
                    <th class="col-name">Permissions</th>
                    <th>Status</th>
                    <th class="col-actions" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roles_display)): ?>
                    <?php foreach ($roles_display as $role): ?>
                        <tr>
                            <td class="text-muted"><?php echo (int) $role->id; ?></td>
                            <td class="col-name">
                                <div class="dt-cell-title"><?php echo htmlspecialchars($role->name); ?></div>
                                <div class="dt-cell-sub"><code><?php echo htmlspecialchars($role->slug); ?></code></div>
                                <?php if (!empty($role->description)): ?>
                                <div class="dt-cell-sub text-muted" title="<?php echo htmlspecialchars($role->description); ?>"><?php echo htmlspecialchars($role->description); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-name">
                                <?php if (!empty($role->permissions)): ?>
                                    <span class="badge bg-primary me-1" title="<?php echo count($role->permissions); ?> permissions">
                                        <?php echo count($role->permissions); ?> permission(s)
                                    </span>
                                    <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#perms_<?php echo $role->id; ?>" aria-expanded="false">
                                        <small>View</small>
                                    </button>
                                    <div class="collapse mt-2" id="perms_<?php echo $role->id; ?>">
                                        <div class="card card-body p-2">
                                            <?php foreach ($role->permissions as $perm): ?>
                                                <small class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($perm->name); ?></small>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No permissions assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $role->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($role->status); ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <?php if (isset($can_manage) && $can_manage): ?>
                                <a href="<?php echo base_url('roles/edit/' . $role->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo base_url('roles/delete/' . $role->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No roles found
                            <?php if (isset($roles)): ?>
                                <br><small class="text-danger">Debug: roles variable exists (type: <?php echo gettype($roles); ?>, 
                                <?php if (is_array($roles)): ?>
                                    count: <?php echo count($roles); ?>)
                                <?php elseif (is_object($roles)): ?>
                                    object with <?php echo count((array)$roles); ?> properties)
                                <?php else: ?>
                                    value: <?php echo var_export($roles, true); ?>)
                                <?php endif; ?>
                                </small>
                            <?php else: ?>
                                <br><small class="text-danger">Debug: roles variable is NOT set in view</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="mob-card-list mob-card-list--xl d-xl-none">
        <?php if (!empty($roles_display)): ?>
            <?php foreach ($roles_display as $role): ?>
                <div class="mob-list-card">
                    <div class="mob-list-card-header">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="mob-list-card-title"><?php echo htmlspecialchars($role->name); ?></div>
                            <div class="mob-list-card-meta text-truncate">#<?php echo (int) $role->id; ?> &middot; <code><?php echo htmlspecialchars($role->slug); ?></code></div>
                        </div>
                        <span class="badge bg-<?php echo $role->status == 'active' ? 'success' : 'secondary'; ?> flex-shrink-0">
                            <?php echo ucfirst($role->status); ?>
                        </span>
                    </div>
                    <div class="mob-list-card-body">
                        <?php if (!empty($role->description)): ?>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($role->description); ?>
                        </div>
                        <?php endif; ?>
                        <div class="mob-list-card-meta">
                            <i class="bi bi-key"></i>
                            <?php if (!empty($role->permissions)): ?>
                                <span class="badge bg-primary me-1"><?php echo count($role->permissions); ?> permission(s)</span>
                                <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#perms_card_<?php echo (int) $role->id; ?>" aria-expanded="false">
                                    <small>View</small>
                                </button>
                                <div class="collapse mt-2" id="perms_card_<?php echo (int) $role->id; ?>">
                                    <div class="card card-body p-2">
                                        <?php foreach ($role->permissions as $perm): ?>
                                            <small class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($perm->name); ?></small>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No permissions assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isset($can_manage) && $can_manage): ?>
                    <div class="mob-list-card-actions">
                        <a href="<?php echo base_url('roles/edit/' . $role->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="<?php echo base_url('roles/delete/' . $role->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?');" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mob-list-card mob-list-card-empty text-center text-muted py-4">No roles found</div>
        <?php endif; ?>
    </div>
</div>

