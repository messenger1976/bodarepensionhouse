<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Room</h5>
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
    
    <?php echo form_open('rooms/edit/' . $room->id, array('id' => 'room-form')); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="room_name" class="form-label">Room Name *</label>
                <input type="text" class="form-control" id="room_name" name="room_name" value="<?php echo set_value('room_name', $room->room_name); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="room_type" class="form-label">Room Type *</label>
                <input type="text" class="form-control" id="room_type" name="room_type" value="<?php echo set_value('room_type', $room->room_type); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="room_code" class="form-label">Room Code *</label>
                <input type="text" class="form-control" id="room_code" name="room_code" value="<?php echo set_value('room_code', isset($room->room_code) ? $room->room_code : ''); ?>" placeholder="e.g., dormitory, standard, deluxeb" required>
                <small class="form-text text-muted">Unique code used to link rooms in rooms.php (e.g., dormitory, standard, deluxeb)</small>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Price per Night (₱) *</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo set_value('price', $room->price); ?>" step="0.01" min="0" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label">Capacity (persons) *</label>
                <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo set_value('capacity', $room->capacity); ?>" min="1" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="available_rooms" class="form-label">Number of Rooms Available *</label>
                <input type="number" class="form-control" id="available_rooms" name="available_rooms" value="<?php echo set_value('available_rooms', isset($room->available_rooms) ? $room->available_rooms : 1); ?>" min="1" required>
                <small class="form-text text-muted">How many rooms of this type are available for booking. Used for conflict checking.</small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo set_value('description', $room->description); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="amenities" class="form-label">Amenities</label>
            <textarea class="form-control" id="amenities" name="amenities" rows="3" placeholder="e.g., Wifi, Television, Bathroom"><?php echo set_value('amenities', $room->amenities); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status *</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" <?php echo set_select('status', 'active', $room->status == 'active'); ?>>Active</option>
                <option value="inactive" <?php echo set_select('status', 'inactive', $room->status == 'inactive'); ?>>Inactive</option>
            </select>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('rooms'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Room</button>
        </div>
    <?php echo form_close(); ?>
    
    <!-- Image Gallery Section -->
    <div class="content-card mt-4">
        <h5 class="mb-4"><i class="bi bi-images"></i> Room Image Gallery</h5>
        
        <!-- Upload Form -->
        <div class="mb-4">
            <form id="image-upload-form" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label for="image_file" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" id="image_file" name="image" accept="image/*" required>
                        <small class="form-text text-muted">Allowed: JPG, PNG, GIF, WEBP (Max 5MB)</small>
                    </div>
                    <div class="col-md-4">
                        <label for="alt_text" class="form-label">Alt Text (Optional)</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Image description">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Image Gallery Grid -->
        <div id="image-gallery" class="row g-3">
            <?php if (!empty($room_images)): ?>
                <?php foreach ($room_images as $image): ?>
                    <div class="col-md-3 col-sm-4 col-6 image-item" data-image-id="<?php echo $image->id; ?>">
                        <div class="card position-relative">
                            <a href="<?php echo base_url($image->image_path); ?>" data-lightbox="room-gallery" data-title="<?php echo htmlspecialchars($image->alt_text ? $image->alt_text : $room->room_name); ?>">
                                <img src="<?php echo base_url($image->image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($image->alt_text); ?>" style="height: 200px; object-fit: cover;">
                            </a>
                            <?php if ($image->is_primary): ?>
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">Primary</span>
                            <?php endif; ?>
                            <div class="card-body p-2">
                                <div class="btn-group w-100" role="group">
                                    <?php if (!$image->is_primary): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary set-primary-btn" data-image-id="<?php echo $image->id; ?>" title="Set as Primary">
                                            <i class="bi bi-star"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-image-btn" data-image-id="<?php echo $image->id; ?>" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted text-center">No images uploaded yet. Upload images to create a gallery.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- jQuery (required for lightbox2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    
    <script>
        // Wait for jQuery and lightbox to be fully loaded
        (function() {
            // Check if jQuery is loaded
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is required for lightbox but is not loaded');
                return;
            }
            
            jQuery(document).ready(function($) {
                // Wait a bit to ensure lightbox is fully loaded
                setTimeout(function() {
                    if (typeof lightbox !== 'undefined' && typeof lightbox.option === 'function') {
                        // Configure lightbox
                        lightbox.option({
                            'resizeDuration': 200,
                            'wrapAround': true,
                            'fadeDuration': 300,
                            'imageFadeDuration': 300
                        });
                    } else {
                        console.warn('Lightbox is not properly loaded');
                    }
                }, 100);
            });
        })();
        
        // Image upload handler
        document.getElementById('image-upload-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('image', document.getElementById('image_file').files[0]);
            formData.append('alt_text', document.getElementById('alt_text').value);
            
            const uploadBtn = this.querySelector('button[type="submit"]');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';
            
            fetch('<?php echo base_url("rooms/upload_image/" . $room->id); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show new image
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the image.');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = originalText;
            });
        });
        
        // Delete image handler
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to delete this image?')) {
                    return;
                }
                
                const imageId = this.getAttribute('data-image-id');
                const imageItem = this.closest('.image-item');
                
                fetch('<?php echo base_url("rooms/delete_image/"); ?>' + imageId, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        imageItem.remove();
                        // Check if gallery is empty
                        if (document.querySelectorAll('.image-item').length === 0) {
                            document.getElementById('image-gallery').innerHTML = '<div class="col-12"><p class="text-muted text-center">No images uploaded yet. Upload images to create a gallery.</p></div>';
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the image.');
                });
            });
        });
        
        // Set primary image handler
        document.querySelectorAll('.set-primary-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const imageId = this.getAttribute('data-image-id');
                
                fetch('<?php echo base_url("rooms/set_primary_image/" . $room->id . "/"); ?>' + imageId, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while setting the primary image.');
                });
            });
        });
    </script>
</div>

