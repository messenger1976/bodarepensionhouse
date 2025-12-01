<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Room</h5>
        <a href="<?php echo base_url('rooms'); ?>" class="btn btn-secondary">
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
    
    <?php echo form_open('rooms/add', array('id' => 'room-form')); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="room_name" class="form-label">Room Name *</label>
                <input type="text" class="form-control" id="room_name" name="room_name" value="<?php echo set_value('room_name'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="room_type" class="form-label">Room Type *</label>
                <input type="text" class="form-control" id="room_type" name="room_type" value="<?php echo set_value('room_type'); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="room_code" class="form-label">Room Code *</label>
                <input type="text" class="form-control" id="room_code" name="room_code" value="<?php echo set_value('room_code'); ?>" placeholder="e.g., dormitory, standard, deluxeb" required>
                <small class="form-text text-muted">Unique code used to link rooms in rooms.php (e.g., dormitory, standard, deluxeb)</small>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Price per Night (₱) *</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo set_value('price'); ?>" step="0.01" min="0" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label">Capacity (persons) *</label>
                <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo set_value('capacity'); ?>" min="1" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="available_rooms" class="form-label">Number of Rooms Available *</label>
                <input type="number" class="form-control" id="available_rooms" name="available_rooms" value="<?php echo set_value('available_rooms', 1); ?>" min="1" required>
                <small class="form-text text-muted">How many rooms of this type are available for booking. Used for conflict checking.</small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo set_value('description'); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="amenities" class="form-label">Amenities</label>
            <textarea class="form-control" id="amenities" name="amenities" rows="3" placeholder="e.g., Wifi, Television, Bathroom"><?php echo set_value('amenities'); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status *</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" <?php echo set_select('status', 'active', TRUE); ?>>Active</option>
                <option value="inactive" <?php echo set_select('status', 'inactive'); ?>>Inactive</option>
            </select>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('rooms'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Add Room</button>
        </div>
    <?php echo form_close(); ?>
</div>

