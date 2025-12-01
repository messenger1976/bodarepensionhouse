<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-gear"></i> Room Settings</h5>
        <a href="<?php echo base_url('rooms'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Rooms
        </a>
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
    
    <?php echo form_open('room_settings'); ?>
        <div class="row">
            <!-- General Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-sliders"></i> General Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="default_status" class="form-label">Default Room Status *</label>
                            <select class="form-control" id="default_status" name="default_status" required>
                                <option value="active" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="maintenance" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                            <small class="form-text text-muted">Status assigned to new rooms</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="default_capacity" class="form-label">Default Capacity</label>
                                <input type="number" class="form-control" id="default_capacity" name="default_capacity" 
                                    value="<?php echo isset($settings['default_capacity']) ? htmlspecialchars($settings['default_capacity']) : '2'; ?>" min="1">
                                <small class="form-text text-muted">Default number of guests</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_capacity" class="form-label">Maximum Capacity</label>
                                <input type="number" class="form-control" id="max_capacity" name="max_capacity" 
                                    value="<?php echo isset($settings['max_capacity']) ? htmlspecialchars($settings['max_capacity']) : '10'; ?>" min="1">
                                <small class="form-text text-muted">Maximum guests allowed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-currency-dollar"></i> Pricing Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="price_currency" class="form-label">Currency Symbol</label>
                            <input type="text" class="form-control" id="price_currency" name="price_currency" 
                                value="<?php echo isset($settings['price_currency']) ? htmlspecialchars($settings['price_currency']) : '₱'; ?>" maxlength="5">
                            <small class="form-text text-muted">Currency symbol (e.g., ₱, $, €)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price_display_format" class="form-label">Price Display Format</label>
                            <select class="form-control" id="price_display_format" name="price_display_format">
                                <option value="per_night" <?php echo (isset($settings['price_display_format']) && $settings['price_display_format'] == 'per_night') ? 'selected' : ''; ?>>Per Night</option>
                                <option value="per_person" <?php echo (isset($settings['price_display_format']) && $settings['price_display_format'] == 'per_person') ? 'selected' : ''; ?>>Per Person</option>
                                <option value="per_room" <?php echo (isset($settings['price_display_format']) && $settings['price_display_format'] == 'per_room') ? 'selected' : ''; ?>>Per Room</option>
                            </select>
                            <small class="form-text text-muted">How prices are displayed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Room Types -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-tags"></i> Room Types</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="room_types" class="form-label">Available Room Types</label>
                            <textarea class="form-control" id="room_types" name="room_types" rows="6" 
                                placeholder="Standard&#10;Deluxe&#10;Executive&#10;Suite&#10;Dormitory"><?php echo isset($settings['room_types']) ? htmlspecialchars($settings['room_types']) : 'Standard' . "\n" . 'Deluxe' . "\n" . 'Executive' . "\n" . 'Suite' . "\n" . 'Dormitory'; ?></textarea>
                            <small class="form-text text-muted">One room type per line (used as suggestions when creating rooms)</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Amenities -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-star"></i> Amenities</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="amenities_list" class="form-label">Available Amenities</label>
                            <textarea class="form-control" id="amenities_list" name="amenities_list" rows="6" 
                                placeholder="WiFi&#10;Television&#10;Private Bathroom&#10;Air Conditioning&#10;Hot Water&#10;Room Service"><?php echo isset($settings['amenities_list']) ? htmlspecialchars($settings['amenities_list']) : 'WiFi' . "\n" . 'Television' . "\n" . 'Private Bathroom' . "\n" . 'Air Conditioning' . "\n" . 'Hot Water'; ?></textarea>
                            <small class="form-text text-muted">One amenity per line (used as suggestions when creating rooms)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Image Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-image"></i> Image Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image_upload_path" class="form-label">Image Upload Path</label>
                            <input type="text" class="form-control" id="image_upload_path" name="image_upload_path" 
                                value="<?php echo isset($settings['image_upload_path']) ? htmlspecialchars($settings['image_upload_path']) : 'img/rooms/'; ?>">
                            <small class="form-text text-muted">Directory path for room images (relative to root)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_images_per_room" class="form-label">Maximum Images Per Room</label>
                            <input type="number" class="form-control" id="max_images_per_room" name="max_images_per_room" 
                                value="<?php echo isset($settings['max_images_per_room']) ? htmlspecialchars($settings['max_images_per_room']) : '5'; ?>" min="1" max="20">
                            <small class="form-text text-muted">Maximum number of images allowed per room</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Display Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-display"></i> Display Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_online_booking" name="allow_online_booking" value="1" 
                                    <?php echo (isset($settings['allow_online_booking']) && $settings['allow_online_booking'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="allow_online_booking">
                                    Allow Online Booking
                                </label>
                                <small class="form-text text-muted d-block">Allow customers to book rooms online</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_availability_calendar" name="show_availability_calendar" value="1" 
                                    <?php echo (isset($settings['show_availability_calendar']) && $settings['show_availability_calendar'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_availability_calendar">
                                    Show Availability Calendar
                                </label>
                                <small class="form-text text-muted d-block">Display room availability calendar on frontend</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Notes -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-file-text"></i> Additional Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="room_notes" class="form-label">Room Notes/Policy</label>
                    <textarea class="form-control" id="room_notes" name="room_notes" rows="5" 
                        placeholder="Enter room policy, terms and conditions, or additional notes..."><?php echo isset($settings['room_notes']) ? htmlspecialchars($settings['room_notes']) : ''; ?></textarea>
                    <small class="form-text text-muted">This information can be displayed to guests when viewing rooms</small>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('rooms'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save Settings
            </button>
        </div>
    <?php echo form_close(); ?>
</div>

