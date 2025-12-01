<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Customer/Guest</h5>
        <a href="<?php echo base_url('customers'); ?>" class="btn btn-secondary">
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
    
    <?php echo form_open('customers/edit/' . $customer->id); ?>
        <h6 class="mb-3"><i class="bi bi-person"></i> Personal Information</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo set_value('first_name', $customer->first_name); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Last Name *</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo set_value('last_name', $customer->last_name); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo set_value('email', $customer->email); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo set_value('phone', $customer->phone); ?>" placeholder="e.g., +63 912 345 6789">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo set_value('date_of_birth', $customer->date_of_birth); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" <?php echo set_select('gender', 'male', $customer->gender == 'male'); ?>>Male</option>
                    <option value="female" <?php echo set_select('gender', 'female', $customer->gender == 'female'); ?>>Female</option>
                    <option value="other" <?php echo set_select('gender', 'other', $customer->gender == 'other'); ?>>Other</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="nationality" class="form-label">Nationality</label>
                <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo set_value('nationality', $customer->nationality); ?>" placeholder="e.g., Filipino">
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="mb-3"><i class="bi bi-geo-alt"></i> Address Information</h6>
        <div class="mb-3">
            <label for="address" class="form-label">Street Address</label>
            <textarea class="form-control" id="address" name="address" rows="2"><?php echo set_value('address', $customer->address); ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="<?php echo set_value('city', $customer->city); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="province" class="form-label">Province</label>
                <input type="text" class="form-control" id="province" name="province" value="<?php echo set_value('province', $customer->province); ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo set_value('postal_code', $customer->postal_code); ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" value="<?php echo set_value('country', $customer->country ? $customer->country : 'Philippines'); ?>">
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="mb-3"><i class="bi bi-card-text"></i> Identification</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="id_type" class="form-label">ID Type</label>
                <select class="form-select" id="id_type" name="id_type">
                    <option value="">Select ID Type</option>
                    <option value="passport" <?php echo set_select('id_type', 'passport', $customer->id_type == 'passport'); ?>>Passport</option>
                    <option value="driver_license" <?php echo set_select('id_type', 'driver_license', $customer->id_type == 'driver_license'); ?>>Driver's License</option>
                    <option value="national_id" <?php echo set_select('id_type', 'national_id', $customer->id_type == 'national_id'); ?>>National ID</option>
                    <option value="philhealth" <?php echo set_select('id_type', 'philhealth', $customer->id_type == 'philhealth'); ?>>PhilHealth ID</option>
                    <option value="sss" <?php echo set_select('id_type', 'sss', $customer->id_type == 'sss'); ?>>SSS ID</option>
                    <option value="tin" <?php echo set_select('id_type', 'tin', $customer->id_type == 'tin'); ?>>TIN ID</option>
                    <option value="other" <?php echo set_select('id_type', 'other', $customer->id_type == 'other'); ?>>Other</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="id_number" class="form-label">ID Number</label>
                <input type="text" class="form-control" id="id_number" name="id_number" value="<?php echo set_value('id_number', $customer->id_number); ?>">
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="mb-3"><i class="bi bi-sticky"></i> Additional Information</h6>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about this customer/guest..."><?php echo set_value('notes', $customer->notes); ?></textarea>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?php echo set_checkbox('status', '1', $customer->status == 'active'); ?>>
                <label class="form-check-label" for="status">
                    Active
                </label>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('customers'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Customer/Guest</button>
        </div>
    <?php echo form_close(); ?>
</div>

