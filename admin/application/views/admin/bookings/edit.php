<div class="content-card">
    <!-- Header Section -->
    <div class="booking-header-section mb-4 p-4 bg-gradient text-white rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="bi bi-pencil"></i> Edit Booking</h4>
                <p class="mb-0 opacity-75">Update booking reservation details</p>
            </div>
            <a href="<?php echo base_url('bookings'); ?>" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
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
    
    <?php echo form_open('bookings/edit/' . $booking->id, array('id' => 'booking-form')); ?>
    
    <!-- Guest Information - Inline Row -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h6 class="card-title mb-3"><i class="bi bi-person-fill text-primary"></i> Guest Information</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-12">
                    <label for="customer_id" class="form-label small fw-bold">Select Existing Customer (Optional)</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">-- Select a customer or enter manually --</option>
                        <?php if (!empty($customers)): ?>
                            <?php 
                            // Try to find matching customer by email
                            $selected_customer_id = '';
                            foreach ($customers as $customer) {
                                if (!empty($customer->email) && strtolower(trim($customer->email)) == strtolower(trim($booking->guest_email))) {
                                    $selected_customer_id = $customer->id;
                                    break;
                                }
                            }
                            ?>
                            <?php foreach ($customers as $customer): ?>
                                <?php 
                                $customer_name = trim($customer->first_name . ' ' . $customer->last_name);
                                $customer_email = !empty($customer->email) ? $customer->email : '';
                                $customer_phone = !empty($customer->phone) ? $customer->phone : '';
                                $display_text = $customer_name;
                                if ($customer_email) {
                                    $display_text .= ' (' . $customer_email . ')';
                                }
                                ?>
                                <option value="<?php echo $customer->id; ?>" 
                                        data-name="<?php echo htmlspecialchars($customer_name); ?>"
                                        data-email="<?php echo htmlspecialchars($customer_email); ?>"
                                        data-phone="<?php echo htmlspecialchars($customer_phone); ?>"
                                        data-address="<?php echo htmlspecialchars(isset($customer->address) ? $customer->address : ''); ?>"
                                        data-city="<?php echo htmlspecialchars(isset($customer->city) ? $customer->city : ''); ?>"
                                        data-province="<?php echo htmlspecialchars(isset($customer->province) ? $customer->province : ''); ?>"
                                        data-country="<?php echo htmlspecialchars(isset($customer->country) ? $customer->country : ''); ?>"
                                        data-zipcode="<?php echo htmlspecialchars(isset($customer->postal_code) ? $customer->postal_code : ''); ?>"
                                        <?php echo ($selected_customer_id == $customer->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($display_text); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Selecting a customer will auto-fill the guest information below.</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="guest_name" class="form-label small fw-bold">Full Name *</label>
                    <input type="text" class="form-control" id="guest_name" name="guest_name" value="<?php echo set_value('guest_name', $booking->guest_name); ?>" required placeholder="Enter guest name">
                </div>
                <div class="col-md-4">
                    <label for="guest_email" class="form-label small fw-bold">Email Address *</label>
                    <input type="email" class="form-control" id="guest_email" name="guest_email" value="<?php echo set_value('guest_email', $booking->guest_email); ?>" required placeholder="guest@example.com">
                </div>
                <div class="col-md-4">
                    <label for="guest_phone" class="form-label small fw-bold">Phone Number *</label>
                    <input type="text" class="form-control" id="guest_phone" name="guest_phone" value="<?php echo set_value('guest_phone', $booking->guest_phone); ?>" required placeholder="+63 XXX XXX XXXX">
                </div>
                <div class="col-md-12">
                    <label for="guest_address" class="form-label small fw-bold">Address</label>
                    <textarea class="form-control" id="guest_address" name="guest_address" rows="2" placeholder="Enter street address"><?php echo set_value('guest_address', isset($booking->guest_address) ? $booking->guest_address : ''); ?></textarea>
                </div>
                <div class="col-md-3">
                    <label for="guest_city" class="form-label small fw-bold">City</label>
                    <input type="text" class="form-control" id="guest_city" name="guest_city" value="<?php echo set_value('guest_city', isset($booking->guest_city) ? $booking->guest_city : ''); ?>" placeholder="Enter city">
                </div>
                <div class="col-md-3">
                    <label for="guest_province" class="form-label small fw-bold">Province</label>
                    <input type="text" class="form-control" id="guest_province" name="guest_province" value="<?php echo set_value('guest_province', isset($booking->guest_province) ? $booking->guest_province : ''); ?>" placeholder="Enter province">
                </div>
                <div class="col-md-3">
                    <label for="guest_country" class="form-label small fw-bold">Country</label>
                    <input type="text" class="form-control" id="guest_country" name="guest_country" value="<?php echo set_value('guest_country', isset($booking->guest_country) ? $booking->guest_country : 'Philippines'); ?>" placeholder="Enter country">
                </div>
                <div class="col-md-3">
                    <label for="guest_zipcode" class="form-label small fw-bold">Zip Code</label>
                    <input type="text" class="form-control" id="guest_zipcode" name="guest_zipcode" value="<?php echo set_value('guest_zipcode', isset($booking->guest_zipcode) ? $booking->guest_zipcode : ''); ?>" placeholder="Enter zip code">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Multiple Room Selection Section -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0"><i class="bi bi-door-open text-info"></i> Room Selection</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-room-btn">
                    <i class="bi bi-plus-circle"></i> Add Room
                </button>
            </div>
            
            <div id="rooms-container">
                <!-- Room selection rows will be populated from existing booking items or default -->
                <?php 
                $existing_items = isset($booking_items) && !empty($booking_items) ? $booking_items : array();
                $room_index = 0;
                
                if (!empty($existing_items)) {
                    // Group items by room_id, dates, and price to show quantity
                    $grouped_items = array();
                    foreach ($existing_items as $item) {
                        $key = $item->room_id . '_' . $item->check_in . '_' . $item->check_out . '_' . $item->price_per_night;
                        if (!isset($grouped_items[$key])) {
                            $grouped_items[$key] = array(
                                'room_id' => $item->room_id,
                                'room_name' => $item->room_name,
                                'price_per_night' => $item->price_per_night,
                                'check_in' => $item->check_in,
                                'check_out' => $item->check_out,
                                'guests' => $booking->guests, // Default to booking guests
                                'quantity' => 0
                            );
                        }
                        $grouped_items[$key]['quantity']++;
                    }
                    
                    foreach ($grouped_items as $grouped_item):
                ?>
                <div class="room-row mb-3 p-3 border rounded" data-room-index="<?php echo $room_index; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Select Room *</label>
                            <select class="form-select room-select" name="room_selections[<?php echo $room_index; ?>][room_id]" data-index="<?php echo $room_index; ?>" required>
                                <option value="">-- Choose a room --</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room->id; ?>" data-price="<?php echo $room->price; ?>" data-name="<?php echo htmlspecialchars($room->room_name); ?>" <?php echo ($grouped_item['room_id'] == $room->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($room->room_name . ' (' . $room->room_type . ') - ₱' . number_format($room->price, 2) . '/night'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Check-In Date *</label>
                            <input type="date" class="form-control room-checkin" name="room_selections[<?php echo $room_index; ?>][check_in]" value="<?php echo $grouped_item['check_in']; ?>" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Check-Out Date *</label>
                            <input type="date" class="form-control room-checkout" name="room_selections[<?php echo $room_index; ?>][check_out]" value="<?php echo $grouped_item['check_out']; ?>" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Guests</label>
                            <input type="number" class="form-control room-guests" name="room_selections[<?php echo $room_index; ?>][guests]" value="<?php echo $grouped_item['guests']; ?>" min="1" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Quantity</label>
                            <input type="number" class="form-control room-quantity" name="room_selections[<?php echo $room_index; ?>][quantity]" value="<?php echo $grouped_item['quantity']; ?>" min="1" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Price/Night</label>
                            <input type="text" class="form-control room-price-display" readonly value="₱<?php echo number_format($grouped_item['price_per_night'], 2); ?>" style="background-color: #f8f9fa; font-size: 0.85rem;">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Subtotal</label>
                            <?php 
                            // Calculate subtotal for this grouped item
                            $item_check_in = new DateTime($grouped_item['check_in']);
                            $item_check_out = new DateTime($grouped_item['check_out']);
                            $item_nights = $item_check_in->diff($item_check_out)->days;
                            $item_nights = max($item_nights, 1);
                            $item_subtotal = $grouped_item['price_per_night'] * $item_nights * $grouped_item['quantity'];
                            ?>
                            <input type="text" class="form-control room-subtotal" readonly value="₱<?php echo number_format($item_subtotal, 2); ?>" style="background-color: #f8f9fa; font-weight: bold; font-size: 0.85rem;">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger remove-room-btn w-100">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php 
                    $room_index++;
                    endforeach;
                } else {
                    // Default single room row
                ?>
                <div class="room-row mb-3 p-3 border rounded" data-room-index="0">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Select Room *</label>
                            <select class="form-select room-select" name="room_selections[0][room_id]" data-index="0" required>
                                <option value="">-- Choose a room --</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room->id; ?>" data-price="<?php echo $room->price; ?>" data-name="<?php echo htmlspecialchars($room->room_name); ?>" <?php echo ($booking->room_id == $room->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($room->room_name . ' (' . $room->room_type . ') - ₱' . number_format($room->price, 2) . '/night'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Check-In Date *</label>
                            <input type="date" class="form-control room-checkin" name="room_selections[0][check_in]" value="<?php echo $booking->check_in; ?>" data-index="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Check-Out Date *</label>
                            <input type="date" class="form-control room-checkout" name="room_selections[0][check_out]" value="<?php echo $booking->check_out; ?>" data-index="0" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Guests</label>
                            <input type="number" class="form-control room-guests" name="room_selections[0][guests]" value="<?php echo $booking->guests; ?>" min="1" data-index="0" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Quantity</label>
                            <input type="number" class="form-control room-quantity" name="room_selections[0][quantity]" value="<?php echo isset($booking->rooms) ? $booking->rooms : 1; ?>" min="1" data-index="0" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Price/Night</label>
                            <input type="text" class="form-control room-price-display" readonly value="₱0.00" style="background-color: #f8f9fa; font-size: 0.85rem;">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">Subtotal</label>
                            <input type="text" class="form-control room-subtotal" readonly value="₱0.00" style="background-color: #f8f9fa; font-weight: bold; font-size: 0.85rem;">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger remove-room-btn w-100" style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <!-- Booking Summary - Inline Row -->
    <div class="card mb-3 shadow-sm border-primary">
        <div class="card-body bg-light">
            <h6 class="card-title mb-3"><i class="bi bi-calculator text-primary"></i> Booking Summary</h6>
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Total Rooms</label>
                    <div class="form-control bg-white" id="total-rooms-display">0</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Total Guests</label>
                    <div class="form-control bg-white" id="total-guests-display">0</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Earliest Check-In</label>
                    <div class="form-control bg-white" id="earliest-checkin-display">-</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Latest Check-Out</label>
                    <div class="form-control bg-white" id="latest-checkout-display">-</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Total Amount</label>
                    <div class="form-control bg-white fw-bold text-success fs-5" id="total-amount-display">₱<?php echo isset($booking->total_amount) ? number_format($booking->total_amount, 2) : '0.00'; ?></div>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <label for="status" class="form-label small fw-bold">Booking Status *</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="pending" <?php echo set_select('status', 'pending', $booking->status == 'pending'); ?>>⏳ Pending</option>
                        <option value="confirmed" <?php echo set_select('status', 'confirmed', $booking->status == 'confirmed'); ?>>✅ Confirmed</option>
                        <option value="cancelled" <?php echo set_select('status', 'cancelled', $booking->status == 'cancelled'); ?>>❌ Cancelled</option>
                        <option value="completed" <?php echo set_select('status', 'completed', $booking->status == 'completed'); ?>>✔️ Completed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Notes - Inline Row -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h6 class="card-title mb-3"><i class="bi bi-sticky text-warning"></i> Additional Notes</h6>
            <div class="row">
                <div class="col-md-12">
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special requests, payment method, or additional information..."><?php echo set_value('notes', $booking->notes); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden fields for backward compatibility -->
    <input type="hidden" id="room_id" name="room_id" value="<?php echo $booking->room_id; ?>">
    <input type="hidden" id="rooms" name="rooms" value="<?php echo isset($booking->rooms) ? $booking->rooms : 1; ?>">
    <input type="hidden" id="check_in" name="check_in" value="<?php echo $booking->check_in; ?>">
    <input type="hidden" id="check_out" name="check_out" value="<?php echo $booking->check_out; ?>">
    <input type="hidden" id="guests" name="guests" value="<?php echo $booking->guests; ?>">
    
    <!-- Action Buttons -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
        <a href="<?php echo base_url('bookings'); ?>" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle"></i> Update Booking
        </button>
    </div>
    
    <?php echo form_close(); ?>
</div>

<style>
.booking-header-section {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.room-row {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}
.room-row:hover {
    background-color: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.card {
    border: none;
    border-radius: 8px;
}
.card-title {
    color: #495057;
    font-weight: 600;
}
.form-label.small {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let roomIndex = <?php echo $room_index; ?>;
    const roomsContainer = document.getElementById('rooms-container');
    const addRoomBtn = document.getElementById('add-room-btn');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    
    // Set min dates for all existing room rows
    document.querySelectorAll('.room-checkin, .room-checkout').forEach(input => {
        input.setAttribute('min', today);
    });
    
    // Update remove button visibility
    function updateRemoveButtons() {
        const roomRows = document.querySelectorAll('.room-row');
        roomRows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-room-btn');
            if (roomRows.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    // Add new room row
    addRoomBtn.addEventListener('click', function() {
        roomIndex++;
        const newRow = document.createElement('div');
        newRow.className = 'room-row mb-3 p-3 border rounded';
        newRow.setAttribute('data-room-index', roomIndex);
        
        // Get dates from first room row or use defaults
        const firstCheckIn = document.querySelector('.room-checkin')?.value || today;
        const firstCheckOut = document.querySelector('.room-checkout')?.value || '';
        const tomorrow = firstCheckOut || (() => {
            const t = new Date(firstCheckIn);
            t.setDate(t.getDate() + 1);
            return t.toISOString().split('T')[0];
        })();
        
        newRow.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Select Room *</label>
                    <select class="form-select room-select" name="room_selections[${roomIndex}][room_id]" data-index="${roomIndex}" required>
                        <option value="">-- Choose a room --</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room->id; ?>" data-price="<?php echo $room->price; ?>" data-name="<?php echo htmlspecialchars($room->room_name); ?>">
                                <?php echo htmlspecialchars($room->room_name . ' (' . $room->room_type . ') - ₱' . number_format($room->price, 2) . '/night'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Check-In Date *</label>
                    <input type="date" class="form-control room-checkin" name="room_selections[${roomIndex}][check_in]" value="${firstCheckIn}" data-index="${roomIndex}" min="${today}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Check-Out Date *</label>
                    <input type="date" class="form-control room-checkout" name="room_selections[${roomIndex}][check_out]" value="${tomorrow}" data-index="${roomIndex}" min="${today}" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Guests</label>
                    <input type="number" class="form-control room-guests" name="room_selections[${roomIndex}][guests]" value="1" min="1" data-index="${roomIndex}" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Quantity</label>
                    <input type="number" class="form-control room-quantity" name="room_selections[${roomIndex}][quantity]" value="1" min="1" data-index="${roomIndex}" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Price/Night</label>
                    <input type="text" class="form-control room-price-display" readonly value="₱0.00" style="background-color: #f8f9fa; font-size: 0.85rem;">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Subtotal</label>
                    <input type="text" class="form-control room-subtotal" readonly value="₱0.00" style="background-color: #f8f9fa; font-weight: bold; font-size: 0.85rem;">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-room-btn w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        roomsContainer.appendChild(newRow);
        updateRemoveButtons();
        attachRoomRowEvents(newRow);
        
        // Set up date validation for new row
        const newCheckIn = newRow.querySelector('.room-checkin');
        const newCheckOut = newRow.querySelector('.room-checkout');
        if (newCheckIn && newCheckOut) {
            newCheckIn.addEventListener('change', function() {
                if (newCheckOut.value && newCheckOut.value <= newCheckIn.value) {
                    const nextDay = new Date(newCheckIn.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    newCheckOut.value = nextDay.toISOString().split('T')[0];
                    newCheckOut.setAttribute('min', newCheckIn.value);
                }
                calculateRoomSubtotal(newRow);
                calculateTotals();
            });
            newCheckOut.addEventListener('change', function() {
                newCheckOut.setAttribute('min', newCheckIn.value);
                calculateRoomSubtotal(newRow);
                calculateTotals();
            });
        }
    });
    
    // Remove room row
    roomsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-room-btn')) {
            const roomRow = e.target.closest('.room-row');
            roomRow.remove();
            updateRemoveButtons();
            calculateTotals();
        }
    });
    
    // Attach events to room row
    function attachRoomRowEvents(row) {
        const roomSelect = row.querySelector('.room-select');
        const quantityInput = row.querySelector('.room-quantity');
        const guestsInput = row.querySelector('.room-guests');
        const checkInInput = row.querySelector('.room-checkin');
        const checkOutInput = row.querySelector('.room-checkout');
        const priceDisplay = row.querySelector('.room-price-display');
        const subtotalDisplay = row.querySelector('.room-subtotal');
        
        roomSelect.addEventListener('change', function() {
            calculateRoomSubtotal(row);
            calculateTotals();
        });
        
        quantityInput.addEventListener('input', function() {
            calculateRoomSubtotal(row);
            calculateTotals();
        });
        
        guestsInput.addEventListener('input', function() {
            calculateRoomSubtotal(row);
            calculateTotals();
        });
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', function() {
                if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
                    const nextDay = new Date(checkInInput.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkOutInput.value = nextDay.toISOString().split('T')[0];
                }
                checkOutInput.setAttribute('min', checkInInput.value);
                calculateRoomSubtotal(row);
                calculateTotals();
            });
            
            checkOutInput.addEventListener('change', function() {
                checkOutInput.setAttribute('min', checkInInput.value);
                calculateRoomSubtotal(row);
                calculateTotals();
            });
        }
    }
    
    // Calculate subtotal for a single room row
    function calculateRoomSubtotal(row) {
        const roomSelect = row.querySelector('.room-select');
        const quantityInput = row.querySelector('.room-quantity');
        const checkInInput = row.querySelector('.room-checkin');
        const checkOutInput = row.querySelector('.room-checkout');
        const priceDisplay = row.querySelector('.room-price-display');
        const subtotalDisplay = row.querySelector('.room-subtotal');
        
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const quantity = parseInt(quantityInput.value) || 1;
        const checkIn = checkInInput ? checkInInput.value : '';
        const checkOut = checkOutInput ? checkOutInput.value : '';
        
        if (!selectedOption || !selectedOption.value || !checkIn || !checkOut) {
            priceDisplay.value = '₱0.00';
            subtotalDisplay.value = '₱0.00';
            return;
        }
        
        const pricePerNight = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        
        // Calculate nights
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const nights = Math.max(1, Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
        
        const subtotal = pricePerNight * nights * quantity;
        
        priceDisplay.value = '₱' + pricePerNight.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        subtotalDisplay.value = '₱' + subtotal.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Calculate totals
    function calculateTotals() {
        // Calculate total rooms, guests, amount, and date ranges
        let totalRooms = 0;
        let totalGuests = 0;
        let totalAmount = 0;
        let earliestCheckIn = null;
        let latestCheckOut = null;
        
        document.querySelectorAll('.room-row').forEach(row => {
            const roomSelect = row.querySelector('.room-select');
            const quantityInput = row.querySelector('.room-quantity');
            const guestsInput = row.querySelector('.room-guests');
            const checkInInput = row.querySelector('.room-checkin');
            const checkOutInput = row.querySelector('.room-checkout');
            const subtotalDisplay = row.querySelector('.room-subtotal');
            
            if (roomSelect.value && checkInInput && checkOutInput && checkInInput.value && checkOutInput.value) {
                const quantity = parseInt(quantityInput.value) || 1;
                const guests = parseInt(guestsInput.value) || 1;
                totalRooms += quantity;
                totalGuests += guests * quantity;
                
                const subtotalText = subtotalDisplay.value.replace(/[₱,]/g, '');
                totalAmount += parseFloat(subtotalText) || 0;
                
                // Track earliest check-in and latest check-out
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                
                if (!earliestCheckIn || checkIn < earliestCheckIn) {
                    earliestCheckIn = checkIn;
                }
                if (!latestCheckOut || checkOut > latestCheckOut) {
                    latestCheckOut = checkOut;
                }
            }
        });
        
        // Update displays
        document.getElementById('total-rooms-display').textContent = totalRooms;
        document.getElementById('total-guests-display').textContent = totalGuests;
        document.getElementById('earliest-checkin-display').textContent = earliestCheckIn ? new Date(earliestCheckIn).toLocaleDateString() : '-';
        document.getElementById('latest-checkout-display').textContent = latestCheckOut ? new Date(latestCheckOut).toLocaleDateString() : '-';
        document.getElementById('total-amount-display').textContent = '₱' + totalAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        // Update hidden fields for backward compatibility (use first room and earliest/latest dates)
        const firstRoomSelect = document.querySelector('.room-select');
        const firstQuantity = document.querySelector('.room-quantity');
        const roomIdField = document.getElementById('room_id');
        const roomsField = document.getElementById('rooms');
        const checkInField = document.getElementById('check_in');
        const checkOutField = document.getElementById('check_out');
        const guestsField = document.getElementById('guests');
        
        if (firstRoomSelect && firstRoomSelect.value) {
            if (roomIdField) roomIdField.value = firstRoomSelect.value;
            if (roomsField) roomsField.value = totalRooms;
        }
        
        // Update check-in/check-out and guests for backward compatibility
        if (earliestCheckIn && checkInField) {
            checkInField.value = earliestCheckIn;
        }
        if (latestCheckOut && checkOutField) {
            checkOutField.value = latestCheckOut;
        }
        if (guestsField) {
            guestsField.value = totalGuests || 1;
        }
    }
    
    // Attach events to initial room rows
    document.querySelectorAll('.room-row').forEach(row => attachRoomRowEvents(row));
    
    // Customer selection handler
    const customerSelect = document.getElementById('customer_id');
    const guestNameInput = document.getElementById('guest_name');
    const guestEmailInput = document.getElementById('guest_email');
    const guestPhoneInput = document.getElementById('guest_phone');
    const guestAddressInput = document.getElementById('guest_address');
    const guestCityInput = document.getElementById('guest_city');
    const guestProvinceInput = document.getElementById('guest_province');
    const guestCountryInput = document.getElementById('guest_country');
    const guestZipcodeInput = document.getElementById('guest_zipcode');
    
    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption && selectedOption.value && selectedOption.value !== '') {
                // Get customer data from data attributes
                const customerName = selectedOption.getAttribute('data-name') || '';
                const customerEmail = selectedOption.getAttribute('data-email') || '';
                const customerPhone = selectedOption.getAttribute('data-phone') || '';
                const customerAddress = selectedOption.getAttribute('data-address') || '';
                const customerCity = selectedOption.getAttribute('data-city') || '';
                const customerProvince = selectedOption.getAttribute('data-province') || '';
                const customerCountry = selectedOption.getAttribute('data-country') || '';
                const customerZipcode = selectedOption.getAttribute('data-zipcode') || '';
                
                // Fill the guest information fields
                if (guestNameInput && customerName) {
                    guestNameInput.value = customerName;
                }
                if (guestEmailInput && customerEmail) {
                    guestEmailInput.value = customerEmail;
                }
                if (guestPhoneInput && customerPhone) {
                    guestPhoneInput.value = customerPhone;
                }
                if (guestAddressInput && customerAddress) {
                    guestAddressInput.value = customerAddress;
                }
                if (guestCityInput && customerCity) {
                    guestCityInput.value = customerCity;
                }
                if (guestProvinceInput && customerProvince) {
                    guestProvinceInput.value = customerProvince;
                }
                if (guestCountryInput && customerCountry) {
                    guestCountryInput.value = customerCountry;
                }
                if (guestZipcodeInput && customerZipcode) {
                    guestZipcodeInput.value = customerZipcode;
                }
            } else {
                // Clear fields when "Select a customer" is chosen or empty value
                if (guestNameInput) guestNameInput.value = '';
                if (guestEmailInput) guestEmailInput.value = '';
                if (guestPhoneInput) guestPhoneInput.value = '';
                if (guestAddressInput) guestAddressInput.value = '';
                if (guestCityInput) guestCityInput.value = '';
                if (guestProvinceInput) guestProvinceInput.value = '';
                if (guestCountryInput) guestCountryInput.value = '';
                if (guestZipcodeInput) guestZipcodeInput.value = '';
            }
        });
    }
    
    // Initial calculation - wait a bit for DOM to be fully ready
    updateRemoveButtons();
    
    // Calculate subtotals for all existing room rows first
    setTimeout(function() {
        document.querySelectorAll('.room-row').forEach(row => {
            calculateRoomSubtotal(row);
        });
        // Then calculate totals
        calculateTotals();
    }, 100);
});
</script>
