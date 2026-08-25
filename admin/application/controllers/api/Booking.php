<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Booking_model');
        $this->load->model('Booking_item_model');
        $this->load->model('Room_model');
        $this->load->model('Room_image_model');
        $this->load->model('User_model');
        $this->load->model('Booking_settings_model');
        $this->load->library('form_validation');
        header('Content-Type: application/json');
    }
    
    /**
     * Check room availability
     */
    public function check_availability() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($this->input->method() === 'options') {
            exit;
        }
        
        $check_in = $this->input->get_post('check_in');
        $check_out = $this->input->get_post('check_out');
        $guests = $this->input->get_post('guests');
        
        if (!$check_in || !$check_out) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Check-in and check-out dates are required'
            ]);
            return;
        }
        
        // Validate dates
        $check_in_date = DateTime::createFromFormat('Y-m-d', $check_in);
        $check_out_date = DateTime::createFromFormat('Y-m-d', $check_out);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if (!$check_in_date || !$check_out_date) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        if ($check_in_date < $today) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Check-in date cannot be in the past'
            ]);
            return;
        }
        
        if ($check_out_date <= $check_in_date) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Check-out date must be after check-in date'
            ]);
            return;
        }
        
        // Get available rooms
        $available_rooms = $this->Booking_model->get_available_rooms($check_in, $check_out, $guests);
        
        echo json_encode([
            'success' => true,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'rooms' => $available_rooms
        ]);
    }
    
    /**
     * Get room details
     */
    public function get_room($id = null) {
        header('Access-Control-Allow-Origin: *');
        
        if (!$id) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Room ID is required']);
            return;
        }
        
        $room = $this->Room_model->get_room($id);
        
        if ($room) {
            echo json_encode([
                'success' => true,
                'room' => $room
            ]);
        } else {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'Room not found'
            ]);
        }
    }
    
    /**
     * Get room by room_code
     */
    public function get_room_by_code($room_code = null) {
        header('Access-Control-Allow-Origin: *');
        
        if (!$room_code) {
            $room_code = $this->input->get_post('room_code');
        }
        
        if (!$room_code) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Room code is required']);
            return;
        }
        
        $room = $this->Room_model->get_room_by_code($room_code);
        
        if ($room) {
            // Add images to room
            $room->images = $this->Room_image_model->get_room_images($room->id);
            $room->primary_image = $this->Room_image_model->get_primary_image($room->id);
            
            echo json_encode([
                'success' => true,
                'room' => $room
            ]);
        } else {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'Room not found'
            ]);
        }
    }
    
    /**
     * List all rooms
     */
    public function get_rooms() {
        header('Access-Control-Allow-Origin: *');
        
        $rooms = $this->Room_model->get_active_rooms();
        
        // Add images to each room
        foreach ($rooms as $room) {
            $room->images = $this->Room_image_model->get_room_images($room->id);
            $room->primary_image = $this->Room_image_model->get_primary_image($room->id);
        }
        
        echo json_encode([
            'success' => true,
            'rooms' => $rooms
        ]);
    }
    
    /**
     * Create booking
     */
    public function create() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($this->input->method() === 'options') {
            exit;
        }
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $this->input->post();
        }
        
        // Validation - check if room_selections array is provided (new format) or single room (old format)
        $room_selections = isset($data['room_selections']) && is_array($data['room_selections']) ? $data['room_selections'] : null;
        $room_id = isset($data['room_id']) ? $data['room_id'] : null;
        
        // Validate basic guest information
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('guest_name', 'Guest Name', 'required|trim');
        $this->form_validation->set_rules('guest_email', 'Guest Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('guest_phone', 'Guest Phone', 'required|trim');

        $payment_method = isset($data['payment_method']) ? strtolower(trim((string) $data['payment_method'])) : 'pay_at_hotel';
        $allowed_payment_methods = array('pay_at_hotel', 'card', 'gcash');
        if (!in_array($payment_method, $allowed_payment_methods, true)) {
            $payment_method = 'pay_at_hotel';
        }

        // Fail fast for GCash before room holds / availability work
        if ($payment_method === 'gcash') {
            $this->load->library('paymongo_service');
            if (!$this->paymongo_service->is_ready()) {
                $this->output->set_status_header(503);
                echo json_encode([
                    'success' => false,
                    'message' => 'Online GCash (PayMongo) is not configured yet. Please choose Pay at the Hotel, or ask the administrator to enable PayMongo in Booking Settings.'
                ]);
                return;
            }
        }
        
        // If room_selections is provided, validate it; otherwise validate old format
        if ($room_selections && !empty($room_selections)) {
            // New format: validate each room selection
            foreach ($room_selections as $index => $selection) {
                if (empty($selection['room_id']) || empty($selection['quantity']) || empty($selection['check_in']) || empty($selection['check_out'])) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Please provide room_id, quantity, check_in, and check_out for all room selections.'
                    ]);
                    return;
                }
            }
        } else {
            // Old format: validate single room
            $this->form_validation->set_rules('room_id', 'Room', 'required|integer');
            $this->form_validation->set_rules('check_in', 'Check In Date', 'required');
            $this->form_validation->set_rules('check_out', 'Check Out Date', 'required');
            $this->form_validation->set_rules('guests', 'Number of Guests', 'required|integer');
            $this->form_validation->set_rules('rooms', 'Number of Rooms', 'integer|greater_than[0]');
        }
        
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please check your booking details and ensure all information is entered correctly.',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Process room selections (new format) or fallback to old format
        $selected_rooms = array();
        $total_amount = 0;
        $total_rooms_count = 0;
        $first_room_id = null;
        $check_in = null;
        $check_out = null;
        $guests = 0;
        $extra_services = array();
        
        if ($room_selections && !empty($room_selections)) {
            // New format: multiple room selections with individual dates and guests
            foreach ($room_selections as $selection) {
                if (!empty($selection['room_id']) && !empty($selection['quantity']) && !empty($selection['check_in']) && !empty($selection['check_out'])) {
                    $sel_room_id = (int)$selection['room_id'];
                    $sel_quantity = (int)$selection['quantity'];
                    $sel_check_in = $this->normalize_booking_date($selection['check_in']);
                    $sel_check_out = $this->normalize_booking_date($selection['check_out']);
                    $sel_guests = isset($selection['guests']) ? (int)$selection['guests'] : 1;
                    if ($sel_guests < 1) {
                        $sel_guests = 1;
                    }

                    if (!$sel_check_in || !$sel_check_out) {
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid check-in or check-out date for one of the rooms.'
                        ]);
                        return;
                    }
                    
                    // Validate dates (compare calendar dates only)
                    if ($sel_check_out <= $sel_check_in) {
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Check-out date must be after check-in date for one of the rooms.'
                        ]);
                        return;
                    }
                    
                    // Check availability for this specific room and dates
                    if (!$this->Booking_model->check_room_availability($sel_room_id, $sel_check_in, $sel_check_out, null, $sel_quantity)) {
                        $room_info = $this->Room_model->get_room($sel_room_id);
                        $room_name = $room_info ? $room_info->room_name : 'Unknown';
                        $this->output->set_status_header(400);
                        echo json_encode([
                            'success' => false,
                            'message' => "Room '{$room_name}' is not available for the selected dates ({$sel_check_in} to {$sel_check_out}). Not enough rooms available."
                        ]);
                        return;
                    }
                    
                    $room = $this->Room_model->get_room($sel_room_id);
                    if (!$room) {
                        $this->output->set_status_header(404);
                        echo json_encode([
                            'success' => false,
                            'message' => 'One of the selected rooms was not found.'
                        ]);
                        return;
                    }
                    
                    if ($first_room_id === null) {
                        $first_room_id = $sel_room_id;
                        // Use first room's dates for main booking record
                        $check_in = $sel_check_in;
                        $check_out = $sel_check_out;
                    }

                    // Accumulate total guests across all room lines
                    $guests += ($sel_guests * $sel_quantity);
                    
                    // Calculate nights for this specific room selection
                    $check_in_date = new DateTime($sel_check_in);
                    $check_out_date = new DateTime($sel_check_out);
                    $nights = $check_in_date->diff($check_out_date)->days;
                    $nights = max($nights, 1);
                    
                    // Calculate subtotal for this room selection
                    $base_total = $this->Booking_model->calculate_total_amount($sel_room_id, $sel_check_in, $sel_check_out, $sel_guests);
                    $subtotal = $base_total * $sel_quantity;

                    $extra_beds = isset($selection['extra_beds']) ? max(0, (int)$selection['extra_beds']) : 0;
                    $extra_bed_price = isset($selection['extra_bed_price']) ? max(0, floatval($selection['extra_bed_price'])) : 0;
                    if ($extra_beds > 0 && $extra_bed_price <= 0) {
                        $extra_bed_price = $this->get_extra_bed_price_setting();
                    }
                    $extra_bed_total = 0;
                    if ($extra_beds > 0 && $extra_bed_price > 0) {
                        $extra_bed_total = $extra_beds * $extra_bed_price * $nights;
                    }
                    
                    $selected_rooms[] = array(
                        'room_id' => $sel_room_id,
                        'room_name' => $room->room_name,
                        'quantity' => $sel_quantity,
                        'check_in' => $sel_check_in,
                        'check_out' => $sel_check_out,
                        'guests' => $sel_guests,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $subtotal,
                        'extra_beds' => $extra_beds,
                        'extra_bed_price' => $extra_bed_price,
                        'extra_bed_total' => $extra_bed_total
                    );
                    
                    $total_amount += $subtotal;
                    $total_rooms_count += $sel_quantity;
                }
            }

            // Prefer summed room guests; fall back to request total if needed
            if ($guests < 1 && isset($data['guests']) && (int)$data['guests'] > 0) {
                $guests = (int)$data['guests'];
            }
            if ($guests < 1) {
                $guests = 1;
            }
        } else if ($room_id) {
            // Old format: single room with quantity
            $requested_rooms = isset($data['rooms']) && $data['rooms'] > 0 ? (int)$data['rooms'] : 1;
            $check_in = $this->normalize_booking_date(isset($data['check_in']) ? $data['check_in'] : null);
            $check_out = $this->normalize_booking_date(isset($data['check_out']) ? $data['check_out'] : null);
            $guests = $data['guests'];

            if (!$check_in || !$check_out || $check_out <= $check_in) {
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Check-out date must be after check-in date'
                ]);
                return;
            }

            // Check room availability with number of rooms requested
            $is_available = $this->Booking_model->check_room_availability($room_id, $check_in, $check_out, null, $requested_rooms);
            
            // Debug: Get conflicting bookings to help diagnose the issue
            $conflicting_bookings = $this->Booking_model->get_conflicting_bookings($room_id, $check_in, $check_out);
            
            // Get room information
            $room = $this->Room_model->get_room($room_id);
            $room_name = $room ? $room->room_name : 'Unknown Room';
            
            if (!$is_available) {
                $this->output->set_status_header(400);
                
                // Include debug info in development (remove in production)
                $debug_info = array();
                if (!empty($conflicting_bookings)) {
                    $debug_info['conflicting_bookings'] = array();
                    foreach ($conflicting_bookings as $booking) {
                        $debug_info['conflicting_bookings'][] = array(
                            'id' => $booking->id,
                            'check_in' => $booking->check_in,
                            'check_out' => $booking->check_out,
                            'status' => $booking->status,
                            'room_id' => $booking->room_id,
                            'room_name' => isset($booking->room_name) ? $booking->room_name : $room_name
                        );
                    }
                }
                // Get room availability info
                $available_rooms = isset($room->available_rooms) ? (int)$room->available_rooms : 1;
                $booked_rooms = $this->Booking_model->count_booked_rooms($room_id, $check_in, $check_out);
                $remaining_rooms = $available_rooms - $booked_rooms;
                
                $debug_info['requested_dates'] = array(
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'room_id' => $room_id,
                    'room_name' => $room_name,
                    'requested_rooms' => $requested_rooms,
                    'available_rooms' => $available_rooms,
                    'booked_rooms' => $booked_rooms,
                    'remaining_rooms' => $remaining_rooms
                );
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Sorry, the selected room is not available for your chosen dates. Please select different dates or try another room.',
                    'debug' => $debug_info
                ]);
                return;
            }
            
            if (!$room) {
                $this->output->set_status_header(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'The room you selected could not be found. Please refresh the page and try again.'
                ]);
                return;
            }
            
            // Calculate nights
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = $check_in_date->diff($check_out_date)->days;
            $nights = max($nights, 1);
            
            // Calculate base total for one room
            $base_total = $this->Booking_model->calculate_total_amount($room_id, $check_in, $check_out, $guests);
            $total_amount = $base_total * $requested_rooms;
            $total_rooms_count = $requested_rooms;
            
            $selected_rooms[] = array(
                'room_id' => $room_id,
                'room_name' => $room->room_name,
                'quantity' => $requested_rooms,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'guests' => $guests,
                'price_per_night' => $room->price,
                'nights' => $nights,
                'subtotal' => $total_amount
            );
            
            $first_room_id = $room_id;
        }
        
        if (empty($selected_rooms)) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please select at least one room.'
            ]);
            return;
        }

        // Merge cart extra services (pet, spa, laundry, extra bed, etc.)
        $services_total = 0;
        if (isset($data['extra_services']) && is_array($data['extra_services'])) {
            foreach ($data['extra_services'] as $service) {
                if (!is_array($service) || empty($service['name'])) {
                    continue;
                }
                $cost = isset($service['cost']) ? floatval($service['cost']) : 0;
                $extra_services[] = array(
                    'name' => trim((string)$service['name']),
                    'cost' => $cost
                );
                $services_total += $cost;
            }
        }

        // Fallback: charge extra beds from room selections if frontend did not send service rows
        $has_extra_bed_service = false;
        foreach ($extra_services as $service) {
            if ($this->is_extra_bed_service_name($service['name'])) {
                $has_extra_bed_service = true;
                break;
            }
        }
        if (!$has_extra_bed_service) {
            foreach ($selected_rooms as $room_selection) {
                if (empty($room_selection['extra_bed_total'])) {
                    continue;
                }
                $extra_bed_total = floatval($room_selection['extra_bed_total']);
                if ($extra_bed_total <= 0) {
                    continue;
                }
                $extra_beds = isset($room_selection['extra_beds']) ? (int)$room_selection['extra_beds'] : 0;
                $nights = isset($room_selection['nights']) ? (int)$room_selection['nights'] : 1;
                $bed_label = $extra_beds === 1 ? 'bed' : 'beds';
                $night_label = $nights === 1 ? 'night' : 'nights';
                $room_name = isset($room_selection['room_name']) ? $room_selection['room_name'] : 'Room';
                $extra_services[] = array(
                    'name' => "Extra Bed ({$extra_beds} {$bed_label} × {$nights} {$night_label} — {$room_name})",
                    'cost' => $extra_bed_total
                );
                $services_total += $extra_bed_total;
            }
        }

        $total_amount = floatval($total_amount) + $services_total;

        $this->load->library('billing_service');
        $booking_status = $this->billing_service->resolve_initial_booking_status();

        // Online GCash must stay pending until PayMongo confirms payment
        if ($payment_method === 'gcash') {
            if ($total_amount < 20) {
                $this->output->set_status_header(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'GCash online payment requires a minimum total of ₱20.00.'
                ]);
                return;
            }
            $booking_status = 'pending';
        }
        
        // Prepare main booking data
        // Include room_id for backward compatibility (use first room's ID)
        $booking_data = array(
            'room_id' => $first_room_id, // Keep for backward compatibility
            'user_id' => $this->session->userdata('user_id'), // May be null for guest booking
            'guest_name' => $data['guest_name'],
            'guest_email' => $data['guest_email'],
            'guest_phone' => $data['guest_phone'],
            'guest_address' => isset($data['guest_address']) ? $data['guest_address'] : null,
            'guest_city' => isset($data['guest_city']) ? $data['guest_city'] : null,
            'guest_province' => isset($data['guest_province']) ? $data['guest_province'] : null,
            'guest_country' => isset($data['guest_country']) ? $data['guest_country'] : null,
            'guest_zipcode' => isset($data['guest_zipcode']) ? $data['guest_zipcode'] : null,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'rooms' => $total_rooms_count, // Total number of rooms (for backward compatibility)
            'total_amount' => $total_amount,
            'status' => $booking_status,
            'notes' => isset($data['notes']) ? $data['notes'] : '',
            'extra_services' => !empty($extra_services) ? json_encode($extra_services) : null,
            'booking_number' => $this->Booking_model->generate_booking_number()
        );
        
        // If admin is creating the booking
        if ($this->session->userdata('admin_logged_in')) {
            $booking_data['admin_id'] = $this->session->userdata('admin_id');
        }
        
        // Start database transaction
        $this->db->trans_start();
        
        // Create main booking record
        $booking_id = $this->Booking_model->create_booking($booking_data);
        
        if (!$booking_id) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue processing your booking. Our team has been notified. Please try again or contact us for assistance.'
            ]);
            return;
        }
        
        // Check if booking_items table exists
        if (!$this->db->table_exists('booking_items')) {
            error_log('ERROR: booking_items table does not exist! Please run the SQL migration.');
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database configuration error. Please contact support.'
            ]);
            return;
        }
        
        // Create individual booking items for each room selection (same logic as admin panel)
        $created_items = array();
        foreach ($selected_rooms as $room_selection) {
            for ($i = 0; $i < $room_selection['quantity']; $i++) {
                // Use room-specific dates if available, otherwise use booking dates
                $item_check_in = isset($room_selection['check_in']) ? $room_selection['check_in'] : $check_in;
                $item_check_out = isset($room_selection['check_out']) ? $room_selection['check_out'] : $check_out;
                
                // Calculate nights for this specific item
                $item_check_in_date = new DateTime($item_check_in);
                $item_check_out_date = new DateTime($item_check_out);
                $item_nights = $item_check_in_date->diff($item_check_out_date)->days;
                $item_nights = max($item_nights, 1);
                
                $booking_item_data = array(
                    'booking_id' => $booking_id,
                    'room_id' => $room_selection['room_id'],
                    'room_name' => $room_selection['room_name'],
                    'check_in' => $item_check_in,
                    'check_out' => $item_check_out,
                    'price_per_night' => $room_selection['price_per_night'],
                    'nights' => $item_nights,
                    'guests' => isset($room_selection['guests']) ? (int)$room_selection['guests'] : 1,
                    'subtotal' => $room_selection['price_per_night'] * $item_nights,
                    'status' => $booking_status
                );
                
                error_log('Creating booking item: ' . print_r($booking_item_data, true));
                
                $item_id = $this->Booking_item_model->create_booking_item($booking_item_data);
                
                if (!$item_id) {
                    $db_error = $this->db->error();
                    error_log('ERROR creating booking item: ' . print_r($db_error, true));
                    $this->db->trans_rollback();
                    $this->output->set_status_header(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'We encountered an issue creating booking items. Error: ' . (isset($db_error['message']) ? $db_error['message'] : 'Unknown error')
                    ]);
                    return;
                }
                
                $created_items[] = $item_id;
                error_log('Successfully created booking item with ID: ' . $item_id);
            }
        }
        
        error_log('Created ' . count($created_items) . ' booking items for booking ID: ' . $booking_id);
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'We encountered an issue processing your booking. Our team has been notified. Please try again or contact us for assistance.'
            ]);
            return;
        }

        $auto_invoice = null;
        if ($booking_status === 'confirmed') {
            $auto_invoice = $this->billing_service->maybe_create_invoice_for_booking($booking_id, null);
        }
        
        // Get booking with room details
        $booking = $this->Booking_model->get_booking($booking_id);
        
        // Get booking items
        $booking_items = array();
        if ($this->db->table_exists('booking_items')) {
            $booking_items = $this->Booking_item_model->get_booking_items($booking_id);
            error_log('Retrieved ' . count($booking_items) . ' booking items for booking ID: ' . $booking_id);
        } else {
            error_log('WARNING: booking_items table does not exist when trying to retrieve items');
        }

        $payment_payload = null;
        $response_message = 'Your reservation has been confirmed! We look forward to hosting you at BODARE Pension House.';

        if ($payment_method === 'gcash') {
            $this->load->library('paymongo_service');
            if ($this->paymongo_service->is_ready()) {
                $checkout = $this->paymongo_service->start_gcash_checkout_for_booking($booking, $total_amount);
                if ($checkout && !empty($checkout['checkout_url'])) {
                    $payment_payload = array(
                        'method' => 'gcash',
                        'provider' => 'paymongo',
                        'status' => 'pending',
                        'checkout_url' => $checkout['checkout_url'],
                        'checkout_session_id' => $checkout['checkout_session_id'],
                        'payment_id' => $checkout['payment_id']
                    );
                    $response_message = 'Reservation created. Redirecting you to PayMongo to complete GCash payment.';
                } else {
                    $this->output->set_status_header(502);
                    echo json_encode([
                        'success' => false,
                        'booking_created' => true,
                        'can_retry_payment' => true,
                        'booking_number' => $booking->booking_number,
                        'message' => 'Your booking was saved (' . $booking->booking_number . '), but we could not start GCash payment: '
                            . ($this->paymongo_service->get_last_error() ?: 'Please contact us to complete payment.')
                            . ' You can retry payment without creating a new booking.'
                    ]);
                    return;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => $response_message,
            'booking' => $booking,
            'booking_number' => $booking->booking_number,
            'rooms_booked' => $total_rooms_count,
            'booking_items' => $booking_items,
            'items_count' => count($booking_items),
            'payment_method' => $payment_method,
            'payment' => $payment_payload,
            'invoice' => ($auto_invoice && !empty($auto_invoice['created'])) ? array(
                'id' => $auto_invoice['invoice_id'],
                'invoice_number' => $auto_invoice['invoice_number']
            ) : null
        ]);
    }
    
    /**
     * Get user bookings
     */
    public function my_bookings() {
        // Start output buffering to catch any errors
        ob_start();
        
        // Get the origin from the request
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        // Handle OPTIONS preflight request
        if ($this->input->method() === 'options') {
            ob_end_clean();
            exit;
        }
        
        try {
            if (!$this->session->userdata('user_logged_in')) {
                ob_clean();
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Please login to view your bookings'
                ]);
                ob_end_flush();
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            if (!$user_id) {
                ob_clean();
                $this->output->set_status_header(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'User session is invalid. Please log in again.'
                ]);
                ob_end_flush();
                return;
            }
            
            // Get bookings
            $bookings = $this->User_model->get_user_bookings($user_id);
            
            // Convert bookings to array format for JSON encoding
            $bookings_array = [];
            if ($bookings) {
                foreach ($bookings as $booking) {
                    $items_array = [];
                    if ($this->db->table_exists('booking_items')) {
                        $items = $this->Booking_item_model->get_booking_items($booking->id);
                        if ($items) {
                            foreach ($items as $item) {
                                $items_array[] = [
                                    'id' => $item->id,
                                    'room_id' => $item->room_id,
                                    'room_name' => isset($item->room_name) ? $item->room_name : 'Room',
                                    'room_type' => isset($item->room_type) ? $item->room_type : '',
                                    'check_in' => $item->check_in,
                                    'check_out' => $item->check_out,
                                    'price_per_night' => isset($item->price_per_night) ? floatval($item->price_per_night) : 0,
                                    'nights' => isset($item->nights) ? intval($item->nights) : 1,
                                    'guests' => isset($item->guests) ? intval($item->guests) : 1,
                                    'subtotal' => isset($item->subtotal) ? floatval($item->subtotal) : 0,
                                    'status' => isset($item->status) ? $item->status : 'pending'
                                ];
                            }
                        }
                    }

                    $extra_services = array();
                    if (!empty($booking->extra_services)) {
                        $decoded = json_decode($booking->extra_services, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $service) {
                                if (!is_array($service) || empty($service['name'])) {
                                    continue;
                                }
                                $extra_services[] = array(
                                    'name' => $service['name'],
                                    'cost' => isset($service['cost']) ? floatval($service['cost']) : null
                                );
                            }
                        }
                    }

                    // Fallback for older bookings that only stored services in notes
                    if (empty($extra_services) && !empty($booking->notes) && preg_match('/\|\s*Services:\s*(.+?)(?:\s*\|\s*|$)/i', $booking->notes, $matches)) {
                        $services_text = preg_replace('/\s*\(Card ending in.*$/i', '', $matches[1]);
                        foreach (explode(',', $services_text) as $part) {
                            $part = trim($part);
                            if ($part === '') {
                                continue;
                            }
                            if (preg_match('/^(.+?)\s*\(₱\s*([\d,]+(?:\.\d{1,2})?)\)\s*$/u', $part, $service_match)) {
                                $extra_services[] = array(
                                    'name' => trim($service_match[1]),
                                    'cost' => floatval(str_replace(',', '', $service_match[2]))
                                );
                            } else {
                                $extra_services[] = array(
                                    'name' => $part,
                                    'cost' => null
                                );
                            }
                        }
                    }

                    $bookings_array[] = [
                        'id' => $booking->id,
                        'booking_number' => isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                        'room_id' => $booking->room_id,
                        'room_name' => isset($booking->room_name) ? $booking->room_name : 'Room',
                        'room_type' => isset($booking->room_type) ? $booking->room_type : '',
                        'check_in' => $booking->check_in,
                        'check_out' => $booking->check_out,
                        'guests' => $booking->guests,
                        'rooms' => isset($booking->rooms) ? intval($booking->rooms) : count($items_array),
                        'total_amount' => isset($booking->total_amount) ? floatval($booking->total_amount) : 0,
                        'status' => isset($booking->status) ? $booking->status : 'pending',
                        'notes' => isset($booking->notes) ? $booking->notes : '',
                        'extra_services' => $extra_services,
                        'created_at' => isset($booking->created_at) ? $booking->created_at : '',
                        'items' => $items_array
                    ];
                }
            }
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'bookings' => $bookings_array
            ]);
            ob_end_flush();
            
        } catch (Exception $e) {
            ob_clean();
            log_message('error', 'My bookings API error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->output->set_status_header(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading your bookings. Please try again.'
            ]);
            ob_end_flush();
        }
    }
    
    /**
     * Cancel a pending booking belonging to the logged-in user
     */
    public function cancel() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');

        if ($this->input->method() === 'options') {
            exit;
        }

        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        if (!$this->session->userdata('user_logged_in')) {
            $this->output->set_status_header(401);
            echo json_encode(['success' => false, 'message' => 'Please login to cancel a booking']);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $data = json_decode($this->input->raw_input_stream, true);
        if (!$data) {
            $data = $this->input->post();
        }

        $booking_id = isset($data['booking_id']) ? intval($data['booking_id']) : 0;
        if (!$booking_id) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
            return;
        }

        $booking = $this->Booking_model->get_booking($booking_id);
        if (!$booking) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }

        if (!$booking->user_id || intval($booking->user_id) !== intval($user_id)) {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to cancel this booking']);
            return;
        }

        if (strtolower($booking->status) !== 'pending') {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Only pending bookings can be cancelled']);
            return;
        }

        $this->db->trans_start();

        $updated = $this->Booking_model->update_booking($booking_id, ['status' => 'cancelled']);
        if (!$updated) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);
            echo json_encode(['success' => false, 'message' => 'Failed to cancel booking. Please try again.']);
            return;
        }

        if ($this->db->table_exists('booking_items')) {
            $items = $this->Booking_item_model->get_booking_items($booking_id);
            if ($items) {
                foreach ($items as $item) {
                    if (strtolower($item->status) === 'pending') {
                        $this->Booking_item_model->update_booking_item($item->id, ['status' => 'cancelled']);
                    }
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output->set_status_header(500);
            echo json_encode(['success' => false, 'message' => 'Failed to cancel booking. Please try again.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'booking_id' => $booking_id
        ]);
    }

    /**
     * Get booking by booking number
     */
    public function get_by_number($booking_number = null) {
        header('Access-Control-Allow-Origin: *');
        
        if (!$booking_number) {
            $booking_number = $this->input->get_post('booking_number');
        }
        
        if (!$booking_number) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Booking number is required']);
            return;
        }
        
        $booking = $this->Booking_model->get_booking_by_number($booking_number);
        
        if ($booking) {
            // Check if user has permission to view this booking
            $user_id = $this->session->userdata('user_id');
            if ($booking->user_id && $booking->user_id != $user_id && !$this->session->userdata('admin_logged_in')) {
                $this->output->set_status_header(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have permission to view this booking'
                ]);
                return;
            }

            $items_array = array();
            if ($this->db->table_exists('booking_items')) {
                $items = $this->Booking_item_model->get_booking_items($booking->id);
                if ($items) {
                    foreach ($items as $item) {
                        $items_array[] = array(
                            'id' => $item->id,
                            'room_id' => $item->room_id,
                            'room_name' => isset($item->room_name) ? $item->room_name : 'Room',
                            'room_type' => isset($item->room_type) ? $item->room_type : '',
                            'check_in' => $item->check_in,
                            'check_out' => $item->check_out,
                            'price_per_night' => isset($item->price_per_night) ? floatval($item->price_per_night) : 0,
                            'nights' => isset($item->nights) ? intval($item->nights) : 1,
                            'guests' => isset($item->guests) ? intval($item->guests) : 1,
                            'subtotal' => isset($item->subtotal) ? floatval($item->subtotal) : 0,
                            'status' => isset($item->status) ? $item->status : 'pending'
                        );
                    }
                }
            }

            $extra_services = array();
            if (!empty($booking->extra_services)) {
                $decoded = json_decode($booking->extra_services, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $service) {
                        if (!is_array($service) || empty($service['name'])) {
                            continue;
                        }
                        $extra_services[] = array(
                            'name' => $service['name'],
                            'cost' => isset($service['cost']) ? floatval($service['cost']) : null
                        );
                    }
                }
            }

            $extra_services = $this->append_inferred_extra_bed_services(
                $extra_services,
                isset($booking->notes) ? $booking->notes : '',
                $items_array,
                isset($booking->total_amount) ? floatval($booking->total_amount) : 0,
                isset($booking->room_name) ? $booking->room_name : 'Room',
                $booking->check_in,
                $booking->check_out
            );

            $rooms_subtotal = 0;
            foreach ($items_array as $item) {
                $rooms_subtotal += floatval($item['subtotal']);
            }
            $extra_bed_total = 0;
            $other_services_total = 0;
            foreach ($extra_services as $service) {
                $cost = isset($service['cost']) ? floatval($service['cost']) : 0;
                if ($this->is_extra_bed_service_name($service['name'])) {
                    $extra_bed_total += $cost;
                } else {
                    $other_services_total += $cost;
                }
            }
            $display_total = max(
                floatval($booking->total_amount),
                $rooms_subtotal + $extra_bed_total + $other_services_total
            );

            $booking_array = array(
                'id' => $booking->id,
                'booking_number' => isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'room_id' => $booking->room_id,
                'room_name' => isset($booking->room_name) ? $booking->room_name : 'Room',
                'room_type' => isset($booking->room_type) ? $booking->room_type : '',
                'check_in' => $booking->check_in,
                'check_out' => $booking->check_out,
                'guests' => $booking->guests,
                'rooms' => isset($booking->rooms) ? intval($booking->rooms) : count($items_array),
                'total_amount' => $display_total,
                'status' => isset($booking->status) ? $booking->status : 'pending',
                'notes' => isset($booking->notes) ? $booking->notes : '',
                'extra_services' => $extra_services,
                'created_at' => isset($booking->created_at) ? $booking->created_at : '',
                'items' => $items_array
            );
            
            echo json_encode([
                'success' => true,
                'booking' => $booking_array
            ]);
        } else {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'Booking not found'
            ]);
        }
    }
    
    private function is_extra_bed_service_name($name)
    {
        return is_string($name) && stripos($name, 'extra bed') === 0;
    }

    /**
     * Normalize booking date/datetime values to Y-m-d for DATE columns.
     */
    private function normalize_booking_date($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $matches)) {
            return $matches[1];
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function parse_extra_beds_from_notes($notes)
    {
        if (!is_string($notes) || $notes === '') {
            return 0;
        }

        if (preg_match('/,\s*(\d+)\s+extra\s+beds?\b/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        if (preg_match('/Extra\s+Beds?:\s*(\d+)/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        if (preg_match('/Extra\s+Beds?:[^|]*?(\d+)\s+bed\(s\)/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        return 0;
    }

    private function get_extra_bed_price_setting()
    {
        $this->load->model('Room_settings_model');
        $price = $this->Room_settings_model->get_setting('extra_bed_price', 199);
        return max(0, floatval($price));
    }

    private function append_inferred_extra_bed_services($extra_services, $notes, $items_array, $total_amount, $fallback_room_name, $check_in, $check_out)
    {
        foreach ($extra_services as $service) {
            if ($this->is_extra_bed_service_name($service['name'])) {
                return $extra_services;
            }
        }

        $rooms_subtotal = 0;
        $other_services_total = 0;
        foreach ($items_array as $item) {
            $rooms_subtotal += floatval($item['subtotal']);
        }
        foreach ($extra_services as $service) {
            $other_services_total += isset($service['cost']) ? floatval($service['cost']) : 0;
        }

        $gap = round(floatval($total_amount) - $rooms_subtotal - $other_services_total, 2);
        if ($gap > 0) {
            $extra_services[] = array(
                'name' => 'Extra Bed',
                'cost' => $gap
            );
            return $extra_services;
        }

        $extra_beds = $this->parse_extra_beds_from_notes($notes);
        if ($extra_beds <= 0) {
            return $extra_services;
        }

        $nights = 1;
        if (!empty($items_array[0]['nights'])) {
            $nights = max(1, (int) $items_array[0]['nights']);
        } elseif (!empty($check_in) && !empty($check_out)) {
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = max(1, (int) $check_in_date->diff($check_out_date)->days);
        }

        $extra_bed_price = $this->get_extra_bed_price_setting();
        $extra_bed_total = $extra_beds * $extra_bed_price * $nights;
        $room_name = !empty($items_array[0]['room_name']) ? $items_array[0]['room_name'] : $fallback_room_name;
        $bed_label = $extra_beds === 1 ? 'bed' : 'beds';
        $night_label = $nights === 1 ? 'night' : 'nights';

        $extra_services[] = array(
            'name' => "Extra Bed ({$extra_beds} {$bed_label} × {$nights} {$night_label} @ ₱" . number_format($extra_bed_price, 2) . " — {$room_name})",
            'cost' => $extra_bed_total
        );

        return $extra_services;
    }
    
    /**
     * Calculate booking total
     */
    public function calculate_total() {
        header('Access-Control-Allow-Origin: *');
        
        $room_id = $this->input->get_post('room_id');
        $check_in = $this->input->get_post('check_in');
        $check_out = $this->input->get_post('check_out');
        $guests = $this->input->get_post('guests');
        
        if (!$room_id || !$check_in || !$check_out) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Room ID, check-in, and check-out dates are required'
            ]);
            return;
        }
        
        $total = $this->Booking_model->calculate_total_amount($room_id, $check_in, $check_out, $guests ? $guests : 1);
        $room = $this->Room_model->get_room($room_id);
        
        // Calculate nights
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $nights = $check_in_date->diff($check_out_date)->days;
        $nights = max($nights, 1);
        
        echo json_encode([
            'success' => true,
            'total' => $total,
            'nights' => $nights,
            'price_per_night' => $room ? $room->price : 0
        ]);
    }
    
    /**
     * Get room availability for a specific date
     * Used by calendar to show availability on each day
     */
    public function get_availability() {
        $date = $this->input->get('date');
        
        if (!$date) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Date parameter is required'
            ]);
            return;
        }
        
        // Validate date format
        $date = date('Y-m-d', strtotime($date));
        if ($date == '1970-01-01' || !$date) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid date format'
            ]);
            return;
        }
        
        // Get room availability for the date
        $availability = $this->Booking_model->get_room_availability_for_date($date);
        
        echo json_encode([
            'success' => true,
            'date' => $date,
            'availability' => $availability
        ]);
    }
}

