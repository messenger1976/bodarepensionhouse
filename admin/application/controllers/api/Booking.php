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
        $guests = null;
        
        if ($room_selections && !empty($room_selections)) {
            // New format: multiple room selections with individual dates and guests
            foreach ($room_selections as $selection) {
                if (!empty($selection['room_id']) && !empty($selection['quantity']) && !empty($selection['check_in']) && !empty($selection['check_out'])) {
                    $sel_room_id = (int)$selection['room_id'];
                    $sel_quantity = (int)$selection['quantity'];
                    $sel_check_in = $selection['check_in'];
                    $sel_check_out = $selection['check_out'];
                    $sel_guests = isset($selection['guests']) ? (int)$selection['guests'] : 1;
                    
                    // Validate dates
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
                        $guests = $sel_guests;
                    }
                    
                    // Calculate nights for this specific room selection
                    $check_in_date = new DateTime($sel_check_in);
                    $check_out_date = new DateTime($sel_check_out);
                    $nights = $check_in_date->diff($check_out_date)->days;
                    $nights = max($nights, 1);
                    
                    // Calculate subtotal for this room selection
                    $base_total = $this->Booking_model->calculate_total_amount($sel_room_id, $sel_check_in, $sel_check_out, $sel_guests);
                    $subtotal = $base_total * $sel_quantity;
                    
                    $selected_rooms[] = array(
                        'room_id' => $sel_room_id,
                        'room_name' => $room->room_name,
                        'quantity' => $sel_quantity,
                        'check_in' => $sel_check_in,
                        'check_out' => $sel_check_out,
                        'guests' => $sel_guests,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $subtotal
                    );
                    
                    $total_amount += $subtotal;
                    $total_rooms_count += $sel_quantity;
                }
            }
        } else if ($room_id) {
            // Old format: single room with quantity
            $requested_rooms = isset($data['rooms']) && $data['rooms'] > 0 ? (int)$data['rooms'] : 1;
            $check_in = $data['check_in'];
            $check_out = $data['check_out'];
            $guests = $data['guests'];
            
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
            'status' => 'pending',
            'notes' => isset($data['notes']) ? $data['notes'] : '',
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
                    'subtotal' => $room_selection['price_per_night'] * $item_nights,
                    'status' => 'pending'
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Your reservation has been confirmed! We look forward to hosting you at BODARE Pension House.',
            'booking' => $booking,
            'booking_number' => $booking->booking_number,
            'rooms_booked' => $total_rooms_count,
            'booking_items' => $booking_items,
            'items_count' => count($booking_items)
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
                    $bookings_array[] = [
                        'id' => $booking->id,
                        'booking_number' => isset($booking->booking_number) ? $booking->booking_number : str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                        'room_id' => $booking->room_id,
                        'room_name' => isset($booking->room_name) ? $booking->room_name : 'Room',
                        'room_type' => isset($booking->room_type) ? $booking->room_type : '',
                        'check_in' => $booking->check_in,
                        'check_out' => $booking->check_out,
                        'guests' => $booking->guests,
                        'total_amount' => isset($booking->total_amount) ? floatval($booking->total_amount) : 0,
                        'status' => isset($booking->status) ? $booking->status : 'pending',
                        'notes' => isset($booking->notes) ? $booking->notes : '',
                        'created_at' => isset($booking->created_at) ? $booking->created_at : ''
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
            
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            $this->output->set_status_header(404);
            echo json_encode([
                'success' => false,
                'message' => 'Booking not found'
            ]);
        }
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

