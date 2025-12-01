<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Role</h5>
        <a href="<?php echo base_url('roles'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger">
            <?php echo validation_errors(); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php echo form_open('roles/edit/' . $role->id); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Role Name *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $role->name); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="slug" class="form-label">Role Slug *</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo set_value('slug', $role->slug); ?>" required pattern="[a-z0-9_-]+" title="Only lowercase letters, numbers, dashes, and underscores">
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $role->description); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Assign Permissions *</label>
            <small class="form-text text-muted d-block mb-2">Select one or more permissions to assign to this role</small>
            <?php if (!empty($permissions)): ?>
                <?php foreach ($permissions as $module => $module_permissions): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><?php echo ucfirst($module); ?> Permissions</strong>
                        </div>
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($module_permissions as $permission): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission->id; ?>" id="perm_<?php echo $permission->id; ?>" 
                                        <?php echo (in_array($permission->id, $selected_permission_ids)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="perm_<?php echo $permission->id; ?>">
                                        <strong><?php echo htmlspecialchars($permission->name); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($permission->slug); ?></small>
                                        <?php if ($permission->description): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($permission->description); ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    No permissions available.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?php echo set_checkbox('status', '1', $role->status == 'active'); ?>>
                <label class="form-check-label" for="status">
                    Active
                </label>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('roles'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Role</button>
        </div>
    <?php echo form_close(); ?>
</div>

