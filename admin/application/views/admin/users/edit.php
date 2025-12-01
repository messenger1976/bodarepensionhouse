<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Admin User</h5>
        <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">
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
    
    <?php echo form_open_multipart('users/edit/' . $user->id); ?>
        <!-- Avatar Upload Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <label class="form-label">Profile Avatar</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-preview-container">
                        <?php if (!empty($user->avatar) && file_exists(FCPATH . $user->avatar)): ?>
                            <img src="<?php echo base_url($user->avatar); ?>" alt="Avatar" 
                                class="rounded-circle" id="avatar-preview" 
                                style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #dee2e6;">
                        <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                id="avatar-preview" 
                                style="width: 100px; height: 100px; background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%); color: #fff; font-size: 2.5rem; font-weight: 600; border: 3px solid #dee2e6;">
                                <?php 
                                $display_name = !empty($user->name) ? $user->name : (!empty($user->username) ? $user->username : 'A');
                                echo strtoupper(substr($display_name, 0, 1)); 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" class="form-control mb-2" id="avatar" name="avatar" 
                            accept="image/*" onchange="previewAvatar(this)">
                        <small class="form-text text-muted d-block mb-2">Allowed: JPG, PNG, GIF, WEBP (Max 2MB). Leave blank to keep current avatar.</small>
                        <?php if (!empty($user->avatar) && file_exists(FCPATH . $user->avatar)): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar" value="1">
                                <label class="form-check-label text-danger" for="remove_avatar">
                                    <i class="bi bi-trash"></i> Remove current avatar
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo set_value('username', $user->username); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email', $user->email); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo set_value('name', $user->name); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="form-text text-muted">Minimum 6 characters if changing</small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="groups" class="form-label">Assign Groups *</label>
            <small class="form-text text-muted d-block mb-2">Select one or more groups to assign to this user</small>
            <?php if (!empty($groups)): ?>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($groups as $group): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="groups[]" value="<?php echo $group->id; ?>" id="group_<?php echo $group->id; ?>" 
                                <?php echo (in_array($group->id, $selected_group_ids)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="group_<?php echo $group->id; ?>">
                                <strong><?php echo htmlspecialchars($group->name); ?></strong>
                                <?php if ($group->description): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($group->description); ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No groups available. Please <a href="<?php echo base_url('groups/add'); ?>">create a group</a> first.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?php echo set_checkbox('status', '1', $user->status == 'active'); ?>>
                <label class="form-check-label" for="status">
                    Active
                </label>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>
    <?php echo form_close(); ?>
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

