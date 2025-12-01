<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load Admin_Controller if not already loaded
if (!class_exists('Admin_Controller', FALSE)) {
    require_once(APPPATH.'core/Admin_Controller.php');
}

class Bookings extends Admin_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Booking_model');
        $this->load->model('Booking_item_model');
        $this->load->model('Room_model');
        $this->load->model('Customer_model');
        $this->load->library('form_validation');
    }
    
    public function index() {
        // Require permission to view bookings
        $this->require_permission('view_bookings');
        
        $data['title'] = 'Manage Bookings';
        $data['bookings'] = $this->Booking_model->get_all_bookings();
        $data['can_add'] = $this->has_permission('add_bookings');
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/bookings/index', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function view($id) {
        // Require permission to view bookings
        $this->require_permission('view_bookings');
        
        $data['title'] = 'Booking Details';
        $data['booking'] = $this->Booking_model->get_booking($id);
        
        if (!$data['booking']) {
            show_404();
            return;
        }
        
        // Get booking items (individual rooms) - check if table exists first
        if ($this->db->table_exists('booking_items')) {
            $data['booking_items'] = $this->Booking_item_model->get_booking_items($id);
        } else {
            $data['booking_items'] = array();
            error_log('WARNING: booking_items table does not exist. Please run the SQL migration.');
        }
        
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/bookings/view', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function edit($id) {
        // Require permission to edit bookings
        $this->require_permission('edit_bookings');
        
        $data['title'] = 'Edit Booking';
        $data['booking'] = $this->Booking_model->get_booking($id);
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['customers'] = $this->Customer_model->get_all_with_user_info();
        
        if (!$data['booking']) {
            show_404();
            return;
        }
        
        // Get booking items (individual rooms) - check if table exists first
        if ($this->db->table_exists('booking_items')) {
            $data['booking_items'] = $this->Booking_item_model->get_booking_items($id);
        } else {
            $data['booking_items'] = array();
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('guest_name', 'Guest Name', 'required');
            $this->form_validation->set_rules('guest_email', 'Guest Email', 'required|valid_email');
            $this->form_validation->set_rules('guest_phone', 'Guest Phone', 'required');
            $this->form_validation->set_rules('check_in', 'Check In Date', 'required');
            $this->form_validation->set_rules('check_out', 'Check Out Date', 'required');
            $this->form_validation->set_rules('status', 'Status', 'required');
            $this->form_validation->set_rules('rooms', 'Number of Rooms', 'required|integer|greater_than[0]');
            
            if ($this->form_validation->run() == TRUE) {
                $check_in = $this->input->post('check_in');
                $check_out = $this->input->post('check_out');
                $guests = $this->input->post('guests');
                
                // Get room selections (new format) or fallback to old format
                $room_selections = $this->input->post('room_selections');
                $room_id = $this->input->post('room_id'); // Fallback for old format
                $rooms = $this->input->post('rooms'); // Fallback for old format
                
                // Process room selections
                $selected_rooms = array();
                $total_amount = 0;
                $total_rooms_count = 0;
                $first_room_id = null;
                
                if (!empty($room_selections) && is_array($room_selections)) {
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
                                $this->session->set_flashdata('error', 'Check-out date must be after check-in date for one of the rooms.');
                                redirect('bookings/edit/' . $id);
                                return;
                            }
                            
                            // Check availability for this specific room and dates (exclude current booking)
                            if (!$this->Booking_model->check_room_availability($sel_room_id, $sel_check_in, $sel_check_out, $id, $sel_quantity)) {
                                $room_info = $this->Room_model->get_room($sel_room_id);
                                $room_name = $room_info ? $room_info->room_name : 'Unknown';
                                $this->session->set_flashdata('error', "Room '{$room_name}' is not available for the selected dates ({$sel_check_in} to {$sel_check_out}). Not enough rooms available.");
                                redirect('bookings/edit/' . $id);
                                return;
                            }
                            
                            $room = $this->Room_model->get_room($sel_room_id);
                            if (!$room) {
                                $this->session->set_flashdata('error', 'One of the selected rooms was not found.');
                                redirect('bookings/edit/' . $id);
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
                    $rooms = $rooms ? (int)$rooms : 1;
                    
                    if (!$this->Booking_model->check_room_availability($room_id, $check_in, $check_out, $id, $rooms)) {
                        $this->session->set_flashdata('error', 'Room is not available for the selected dates. Not enough rooms available.');
                        redirect('bookings/edit/' . $id);
                        return;
                    }
                    
                    $room = $this->Room_model->get_room($room_id);
                    if (!$room) {
                        $this->session->set_flashdata('error', 'Room not found.');
                        redirect('bookings/edit/' . $id);
                        return;
                    }
                    
                    // Calculate nights
                    $check_in_date = new DateTime($check_in);
                    $check_out_date = new DateTime($check_out);
                    $nights = $check_in_date->diff($check_out_date)->days;
                    $nights = max($nights, 1);
                    
                    $base_total = $this->Booking_model->calculate_total_amount($room_id, $check_in, $check_out, $guests);
                    $total_amount = $base_total * $rooms;
                    $total_rooms_count = $rooms;
                    
                    $selected_rooms[] = array(
                        'room_id' => $room_id,
                        'room_name' => $room->room_name,
                        'quantity' => $rooms,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $total_amount
                    );
                    
                    $first_room_id = $room_id;
                }
                
                if (empty($selected_rooms)) {
                    $this->session->set_flashdata('error', 'Please select at least one room.');
                    redirect('bookings/edit/' . $id);
                    return;
                }
                
                // Start transaction
                $this->db->trans_start();
                
                // Update main booking
                $update_data = array(
                    'room_id' => $first_room_id, // Keep for backward compatibility (use first room)
                    'guest_name' => $this->input->post('guest_name'),
                    'guest_email' => $this->input->post('guest_email'),
                    'guest_phone' => $this->input->post('guest_phone'),
                    'guest_address' => $this->input->post('guest_address'),
                    'guest_city' => $this->input->post('guest_city'),
                    'guest_province' => $this->input->post('guest_province'),
                    'guest_country' => $this->input->post('guest_country'),
                    'guest_zipcode' => $this->input->post('guest_zipcode'),
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'guests' => $guests,
                    'rooms' => $total_rooms_count,
                    'total_amount' => $total_amount,
                    'status' => $this->input->post('status'),
                    'notes' => $this->input->post('notes'),
                    'admin_id' => $this->admin_id
                );
                
                if ($this->Booking_model->update_booking($id, $update_data)) {
                    // Delete existing booking items
                    $this->Booking_item_model->delete_booking_items($id);
                    
                    // Create new booking items for each room selection
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
                                'booking_id' => $id,
                                'room_id' => $room_selection['room_id'],
                                'room_name' => $room_selection['room_name'],
                                'check_in' => $item_check_in,
                                'check_out' => $item_check_out,
                                'price_per_night' => $room_selection['price_per_night'],
                                'nights' => $item_nights,
                                'subtotal' => $room_selection['price_per_night'] * $item_nights,
                                'status' => $this->input->post('status')
                            );
                            
                            $this->Booking_item_model->create_booking_item($booking_item_data);
                        }
                    }
                    
                    $this->db->trans_complete();
                    
                    if ($this->db->trans_status() === FALSE) {
                        $this->session->set_flashdata('error', 'Failed to update booking items.');
                    } else {
                        $this->session->set_flashdata('success', 'Booking updated successfully with ' . $total_rooms_count . ' room(s).');
                        redirect('bookings');
                    }
                } else {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error', 'Failed to update booking');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/bookings/edit', $data);
        $this->load->view('admin/layout/footer');
    }
    
    public function delete($id) {
        // Require permission to delete bookings
        $this->require_permission('delete_bookings');
        
        // Validate booking ID
        if (empty($id) || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Invalid booking ID');
            redirect('bookings');
            return;
        }
        
        // Check if booking exists
        $booking = $this->Booking_model->get_booking($id);
        if (!$booking) {
            $this->session->set_flashdata('error', 'Booking not found');
            redirect('bookings');
            return;
        }
        
        // Attempt to delete
        if ($this->Booking_model->delete_booking($id)) {
            $this->session->set_flashdata('success', 'Booking #' . (isset($booking->booking_number) ? $booking->booking_number : str_pad($id, 6, '0', STR_PAD_LEFT)) . ' deleted successfully');
        } else {
            // Get database error for debugging
            $error = $this->db->error();
            $error_message = 'Failed to delete booking';
            if (!empty($error['message'])) {
                error_log('Booking deletion error: ' . $error['message']);
                // Don't expose database errors to users, but log them
                $error_message .= '. Please check the error logs for details.';
            }
            $this->session->set_flashdata('error', $error_message);
        }
        redirect('bookings');
    }
    
    public function add() {
        // Require permission to add bookings
        $this->require_permission('add_bookings');
        
        $data['title'] = 'Add New Booking';
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['customers'] = $this->Customer_model->get_all_with_user_info();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('guest_name', 'Guest Name', 'required');
            $this->form_validation->set_rules('guest_email', 'Guest Email', 'required|valid_email');
            $this->form_validation->set_rules('guest_phone', 'Guest Phone', 'required');
            $this->form_validation->set_rules('check_in', 'Check In Date', 'required');
            $this->form_validation->set_rules('check_out', 'Check Out Date', 'required');
            $this->form_validation->set_rules('guests', 'Number of Guests', 'required|integer');
            
            if ($this->form_validation->run() == TRUE) {
                $check_in = $this->input->post('check_in');
                $check_out = $this->input->post('check_out');
                $guests = $this->input->post('guests');
                
                // Get room selections (new format) or fallback to old format
                $room_selections = $this->input->post('room_selections');
                $room_id = $this->input->post('room_id'); // Fallback for old format
                $rooms = $this->input->post('rooms'); // Fallback for old format
                
                // Process room selections
                $selected_rooms = array();
                $total_amount = 0;
                $total_rooms_count = 0;
                $first_room_id = null;
                
                if (!empty($room_selections) && is_array($room_selections)) {
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
                                $this->session->set_flashdata('error', 'Check-out date must be after check-in date for one of the rooms.');
                                redirect('bookings/add');
                                return;
                            }
                            
                            // Check availability for this specific room and dates
                            if (!$this->Booking_model->check_room_availability($sel_room_id, $sel_check_in, $sel_check_out, null, $sel_quantity)) {
                                $room_info = $this->Room_model->get_room($sel_room_id);
                                $room_name = $room_info ? $room_info->room_name : 'Unknown';
                                $this->session->set_flashdata('error', "Room '{$room_name}' is not available for the selected dates ({$sel_check_in} to {$sel_check_out}). Not enough rooms available.");
                                redirect('bookings/add');
                                return;
                            }
                            
                            $room = $this->Room_model->get_room($sel_room_id);
                            if (!$room) {
                                $this->session->set_flashdata('error', 'One of the selected rooms was not found.');
                                redirect('bookings/add');
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
                    $rooms = $rooms ? (int)$rooms : 1;
                    
                    if (!$this->Booking_model->check_room_availability($room_id, $check_in, $check_out, null, $rooms)) {
                        $this->session->set_flashdata('error', 'Room is not available for the selected dates. Not enough rooms available.');
                        redirect('bookings/add');
                        return;
                    }
                    
                    $room = $this->Room_model->get_room($room_id);
                    if (!$room) {
                        $this->session->set_flashdata('error', 'Room not found.');
                        redirect('bookings/add');
                        return;
                    }
                    
                    // Calculate nights
                    $check_in_date = new DateTime($check_in);
                    $check_out_date = new DateTime($check_out);
                    $nights = $check_in_date->diff($check_out_date)->days;
                    $nights = max($nights, 1);
                    
                    $base_total = $this->Booking_model->calculate_total_amount($room_id, $check_in, $check_out, $guests);
                    $total_amount = $base_total * $rooms;
                    $total_rooms_count = $rooms;
                    
                    $selected_rooms[] = array(
                        'room_id' => $room_id,
                        'room_name' => $room->room_name,
                        'quantity' => $rooms,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $total_amount
                    );
                    
                    $first_room_id = $room_id;
                }
                
                if (empty($selected_rooms)) {
                    $this->session->set_flashdata('error', 'Please select at least one room.');
                    redirect('bookings/add');
                    return;
                }
                
                // Start transaction
                $this->db->trans_start();
                
                // Prepare main booking data
                $booking_data = array(
                    'room_id' => $first_room_id, // Keep for backward compatibility (use first room)
                    'guest_name' => $this->input->post('guest_name'),
                    'guest_email' => $this->input->post('guest_email'),
                    'guest_phone' => $this->input->post('guest_phone'),
                    'guest_address' => $this->input->post('guest_address'),
                    'guest_city' => $this->input->post('guest_city'),
                    'guest_province' => $this->input->post('guest_province'),
                    'guest_country' => $this->input->post('guest_country'),
                    'guest_zipcode' => $this->input->post('guest_zipcode'),
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'guests' => $guests,
                    'rooms' => $total_rooms_count,
                    'total_amount' => $total_amount,
                    'status' => $this->input->post('status') ? $this->input->post('status') : 'pending',
                    'notes' => $this->input->post('notes'),
                    'booking_number' => $this->Booking_model->generate_booking_number(),
                    'admin_id' => $this->admin_id
                );
                
                // Create main booking
                $booking_id = $this->Booking_model->create_booking($booking_data);
                
                if ($booking_id) {
                    // Create individual booking items for each room selection
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
                                'status' => $this->input->post('status') ? $this->input->post('status') : 'pending'
                            );
                            
                            $this->Booking_item_model->create_booking_item($booking_item_data);
                        }
                    }
                    
                    $this->db->trans_complete();
                    
                    if ($this->db->trans_status() === FALSE) {
                        $this->session->set_flashdata('error', 'Failed to create booking items.');
                    } else {
                        $this->session->set_flashdata('success', 'Booking created successfully with ' . $total_rooms_count . ' room(s).');
                        redirect('bookings');
                    }
                } else {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error', 'Failed to create booking');
                }
            }
        }
        
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/bookings/add', $data);
        $this->load->view('admin/layout/footer');
    }
}

