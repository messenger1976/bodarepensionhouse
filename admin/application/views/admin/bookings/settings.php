<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-gear"></i> Booking Settings</h5>
        <a href="<?php echo base_url('bookings'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Bookings
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
    
    <?php echo form_open('booking_settings'); ?>
        <div class="row">
            <!-- General Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-sliders"></i> General Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="default_status" class="form-label">Default Booking Status *</label>
                            <select class="form-control" id="default_status" name="default_status" required>
                                <option value="pending" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo (isset($settings['default_status']) && $settings['default_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <small class="form-text text-muted">Status assigned to new bookings</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="booking_number_prefix" class="form-label">Booking Number Prefix</label>
                            <input type="text" class="form-control" id="booking_number_prefix" name="booking_number_prefix" 
                                value="<?php echo isset($settings['booking_number_prefix']) ? htmlspecialchars($settings['booking_number_prefix']) : 'BK'; ?>" maxlength="10">
                            <small class="form-text text-muted">Prefix for booking numbers (e.g., BK, RES, BOD)</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="min_booking_days" class="form-label">Minimum Booking Days</label>
                                <input type="number" class="form-control" id="min_booking_days" name="min_booking_days" 
                                    value="<?php echo isset($settings['min_booking_days']) ? htmlspecialchars($settings['min_booking_days']) : '1'; ?>" min="1">
                                <small class="form-text text-muted">Minimum nights required</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_booking_days" class="form-label">Maximum Booking Days</label>
                                <input type="number" class="form-control" id="max_booking_days" name="max_booking_days" 
                                    value="<?php echo isset($settings['max_booking_days']) ? htmlspecialchars($settings['max_booking_days']) : '30'; ?>" min="1">
                                <small class="form-text text-muted">Maximum nights allowed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Check-in/Check-out Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-clock"></i> Check-in/Check-out Times</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="check_in_time" class="form-label">Check-in Time</label>
                            <input type="time" class="form-control" id="check_in_time" name="check_in_time" 
                                value="<?php echo isset($settings['check_in_time']) ? htmlspecialchars($settings['check_in_time']) : '14:00'; ?>">
                            <small class="form-text text-muted">Default check-in time</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="check_out_time" class="form-label">Check-out Time</label>
                            <input type="time" class="form-control" id="check_out_time" name="check_out_time" 
                                value="<?php echo isset($settings['check_out_time']) ? htmlspecialchars($settings['check_out_time']) : '12:00'; ?>">
                            <small class="form-text text-muted">Default check-out time</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cancellation_hours" class="form-label">Cancellation Hours</label>
                            <input type="number" class="form-control" id="cancellation_hours" name="cancellation_hours" 
                                value="<?php echo isset($settings['cancellation_hours']) ? htmlspecialchars($settings['cancellation_hours']) : '24'; ?>" min="0">
                            <small class="form-text text-muted">Hours before check-in for free cancellation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Payment Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="require_payment" name="require_payment" value="1" 
                                    <?php echo (isset($settings['require_payment']) && $settings['require_payment'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="require_payment">
                                    Require Payment Confirmation
                                </label>
                                <small class="form-text text-muted d-block">Require payment before confirming booking</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                value="<?php echo isset($settings['tax_rate']) ? htmlspecialchars($settings['tax_rate']) : '0'; ?>" step="0.01" min="0" max="100">
                            <small class="form-text text-muted">Tax percentage to add to booking total</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="service_charge" class="form-label">Service Charge (%)</label>
                            <input type="number" class="form-control" id="service_charge" name="service_charge" 
                                value="<?php echo isset($settings['service_charge']) ? htmlspecialchars($settings['service_charge']) : '0'; ?>" step="0.01" min="0" max="100">
                            <small class="form-text text-muted">Service charge percentage</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-bell"></i> Notification Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_email_notifications" name="send_email_notifications" value="1" 
                                    <?php echo (isset($settings['send_email_notifications']) && $settings['send_email_notifications'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="send_email_notifications">
                                    Send Email Notifications
                                </label>
                                <small class="form-text text-muted d-block">Send email when booking is created/updated</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_confirm_bookings" name="auto_confirm_bookings" value="1" 
                                    <?php echo (isset($settings['auto_confirm_bookings']) && $settings['auto_confirm_bookings'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="auto_confirm_bookings">
                                    Auto-Confirm Bookings
                                </label>
                                <small class="form-text text-muted d-block">Automatically confirm new bookings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Notes -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-file-text"></i> Additional Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="booking_notes" class="form-label">Booking Notes/Policy</label>
                    <textarea class="form-control" id="booking_notes" name="booking_notes" rows="5" 
                        placeholder="Enter booking policy, terms and conditions, or additional notes..."><?php echo isset($settings['booking_notes']) ? htmlspecialchars($settings['booking_notes']) : ''; ?></textarea>
                    <small class="form-text text-muted">This information can be displayed to guests during booking</small>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('bookings'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save Settings
            </button>
        </div>
    <?php echo form_close(); ?>
</div>

