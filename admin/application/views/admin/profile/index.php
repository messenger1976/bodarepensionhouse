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

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-person-circle"></i> My Profile</h5>
        <a href="<?php echo base_url('dashboard'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="row">
        <!-- Profile Information Card -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Profile Information</h6>
                </div>
                <div class="card-body">
                    <?php echo form_open_multipart('profile/update'); ?>
                        <!-- Avatar Upload Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Profile Avatar</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-preview-container">
                                        <?php if (!empty($admin->avatar) && file_exists(FCPATH . $admin->avatar)): ?>
                                            <img src="<?php echo base_url($admin->avatar); ?>" alt="Avatar" 
                                                class="rounded-circle" id="avatar-preview" 
                                                style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #dee2e6;">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                id="avatar-preview" 
                                                style="width: 100px; height: 100px; background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%); color: #fff; font-size: 2.5rem; font-weight: 600; border: 3px solid #dee2e6;">
                                                <?php 
                                                $display_name = !empty($admin->name) ? $admin->name : (!empty($admin->username) ? $admin->username : 'A');
                                                echo strtoupper(substr($display_name, 0, 1)); 
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control" id="avatar" name="avatar" 
                                            accept="image/*" onchange="previewAvatar(this)">
                                        <small class="form-text text-muted">Allowed: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo set_value('username', $admin->username); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo set_value('email', $admin->email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?php echo set_value('name', $admin->name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($admin->status); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                    placeholder="Leave blank to keep current password">
                                <small class="form-text text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                    placeholder="Confirm new password">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Profile
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
        
        <!-- Account Details Card -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge"></i> Account Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">ID:</th>
                            <td><?php echo $admin->id; ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?php echo $admin->status == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($admin->status); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo isset($admin->created_at) ? date('M d, Y H:i', strtotime($admin->created_at)) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?php echo isset($admin->updated_at) ? date('M d, Y H:i', strtotime($admin->updated_at)) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if ($is_super_admin): ?>
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Super Admin</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">
                        <i class="bi bi-info-circle"></i> You have super administrator privileges.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Groups and Roles Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-people-fill"></i> Groups</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($groups)): ?>
                        <?php foreach ($groups as $group): ?>
                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($group->name); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No groups assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Roles</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($role->name); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($role->slug); ?></small>
                                <?php if ($role->description): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($role->description); ?></small>
                                <?php endif; ?>
                            </div>
                            <hr class="my-2">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No roles assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Permissions Section -->
    <?php if (!empty($permissions)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-key"></i> Permissions</h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php 
                    $grouped_perms = array();
                    foreach ($permissions as $perm) {
                        $module = $perm->module ? $perm->module : 'general';
                        if (!isset($grouped_perms[$module])) {
                            $grouped_perms[$module] = array();
                        }
                        $grouped_perms[$module][] = $perm;
                    }
                    ?>
                    <?php foreach ($grouped_perms as $module => $module_perms): ?>
                        <div class="mb-3">
                            <strong class="text-primary"><?php echo ucfirst($module); ?>:</strong>
                            <div class="mt-1">
                                <?php foreach ($module_perms as $perm): ?>
                                    <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($perm->name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const preview = document.getElementById('avatar-preview');
        
        reader.onload = function(e) {
            // Check if preview is an img or div
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Replace div with img
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-circle';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.border = '3px solid #dee2e6';
                img.id = 'avatar-preview';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

