<div class="content-card">
    <!-- Header Section -->
    <div class="booking-header-section mb-4 p-3 p-md-4 bg-gradient text-white rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                <div class="col-6 col-md-3">
                    <label for="guest_city" class="form-label small fw-bold">City</label>
                    <input type="text" class="form-control" id="guest_city" name="guest_city" value="<?php echo set_value('guest_city', isset($booking->guest_city) ? $booking->guest_city : ''); ?>" placeholder="Enter city">
                </div>
                <div class="col-6 col-md-3">
                    <label for="guest_province" class="form-label small fw-bold">Province</label>
                    <input type="text" class="form-control" id="guest_province" name="guest_province" value="<?php echo set_value('guest_province', isset($booking->guest_province) ? $booking->guest_province : ''); ?>" placeholder="Enter province">
                </div>
                <div class="col-6 col-md-3">
                    <label for="guest_country" class="form-label small fw-bold">Country</label>
                    <input type="text" class="form-control" id="guest_country" name="guest_country" value="<?php echo set_value('guest_country', isset($booking->guest_country) ? $booking->guest_country : 'Philippines'); ?>" placeholder="Enter country">
                </div>
                <div class="col-6 col-md-3">
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

            <?php
            $edit_ci_default = !empty($booking->check_in_time) ? $booking->check_in_time : (isset($default_check_in_time) ? $default_check_in_time : '14:00');
            $edit_co_default = !empty($booking->check_out_time) ? $booking->check_out_time : (isset($default_check_out_time) ? $default_check_out_time : '12:00');
            $edit_ci_time = set_value('check_in_time', $edit_ci_default);
            $edit_co_time = set_value('check_out_time', $edit_co_default);
            if (strlen($edit_ci_time) > 5) {
                $edit_ci_time = substr($edit_ci_time, 0, 5);
            }
            if (strlen($edit_co_time) > 5) {
                $edit_co_time = substr($edit_co_time, 0, 5);
            }
            ?>
            
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
                            $item_guests = isset($item->guests) && (int)$item->guests > 0
                                ? (int)$item->guests
                                : (int)$booking->guests;
                            $grouped_items[$key] = array(
                                'room_id' => $item->room_id,
                                'room_name' => $item->room_name,
                                'price_per_night' => $item->price_per_night,
                                'check_in' => $item->check_in,
                                'check_out' => $item->check_out,
                                'guests' => $item_guests > 0 ? $item_guests : 1,
                                'quantity' => 0
                            );
                        }
                        $grouped_items[$key]['quantity']++;
                    }
                    
                    foreach ($grouped_items as $grouped_item):
                        $is_first_room_row = ($room_index === 0);
                        $item_check_in = new DateTime($grouped_item['check_in']);
                        $item_check_out = new DateTime($grouped_item['check_out']);
                        $item_nights = max(1, (int) $item_check_in->diff($item_check_out)->days);
                        $item_subtotal = $grouped_item['price_per_night'] * $item_nights * $grouped_item['quantity'];
                ?>
                <div class="room-row mb-3 p-3 border rounded shadow-sm bg-white" data-room-index="<?php echo $room_index; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-2">
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
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold">Check-In *</label>
                            <input type="datetime-local" class="form-control room-checkin-dt" required
                                value="<?php echo htmlspecialchars($grouped_item['check_in'] . 'T' . $edit_ci_time, ENT_QUOTES, 'UTF-8'); ?>"
                                title="Check-in date and time">
                            <input type="hidden" class="room-checkin" name="room_selections[<?php echo $room_index; ?>][check_in]" value="<?php echo htmlspecialchars($grouped_item['check_in'], ENT_QUOTES, 'UTF-8'); ?>" data-index="<?php echo $room_index; ?>">
                            <input type="hidden" class="room-checkin-time" value="<?php echo htmlspecialchars($edit_ci_time, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php if ($is_first_room_row): ?>id="check_in_time" name="check_in_time"<?php endif; ?>>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold">Check-Out *</label>
                            <input type="datetime-local" class="form-control room-checkout-dt" required
                                value="<?php echo htmlspecialchars($grouped_item['check_out'] . 'T' . $edit_co_time, ENT_QUOTES, 'UTF-8'); ?>"
                                title="Check-out date and time">
                            <input type="hidden" class="room-checkout" name="room_selections[<?php echo $room_index; ?>][check_out]" value="<?php echo htmlspecialchars($grouped_item['check_out'], ENT_QUOTES, 'UTF-8'); ?>" data-index="<?php echo $room_index; ?>">
                            <input type="hidden" class="room-checkout-time" value="<?php echo htmlspecialchars($edit_co_time, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php if ($is_first_room_row): ?>id="check_out_time" name="check_out_time"<?php endif; ?>>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Guests</label>
                            <input type="number" class="form-control room-guests" name="room_selections[<?php echo $room_index; ?>][guests]" value="<?php echo $grouped_item['guests']; ?>" min="1" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Qty</label>
                            <input type="number" class="form-control room-quantity" name="room_selections[<?php echo $room_index; ?>][quantity]" value="<?php echo $grouped_item['quantity']; ?>" min="1" data-index="<?php echo $room_index; ?>" required>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Price/Night</label>
                            <input type="text" class="form-control room-price-display" readonly value="₱<?php echo number_format($grouped_item['price_per_night'], 2); ?>" style="font-size: 0.85rem;">
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg">
                            <label class="form-label small fw-bold">Subtotal</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="text" class="form-control room-subtotal" readonly value="₱<?php echo number_format($item_subtotal, 2); ?>" style="font-weight: bold; font-size: 0.85rem;">
                                <button type="button" class="btn btn-sm btn-danger remove-room-btn" title="Remove room">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $room_index++;
                    endforeach;
                } else {
                    // Default single room row
                ?>
                <div class="room-row mb-3 p-3 border rounded shadow-sm bg-white" data-room-index="0">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-2">
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
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold">Check-In *</label>
                            <input type="datetime-local" class="form-control room-checkin-dt" required
                                value="<?php echo htmlspecialchars($booking->check_in . 'T' . $edit_ci_time, ENT_QUOTES, 'UTF-8'); ?>"
                                title="Check-in date and time">
                            <input type="hidden" class="room-checkin" name="room_selections[0][check_in]" value="<?php echo htmlspecialchars($booking->check_in, ENT_QUOTES, 'UTF-8'); ?>" data-index="0">
                            <input type="hidden" class="room-checkin-time" id="check_in_time" name="check_in_time" value="<?php echo htmlspecialchars($edit_ci_time, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label small fw-bold">Check-Out *</label>
                            <input type="datetime-local" class="form-control room-checkout-dt" required
                                value="<?php echo htmlspecialchars($booking->check_out . 'T' . $edit_co_time, ENT_QUOTES, 'UTF-8'); ?>"
                                title="Check-out date and time">
                            <input type="hidden" class="room-checkout" name="room_selections[0][check_out]" value="<?php echo htmlspecialchars($booking->check_out, ENT_QUOTES, 'UTF-8'); ?>" data-index="0">
                            <input type="hidden" class="room-checkout-time" id="check_out_time" name="check_out_time" value="<?php echo htmlspecialchars($edit_co_time, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Guests</label>
                            <input type="number" class="form-control room-guests" name="room_selections[0][guests]" value="<?php echo $booking->guests; ?>" min="1" data-index="0" required>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Qty</label>
                            <input type="number" class="form-control room-quantity" name="room_selections[0][quantity]" value="<?php echo isset($booking->rooms) ? $booking->rooms : 1; ?>" min="1" data-index="0" required>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                            <label class="form-label small fw-bold">Price/Night</label>
                            <input type="text" class="form-control room-price-display" readonly value="₱0.00" style="font-size: 0.85rem;">
                        </div>
                        <div class="col-6 col-sm-3 col-md-3 col-lg">
                            <label class="form-label small fw-bold">Subtotal</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="text" class="form-control room-subtotal" readonly value="₱0.00" style="font-weight: bold; font-size: 0.85rem;">
                                <button type="button" class="btn btn-sm btn-danger remove-room-btn" style="display: none;" title="Remove room">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Total Rooms</label>
                    <div class="form-control bg-white" id="total-rooms-display">0</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Total Guests</label>
                    <div class="form-control bg-white" id="total-guests-display">0</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Earliest Check-In</label>
                    <div class="form-control bg-white" id="earliest-checkin-display">-</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Latest Check-Out</label>
                    <div class="form-control bg-white" id="latest-checkout-display">-</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Rooms Subtotal</label>
                    <div class="form-control bg-white" id="rooms-subtotal-display">₱0.00</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Total Amount</label>
                    <div class="form-control bg-white fw-bold text-success fs-5" id="total-amount-display">₱<?php echo isset($booking->total_amount) ? number_format($booking->total_amount, 2) : '0.00'; ?></div>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-6 col-md-3">
                    <label for="extra_beds" class="form-label small fw-bold">Extra Beds</label>
                    <input type="number" class="form-control" id="extra_beds" name="extra_beds"
                        value="<?php echo set_value('extra_beds', isset($extra_beds) ? (int) $extra_beds : 0); ?>"
                        min="0" step="1">
                    <small class="text-muted">Number of extra beds</small>
                </div>
                <div class="col-6 col-md-3">
                    <label for="extra_bed_price" class="form-label small fw-bold">Extra Bed Price / Night</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="extra_bed_price" name="extra_bed_price"
                            value="<?php echo set_value('extra_bed_price', isset($extra_bed_price) ? number_format((float) $extra_bed_price, 2, '.', '') : '199.00'); ?>"
                            min="0" step="0.01">
                    </div>
                    <small class="text-muted">Default from Room Settings</small>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold">Extra Bed Total</label>
                    <div class="form-control bg-white fw-bold text-primary" id="extra-bed-total-display">₱0.00</div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label small fw-bold">Booking Status *</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="pending" <?php echo set_select('status', 'pending', $booking->status == 'pending'); ?>>Pending</option>
                        <option value="confirmed" <?php echo set_select('status', 'confirmed', $booking->status == 'confirmed'); ?>>Confirmed</option>
                        <option value="checked_in" <?php echo set_select('status', 'checked_in', $booking->status == 'checked_in'); ?>>Checked In</option>
                        <option value="checked_out" <?php echo set_select('status', 'checked_out', $booking->status == 'checked_out'); ?>>Checked Out</option>
                        <option value="completed" <?php echo set_select('status', 'completed', $booking->status == 'completed'); ?>>Completed</option>
                        <option value="cancelled" <?php echo set_select('status', 'cancelled', $booking->status == 'cancelled'); ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Guests Names List -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="card-title mb-0"><i class="bi bi-people text-success"></i> Guests Names List</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-guest-row-btn">
                    <i class="bi bi-plus-circle"></i> Add Row
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 no-datatables" id="guest-names-table">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 180px;">Full Name</th>
                            <th style="width: 90px;">Age</th>
                            <th style="width: 120px;">Gender</th>
                            <th style="width: 160px;">DOB</th>
                            <th style="min-width: 140px;">Contact No.</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody id="guest-names-body">
                        <?php
                        $existing_guests = !empty($booking_guests) ? $booking_guests : array();
                        if (empty($existing_guests)) {
                            $existing_guests = array((object) array(
                                'full_name' => '',
                                'age' => '',
                                'gender' => '',
                                'date_of_birth' => '',
                                'contact_no' => ''
                            ));
                        }
                        foreach ($existing_guests as $g_index => $guest_row):
                            $g_gender = isset($guest_row->gender) ? $guest_row->gender : '';
                            $g_dob = !empty($guest_row->date_of_birth) ? $guest_row->date_of_birth : '';
                            $g_age = isset($guest_row->age) && $guest_row->age !== null && $guest_row->age !== '' ? (int) $guest_row->age : '';
                        ?>
                        <tr class="guest-name-row" data-guest-index="<?php echo $g_index; ?>">
                            <td data-label="Full Name">
                                <input type="text" class="form-control form-control-sm guest-full-name"
                                    name="guest_names[<?php echo $g_index; ?>][full_name]"
                                    value="<?php echo htmlspecialchars(isset($guest_row->full_name) ? $guest_row->full_name : ''); ?>"
                                    placeholder="Full name">
                            </td>
                            <td data-label="Age">
                                <input type="number" class="form-control form-control-sm guest-age"
                                    name="guest_names[<?php echo $g_index; ?>][age]"
                                    value="<?php echo $g_age !== '' ? $g_age : ''; ?>"
                                    min="0" max="120" placeholder="Age">
                            </td>
                            <td data-label="Gender">
                                <select class="form-select form-select-sm guest-gender" name="guest_names[<?php echo $g_index; ?>][gender]">
                                    <option value="">--</option>
                                    <option value="Male" <?php echo $g_gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $g_gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $g_gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </td>
                            <td data-label="Date of Birth">
                                <input type="date" class="form-control form-control-sm guest-dob"
                                    name="guest_names[<?php echo $g_index; ?>][date_of_birth]"
                                    value="<?php echo htmlspecialchars($g_dob); ?>">
                            </td>
                            <td data-label="Contact No.">
                                <input type="text" class="form-control form-control-sm guest-contact"
                                    name="guest_names[<?php echo $g_index; ?>][contact_no]"
                                    value="<?php echo htmlspecialchars(isset($guest_row->contact_no) ? $guest_row->contact_no : ''); ?>"
                                    placeholder="Contact no.">
                            </td>
                            <td data-label="Action" class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-guest-row-btn" title="Remove">
                                    <i class="bi bi-trash"></i> <span class="d-md-none ms-1">Remove Guest</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted d-block mt-2">List each guest staying under this booking. Rows with an empty name are ignored on save.</small>
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
.room-price-display,
.room-subtotal {
    background-color: #f8f9fa;
}
body.dark-mode .room-row {
    background-color: #0f172a;
}
body.dark-mode .room-row:hover {
    background-color: #1e293b;
}
body.dark-mode .card-title {
    color: #e2e8f0;
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
@media (max-width: 767.98px) {
    #guest-names-table,
    #guest-names-table thead,
    #guest-names-table tbody,
    #guest-names-table tr.guest-name-row,
    #guest-names-table tr.guest-name-row > td {
        display: block;
    }
    #guest-names-table thead {
        display: none !important;
    }
    #guest-names-table {
        border: none !important;
    }
    #guest-names-table tbody {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    #guest-names-table tr.guest-name-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem;
        padding: 0.875rem;
        margin-bottom: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    #guest-names-table tr.guest-name-row > td {
        border: none !important;
        padding: 0 !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Full Name"] {
        flex: 1 1 100%;
        width: 100% !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Age"] {
        flex: 1 1 calc(50% - 0.25rem);
        width: calc(50% - 0.25rem) !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Gender"] {
        flex: 1 1 calc(50% - 0.25rem);
        width: calc(50% - 0.25rem) !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Date of Birth"] {
        flex: 1 1 100%;
        width: 100% !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Contact No."] {
        flex: 1 1 100%;
        width: 100% !important;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Action"] {
        flex: 1 1 100%;
        width: 100% !important;
        padding-top: 0.5rem !important;
        margin-top: 0.25rem;
        border-top: 1px dashed #e2e8f0 !important;
        text-align: right;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Action"] .remove-guest-row-btn {
        width: 100%;
        padding: 0.45rem;
        font-size: 0.8125rem;
    }
    #guest-names-table tr.guest-name-row > td[data-label]::before {
        content: attr(data-label);
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    #guest-names-table tr.guest-name-row > td[data-label="Action"]::before {
        display: none;
    }
}
body.dark-mode #guest-names-table tr.guest-name-row {
    background: #0f172a;
    border-color: #334155 !important;
}
body.dark-mode #guest-names-table tr.guest-name-row > td[data-label]::before {
    color: #94a3b8;
}
body.dark-mode #guest-names-table tr.guest-name-row > td[data-label="Action"] {
    border-top-color: #334155 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let roomIndex = <?php echo $room_index; ?>;
    const roomsContainer = document.getElementById('rooms-container');
    const addRoomBtn = document.getElementById('add-room-btn');
    const defaultCheckInTime = <?php echo json_encode($edit_ci_time); ?>;
    const defaultCheckOutTime = <?php echo json_encode($edit_co_time); ?>;

    function toDatetimeLocal(dateStr, timeStr) {
        if (!dateStr) return '';
        const t = (timeStr || '00:00').toString().substring(0, 5);
        return dateStr + 'T' + t;
    }

    function splitDatetimeLocal(value) {
        if (!value || String(value).indexOf('T') === -1) {
            return { date: '', time: '' };
        }
        const parts = String(value).split('T');
        return {
            date: parts[0] || '',
            time: (parts[1] || '').substring(0, 5)
        };
    }

    function applyDatetimeToHidden(row) {
        if (!row) return;
        const ciDt = row.querySelector('.room-checkin-dt');
        const coDt = row.querySelector('.room-checkout-dt');
        const ci = row.querySelector('.room-checkin');
        const co = row.querySelector('.room-checkout');
        const ciT = row.querySelector('.room-checkin-time');
        const coT = row.querySelector('.room-checkout-time');
        if (ciDt && ci) {
            const s = splitDatetimeLocal(ciDt.value);
            ci.value = s.date;
            if (ciT && s.time) ciT.value = s.time;
        }
        if (coDt && co) {
            const s2 = splitDatetimeLocal(coDt.value);
            co.value = s2.date;
            if (coT && s2.time) coT.value = s2.time;
        }
    }

    function syncBookingTimeFieldNames() {
        document.querySelectorAll('.room-checkin-time, .room-checkout-time').forEach(function(el) {
            el.removeAttribute('name');
            el.removeAttribute('id');
        });
        const firstRow = document.querySelector('.room-row');
        if (!firstRow) return;
        const ci = firstRow.querySelector('.room-checkin-time');
        const co = firstRow.querySelector('.room-checkout-time');
        if (ci) {
            ci.name = 'check_in_time';
            ci.id = 'check_in_time';
        }
        if (co) {
            co.name = 'check_out_time';
            co.id = 'check_out_time';
        }
    }

    function syncBookingTimesFrom(sourceDt, isCheckIn) {
        if (!sourceDt || !sourceDt.value) return;
        const parts = splitDatetimeLocal(sourceDt.value);
        if (!parts.time) return;
        const dtSelector = isCheckIn ? '.room-checkin-dt' : '.room-checkout-dt';
        const hiddenTimeSelector = isCheckIn ? '.room-checkin-time' : '.room-checkout-time';
        document.querySelectorAll(dtSelector).forEach(function(el) {
            if (el === sourceDt) return;
            const existing = splitDatetimeLocal(el.value);
            const datePart = existing.date || parts.date;
            if (datePart) {
                el.value = toDatetimeLocal(datePart, parts.time);
            }
            applyDatetimeToHidden(el.closest('.room-row'));
        });
        document.querySelectorAll(hiddenTimeSelector).forEach(function(el) {
            el.value = parts.time;
        });
    }

    // Guests Names List
    let guestIndex = <?php echo !empty($existing_guests) ? count($existing_guests) : 1; ?>;
    const guestNamesBody = document.getElementById('guest-names-body');
    const addGuestRowBtn = document.getElementById('add-guest-row-btn');

    function calcAgeFromDob(dobValue) {
        if (!dobValue) return '';
        const dob = new Date(dobValue + 'T12:00:00');
        if (isNaN(dob.getTime())) return '';
        const todayDate = new Date();
        let age = todayDate.getFullYear() - dob.getFullYear();
        const m = todayDate.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && todayDate.getDate() < dob.getDate())) {
            age--;
        }
        return age >= 0 ? age : '';
    }

    function updateGuestRemoveButtons() {
        const rows = guestNamesBody ? guestNamesBody.querySelectorAll('.guest-name-row') : [];
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-guest-row-btn');
            if (btn) {
                btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
            }
        });
    }

    function attachGuestRowEvents(row) {
        const dobInput = row.querySelector('.guest-dob');
        const ageInput = row.querySelector('.guest-age');
        if (dobInput && ageInput) {
            dobInput.addEventListener('change', function() {
                const age = calcAgeFromDob(dobInput.value);
                if (age !== '') {
                    ageInput.value = age;
                }
            });
        }
    }

    function createGuestRow(index) {
        const tr = document.createElement('tr');
        tr.className = 'guest-name-row';
        tr.setAttribute('data-guest-index', index);
        tr.innerHTML = `
            <td data-label="Full Name">
                <input type="text" class="form-control form-control-sm guest-full-name"
                    name="guest_names[${index}][full_name]" placeholder="Full name">
            </td>
            <td data-label="Age">
                <input type="number" class="form-control form-control-sm guest-age"
                    name="guest_names[${index}][age]" min="0" max="120" placeholder="Age">
            </td>
            <td data-label="Gender">
                <select class="form-select form-select-sm guest-gender" name="guest_names[${index}][gender]">
                    <option value="">--</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td data-label="Date of Birth">
                <input type="date" class="form-control form-control-sm guest-dob"
                    name="guest_names[${index}][date_of_birth]">
            </td>
            <td data-label="Contact No.">
                <input type="text" class="form-control form-control-sm guest-contact"
                    name="guest_names[${index}][contact_no]" placeholder="Contact no.">
            </td>
            <td data-label="Action" class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-guest-row-btn" title="Remove">
                    <i class="bi bi-trash"></i> <span class="d-md-none ms-1">Remove Guest</span>
                </button>
            </td>
        `;
        return tr;
    }

    if (addGuestRowBtn && guestNamesBody) {
        addGuestRowBtn.addEventListener('click', function() {
            const row = createGuestRow(guestIndex++);
            guestNamesBody.appendChild(row);
            attachGuestRowEvents(row);
            updateGuestRemoveButtons();
        });

        guestNamesBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-guest-row-btn')) {
                const rows = guestNamesBody.querySelectorAll('.guest-name-row');
                if (rows.length <= 1) {
                    return;
                }
                e.target.closest('.guest-name-row').remove();
                updateGuestRemoveButtons();
            }
        });

        guestNamesBody.querySelectorAll('.guest-name-row').forEach(attachGuestRowEvents);
        updateGuestRemoveButtons();
    }
    
    const today = typeof bodareTodayYmd === 'function' ? bodareTodayYmd() : (typeof bodareFormatDateLocal === 'function' ? bodareFormatDateLocal(new Date()) : new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' }));
    const ymdFromDate = function(d) {
        if (typeof bodareFormatDateLocal === 'function') return bodareFormatDateLocal(d);
        if (typeof formatDateLocal === 'function') return formatDateLocal(d);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    };
    
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
        newRow.className = 'room-row mb-3 p-3 border rounded shadow-sm bg-white';
        newRow.setAttribute('data-room-index', roomIndex);
        
        // Get dates/times from first room row or use defaults
        document.querySelectorAll('.room-row').forEach(applyDatetimeToHidden);
        const firstCheckIn = document.querySelector('.room-checkin')?.value || today;
        const firstCheckOut = document.querySelector('.room-checkout')?.value || '';
        const firstCheckInTime = document.querySelector('.room-checkin-time')?.value || defaultCheckInTime;
        const firstCheckOutTime = document.querySelector('.room-checkout-time')?.value || defaultCheckOutTime;
        const tomorrow = firstCheckOut || (() => {
            const t = new Date(firstCheckIn);
            t.setDate(t.getDate() + 1);
            return ymdFromDate(t);
        })();
        
        newRow.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-2">
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
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label small fw-bold">Check-In *</label>
                    <input type="datetime-local" class="form-control room-checkin-dt" value="${toDatetimeLocal(firstCheckIn, firstCheckInTime)}" required title="Check-in date and time">
                    <input type="hidden" class="room-checkin" name="room_selections[${roomIndex}][check_in]" value="${firstCheckIn}" data-index="${roomIndex}">
                    <input type="hidden" class="room-checkin-time" value="${firstCheckInTime}">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label small fw-bold">Check-Out *</label>
                    <input type="datetime-local" class="form-control room-checkout-dt" value="${toDatetimeLocal(tomorrow, firstCheckOutTime)}" required title="Check-out date and time">
                    <input type="hidden" class="room-checkout" name="room_selections[${roomIndex}][check_out]" value="${tomorrow}" data-index="${roomIndex}">
                    <input type="hidden" class="room-checkout-time" value="${firstCheckOutTime}">
                </div>
                <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                    <label class="form-label small fw-bold">Guests</label>
                    <input type="number" class="form-control room-guests" name="room_selections[${roomIndex}][guests]" value="1" min="1" data-index="${roomIndex}" required>
                </div>
                <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                    <label class="form-label small fw-bold">Qty</label>
                    <input type="number" class="form-control room-quantity" name="room_selections[${roomIndex}][quantity]" value="1" min="1" data-index="${roomIndex}" required>
                </div>
                <div class="col-6 col-sm-3 col-md-3 col-lg-1">
                    <label class="form-label small fw-bold">Price/Night</label>
                    <input type="text" class="form-control room-price-display" readonly value="₱0.00" style="font-size: 0.85rem;">
                </div>
                <div class="col-6 col-sm-3 col-md-3 col-lg">
                    <label class="form-label small fw-bold">Subtotal</label>
                    <div class="d-flex gap-1 align-items-center">
                        <input type="text" class="form-control room-subtotal" readonly value="₱0.00" style="font-weight: bold; font-size: 0.85rem;">
                        <button type="button" class="btn btn-sm btn-danger remove-room-btn" title="Remove room">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        roomsContainer.appendChild(newRow);
        updateRemoveButtons();
        syncBookingTimeFieldNames();
        attachRoomRowEvents(newRow);
        applyDatetimeToHidden(newRow);
        calculateRoomSubtotal(newRow);
        calculateTotals();
    });
    
    // Remove room row
    roomsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-room-btn')) {
            const roomRow = e.target.closest('.room-row');
            roomRow.remove();
            updateRemoveButtons();
            syncBookingTimeFieldNames();
            calculateTotals();
        }
    });
    
    // Attach events to room row
    function attachRoomRowEvents(row) {
        const roomSelect = row.querySelector('.room-select');
        const quantityInput = row.querySelector('.room-quantity');
        const guestsInput = row.querySelector('.room-guests');
        const checkInDt = row.querySelector('.room-checkin-dt');
        const checkOutDt = row.querySelector('.room-checkout-dt');
        const checkInInput = row.querySelector('.room-checkin');
        const checkOutInput = row.querySelector('.room-checkout');
        const checkOutTimeInput = row.querySelector('.room-checkout-time');

        function handleCheckInChange() {
            applyDatetimeToHidden(row);
            syncBookingTimesFrom(checkInDt, true);
            if (checkInInput && checkOutInput && checkOutInput.value && checkOutInput.value <= checkInInput.value) {
                const nextDay = new Date(checkInInput.value + 'T12:00:00');
                nextDay.setDate(nextDay.getDate() + 1);
                const newDate = ymdFromDate(nextDay);
                const coTime = (checkOutTimeInput && checkOutTimeInput.value) || defaultCheckOutTime;
                checkOutInput.value = newDate;
                if (checkOutDt) checkOutDt.value = toDatetimeLocal(newDate, coTime);
                applyDatetimeToHidden(row);
            }
            if (checkInDt && checkOutDt && checkInDt.value) {
                const ciParts = splitDatetimeLocal(checkInDt.value);
                if (ciParts.date) checkOutDt.min = ciParts.date + 'T00:00';
            }
            calculateRoomSubtotal(row);
            calculateTotals();
        }

        function handleCheckOutChange() {
            applyDatetimeToHidden(row);
            syncBookingTimesFrom(checkOutDt, false);
            if (checkInDt && checkOutDt && checkInDt.value) {
                const ciParts = splitDatetimeLocal(checkInDt.value);
                if (ciParts.date) checkOutDt.min = ciParts.date + 'T00:00';
            }
            calculateRoomSubtotal(row);
            calculateTotals();
        }

        if (checkInDt) {
            checkInDt.addEventListener('change', handleCheckInChange);
            checkInDt.addEventListener('input', handleCheckInChange);
        }
        if (checkOutDt) {
            checkOutDt.addEventListener('change', handleCheckOutChange);
            checkOutDt.addEventListener('input', handleCheckOutChange);
        }
        
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

        applyDatetimeToHidden(row);
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
        let roomsSubtotal = 0;
        let earliestCheckIn = null;
        let latestCheckOut = null;
        let maxNights = 1;
        
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
                roomsSubtotal += parseFloat(subtotalText) || 0;
                
                // Track earliest check-in and latest check-out
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                
                if (!earliestCheckIn || checkIn < earliestCheckIn) {
                    earliestCheckIn = checkIn;
                }
                if (!latestCheckOut || checkOut > latestCheckOut) {
                    latestCheckOut = checkOut;
                }

                const nights = Math.max(1, Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24)));
                if (nights > maxNights) {
                    maxNights = nights;
                }
            }
        });

        const extraBeds = parseInt(document.getElementById('extra_beds')?.value, 10) || 0;
        const extraBedPrice = parseFloat(document.getElementById('extra_bed_price')?.value) || 0;
        const extraBedTotal = extraBeds * extraBedPrice * maxNights;
        const totalAmount = roomsSubtotal + extraBedTotal;
        
        // Update displays
        document.getElementById('total-rooms-display').textContent = totalRooms;
        document.getElementById('total-guests-display').textContent = totalGuests;
        document.getElementById('earliest-checkin-display').textContent = earliestCheckIn ? new Date(earliestCheckIn + 'T12:00:00').toLocaleDateString() : '-';
        document.getElementById('latest-checkout-display').textContent = latestCheckOut ? new Date(latestCheckOut + 'T12:00:00').toLocaleDateString() : '-';
        const roomsSubtotalEl = document.getElementById('rooms-subtotal-display');
        if (roomsSubtotalEl) {
            roomsSubtotalEl.textContent = '₱' + roomsSubtotal.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        const extraBedTotalEl = document.getElementById('extra-bed-total-display');
        if (extraBedTotalEl) {
            extraBedTotalEl.textContent = '₱' + extraBedTotal.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
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
    document.querySelectorAll('.room-row').forEach(function(row) {
        attachRoomRowEvents(row);
        applyDatetimeToHidden(row);
    });
    syncBookingTimeFieldNames();

    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function() {
            document.querySelectorAll('.room-row').forEach(applyDatetimeToHidden);
            syncBookingTimeFieldNames();
        });
    }

    ['extra_beds', 'extra_bed_price'].forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', calculateTotals);
            field.addEventListener('change', calculateTotals);
        }
    });
    
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
