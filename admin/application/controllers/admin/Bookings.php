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
        $this->load->model('Booking_guest_model');
        $this->load->model('Room_model');
        $this->load->model('Customer_model');
        $this->load->model('Room_settings_model');
        $this->load->model('Booking_settings_model');
        $this->load->library('form_validation');
    }

    /**
     * Normalize HTML time input (HH:MM or HH:MM:SS) to TIME string.
     */
    private function normalize_booking_time($value, $default = '14:00')
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
            $h = (int) $m[1];
            $i = (int) $m[2];
            if ($h >= 0 && $h <= 23 && $i >= 0 && $i <= 59) {
                return sprintf('%02d:%02d:00', $h, $i);
            }
        }
        $default = trim((string) $default);
        if (preg_match('/^(\d{1,2}):(\d{2})/', $default, $m)) {
            return sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2]);
        }
        return '14:00:00';
    }

    /**
     * Format TIME for display (e.g. 2:00 PM).
     */
    private function format_booking_time_display($time)
    {
        if (empty($time)) {
            return '';
        }
        $ts = strtotime($time);
        return $ts ? date('g:i A', $ts) : '';
    }

    /**
     * Default check-in / check-out times from booking settings.
     */
    private function get_default_booking_times()
    {
        return array(
            'check_in_time' => $this->Booking_settings_model->get_setting('check_in_time', '14:00'),
            'check_out_time' => $this->Booking_settings_model->get_setting('check_out_time', '12:00')
        );
    }

    /**
     * Normalize posted guest names list into clean rows for booking_guests.
     */
    private function parse_guest_names_list()
    {
        $raw = $this->input->post('guest_names');
        if (empty($raw) || !is_array($raw)) {
            return array();
        }

        $guests = array();
        $allowed_genders = array('Male', 'Female', 'Other');

        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $full_name = isset($row['full_name']) ? trim($row['full_name']) : '';
            if ($full_name === '') {
                continue;
            }

            $gender = isset($row['gender']) ? trim($row['gender']) : '';
            if ($gender !== '' && !in_array($gender, $allowed_genders, true)) {
                $gender = '';
            }

            $dob = isset($row['date_of_birth']) ? trim($row['date_of_birth']) : '';
            if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                $dob = '';
            }

            $age = null;
            if (isset($row['age']) && $row['age'] !== '' && is_numeric($row['age'])) {
                $age = max(0, (int) $row['age']);
            } elseif ($dob !== '') {
                try {
                    $birth = new DateTime($dob);
                    $age = (int) $birth->diff(new DateTime('today'))->y;
                } catch (Exception $e) {
                    $age = null;
                }
            }

            $guests[] = array(
                'full_name' => $full_name,
                'age' => $age,
                'gender' => $gender !== '' ? $gender : null,
                'date_of_birth' => $dob !== '' ? $dob : null,
                'contact_no' => isset($row['contact_no']) ? trim($row['contact_no']) : null
            );
        }

        return $guests;
    }

    /**
     * Parse Extra Bed qty/price from booking.extra_services JSON.
     */
    private function parse_extra_bed_from_services($raw_services, $nights = 1, $default_price = 199)
    {
        $extra_beds = 0;
        $extra_bed_price = (float) $default_price;
        $extra_bed_total = 0.0;
        $other_services = array();

        $decoded = is_array($raw_services) ? $raw_services : json_decode((string) $raw_services, true);
        if (!is_array($decoded)) {
            return array(
                'extra_beds' => 0,
                'extra_bed_price' => $extra_bed_price,
                'extra_bed_total' => 0.0,
                'other_services' => array(),
                'all_services' => array()
            );
        }

        $nights = max(1, (int) $nights);
        foreach ($decoded as $service) {
            if (!is_array($service) || empty($service['name'])) {
                continue;
            }

            $name = trim((string) $service['name']);
            $cost = isset($service['cost']) ? (float) $service['cost'] : 0.0;
            $normalized = array('name' => $name, 'cost' => $cost);

            if (stripos($name, 'extra bed') === 0) {
                $qty = 1;
                if (preg_match('/\((\d+)\s+beds?/i', $name, $matches)) {
                    $qty = max(1, (int) $matches[1]);
                }
                $extra_beds += $qty;
                $extra_bed_total += $cost;
            } else {
                $other_services[] = $normalized;
            }
        }

        if ($extra_beds > 0 && $extra_bed_total > 0) {
            $extra_bed_price = $extra_bed_total / ($extra_beds * $nights);
        }

        return array(
            'extra_beds' => $extra_beds,
            'extra_bed_price' => round($extra_bed_price, 2),
            'extra_bed_total' => $extra_bed_total,
            'other_services' => $other_services,
            'all_services' => $decoded
        );
    }
    
    public function index() {
        // Require permission to view bookings
        $this->require_permission('view_bookings');
        
        $allowed_statuses = array('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'completed');
        $status = $this->input->get('status');
        if (!$status || !in_array($status, $allowed_statuses, true)) {
            $status = '';
        }

        $data['title'] = 'Manage Bookings';
        $data['bookings'] = $this->Booking_model->get_all_bookings($status ?: null);
        $data['filter_status'] = $status;
        $data['can_add'] = $this->has_permission('add_bookings');
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');

        $data['payment_status_map'] = array();
        if ($this->db->table_exists('invoices')) {
            $this->load->model('Invoice_model');
            $data['payment_status_map'] = $this->Invoice_model->get_payment_status_map();
        }
        
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

        $data['booking_guests'] = $this->Booking_guest_model->get_booking_guests($id);
        
        $data['can_edit'] = $this->has_permission('edit_bookings');
        $data['can_delete'] = $this->has_permission('delete_bookings');
        $data['can_create_invoice'] = $this->has_permission('add_invoices');
        $data['can_add_payment'] = $this->has_permission('add_payments');
        
        if ($this->db->table_exists('invoices')) {
            $this->load->model('Invoice_model');
            $data['booking_invoices'] = $this->Invoice_model->get_invoices_for_booking($id);
            $data['payment_status'] = $this->Invoice_model->get_booking_payment_status($id);
        } else {
            $data['booking_invoices'] = array();
            $data['payment_status'] = array(
                'label' => 'no_invoice',
                'display' => 'No Invoice',
                'badge' => 'secondary',
                'balance' => 0,
                'amount_paid' => 0,
                'primary_invoice_id' => null
            );
        }
        
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

        $data['booking_guests'] = $this->Booking_guest_model->get_booking_guests($id);

        $default_extra_bed_price = (float) $this->Room_settings_model->get_setting('extra_bed_price', 199);
        $booking_nights = 1;
        if (!empty($data['booking']->check_in) && !empty($data['booking']->check_out)) {
            $ci = new DateTime($data['booking']->check_in);
            $co = new DateTime($data['booking']->check_out);
            $booking_nights = max(1, (int) $ci->diff($co)->days);
        }
        $parsed_extra = $this->parse_extra_bed_from_services(
            isset($data['booking']->extra_services) ? $data['booking']->extra_services : null,
            $booking_nights,
            $default_extra_bed_price
        );
        $data['extra_beds'] = $parsed_extra['extra_beds'];
        $data['extra_bed_price'] = $parsed_extra['extra_bed_price'] > 0
            ? $parsed_extra['extra_bed_price']
            : $default_extra_bed_price;
        $data['other_extra_services'] = $parsed_extra['other_services'];
        $data['default_extra_bed_price'] = $default_extra_bed_price;
        $default_times = $this->get_default_booking_times();
        $data['default_check_in_time'] = $default_times['check_in_time'];
        $data['default_check_out_time'] = $default_times['check_out_time'];
        
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
                $total_guests_count = 0;
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
                            if ($sel_guests < 1) {
                                $sel_guests = 1;
                            }
                            
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
                            }

                            $total_guests_count += ($sel_guests * $sel_quantity);
                            
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
                        'guests' => $guests ? (int)$guests : 1,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $total_amount
                    );
                    
                    $first_room_id = $room_id;
                }

                if ($total_guests_count > 0) {
                    $guests = $total_guests_count;
                }
                
                if (empty($selected_rooms)) {
                    $this->session->set_flashdata('error', 'Please select at least one room.');
                    redirect('bookings/edit/' . $id);
                    return;
                }

                // Extra bed charge
                $extra_beds = max(0, (int) $this->input->post('extra_beds'));
                $extra_bed_price = max(0, (float) $this->input->post('extra_bed_price'));
                if ($extra_beds > 0 && $extra_bed_price <= 0) {
                    $extra_bed_price = (float) $this->Room_settings_model->get_setting('extra_bed_price', 199);
                }

                $booking_nights_for_extra = 1;
                if (!empty($check_in) && !empty($check_out)) {
                    $ci_extra = new DateTime($check_in);
                    $co_extra = new DateTime($check_out);
                    $booking_nights_for_extra = max(1, (int) $ci_extra->diff($co_extra)->days);
                }

                $extra_services = array();
                $existing_parsed = $this->parse_extra_bed_from_services(
                    isset($data['booking']->extra_services) ? $data['booking']->extra_services : null,
                    $booking_nights_for_extra,
                    $extra_bed_price > 0 ? $extra_bed_price : 199
                );
                foreach ($existing_parsed['other_services'] as $other_service) {
                    $extra_services[] = $other_service;
                    $total_amount += (float) $other_service['cost'];
                }

                if ($extra_beds > 0 && $extra_bed_price > 0) {
                    $extra_bed_total = $extra_beds * $extra_bed_price * $booking_nights_for_extra;
                    $bed_label = $extra_beds === 1 ? 'bed' : 'beds';
                    $night_label = $booking_nights_for_extra === 1 ? 'night' : 'nights';
                    $room_label = !empty($selected_rooms[0]['room_name']) ? $selected_rooms[0]['room_name'] : 'Room';
                    $extra_services[] = array(
                        'name' => "Extra Bed ({$extra_beds} {$bed_label} × {$booking_nights_for_extra} {$night_label} @ ₱" . number_format($extra_bed_price, 2) . " — {$room_label})",
                        'cost' => $extra_bed_total
                    );
                    $total_amount += $extra_bed_total;
                }
                
                // Start transaction
                $this->db->trans_start();
                
                // Update main booking
                $default_times = $this->get_default_booking_times();
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
                    'check_in_time' => $this->normalize_booking_time($this->input->post('check_in_time'), $default_times['check_in_time']),
                    'check_out_time' => $this->normalize_booking_time($this->input->post('check_out_time'), $default_times['check_out_time']),
                    'guests' => $guests,
                    'rooms' => $total_rooms_count,
                    'total_amount' => $total_amount,
                    'status' => $this->input->post('status'),
                    'notes' => $this->input->post('notes'),
                    'extra_services' => !empty($extra_services) ? json_encode($extra_services) : null,
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
                                'guests' => isset($room_selection['guests']) ? (int)$room_selection['guests'] : 1,
                                'subtotal' => $room_selection['price_per_night'] * $item_nights,
                                'status' => $this->input->post('status')
                            );
                            
                            $this->Booking_item_model->create_booking_item($booking_item_data);
                        }
                    }

                    $this->Booking_guest_model->replace_booking_guests($id, $this->parse_guest_names_list());
                    
                    $this->db->trans_complete();
                    
                    if ($this->db->trans_status() === FALSE) {
                        $this->session->set_flashdata('error', 'Failed to update booking items.');
                    } else {
                        $success_msg = 'Booking updated successfully with ' . $total_rooms_count . ' room(s).';
                        $new_status = $this->input->post('status');
                        if ($new_status === 'confirmed') {
                            $this->load->library('billing_service');
                            $auto = $this->billing_service->maybe_create_invoice_for_booking($id, $this->admin_id);
                            if ($auto && !empty($auto['created'])) {
                                $success_msg .= ' Invoice ' . $auto['invoice_number'] . ' was auto-created.';
                            }
                        }
                        $this->session->set_flashdata('success', $success_msg);
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

    /**
     * Mark booking as checked in (guest arrived / in-house).
     */
    public function check_in($id) {
        $this->require_permission('edit_bookings');
        $this->change_booking_status($id, 'checked_in', array('confirmed'), 'Guest checked in successfully.');
    }

    /**
     * Mark booking as checked out (stay finished / closed).
     */
    public function check_out($id) {
        $this->require_permission('edit_bookings');
        $this->change_booking_status($id, 'checked_out', array('checked_in', 'confirmed'), 'Guest checked out successfully. Booking is now closed.');
    }

    /**
     * Update booking + room-item status with validation.
     */
    private function change_booking_status($id, $new_status, $allowed_from, $success_message) {
        if (empty($id) || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Invalid booking ID');
            redirect('bookings');
            return;
        }

        $booking = $this->Booking_model->get_booking($id);
        if (!$booking) {
            $this->session->set_flashdata('error', 'Booking not found');
            redirect('bookings');
            return;
        }

        if (!in_array($booking->status, $allowed_from, true)) {
            $from_label = ucwords(str_replace('_', ' ', $booking->status));
            $to_label = ucwords(str_replace('_', ' ', $new_status));
            $this->session->set_flashdata('error', "Cannot mark as {$to_label} from status: {$from_label}.");
            redirect('bookings/' . $id);
            return;
        }

        if ($this->Booking_model->set_booking_status($id, $new_status)) {
            $this->session->set_flashdata('success', $success_message);
        } else {
            $this->session->set_flashdata('error', 'Failed to update booking status.');
        }

        redirect('bookings/' . $id);
    }
    
    public function add() {
        // Require permission to add bookings
        $this->require_permission('add_bookings');
        
        $data['title'] = 'Add New Booking';
        $data['rooms'] = $this->Room_model->get_all_rooms();
        $data['customers'] = $this->Customer_model->get_all_with_user_info();
        $default_times = $this->get_default_booking_times();
        $data['default_check_in_time'] = $default_times['check_in_time'];
        $data['default_check_out_time'] = $default_times['check_out_time'];
        
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
                $total_guests_count = 0;
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
                            if ($sel_guests < 1) {
                                $sel_guests = 1;
                            }
                            
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
                            }

                            $total_guests_count += ($sel_guests * $sel_quantity);
                            
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
                        'guests' => $guests ? (int)$guests : 1,
                        'price_per_night' => $room->price,
                        'nights' => $nights,
                        'subtotal' => $total_amount
                    );
                    
                    $first_room_id = $room_id;
                }

                if ($total_guests_count > 0) {
                    $guests = $total_guests_count;
                }
                
                if (empty($selected_rooms)) {
                    $this->session->set_flashdata('error', 'Please select at least one room.');
                    redirect('bookings/add');
                    return;
                }
                
                // Start transaction
                $this->db->trans_start();
                
                // Prepare main booking data
                $default_times = $this->get_default_booking_times();
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
                    'check_in_time' => $this->normalize_booking_time($this->input->post('check_in_time'), $default_times['check_in_time']),
                    'check_out_time' => $this->normalize_booking_time($this->input->post('check_out_time'), $default_times['check_out_time']),
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
                                'guests' => isset($room_selection['guests']) ? (int)$room_selection['guests'] : 1,
                                'subtotal' => $room_selection['price_per_night'] * $item_nights,
                                'status' => $this->input->post('status') ? $this->input->post('status') : 'pending'
                            );
                            
                            $this->Booking_item_model->create_booking_item($booking_item_data);
                        }
                    }

                    $this->Booking_guest_model->replace_booking_guests($booking_id, $this->parse_guest_names_list());
                    
                    $this->db->trans_complete();
                    
                    if ($this->db->trans_status() === FALSE) {
                        $this->session->set_flashdata('error', 'Failed to create booking items.');
                    } else {
                        $success_msg = 'Booking created successfully with ' . $total_rooms_count . ' room(s).';
                        $booking_status = $this->input->post('status') ? $this->input->post('status') : 'pending';
                        if ($booking_status === 'confirmed') {
                            $this->load->library('billing_service');
                            $auto = $this->billing_service->maybe_create_invoice_for_booking($booking_id, $this->admin_id);
                            if ($auto && !empty($auto['created'])) {
                                $success_msg .= ' Invoice ' . $auto['invoice_number'] . ' was auto-created.';
                            }
                        }
                        $this->session->set_flashdata('success', $success_msg);
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

