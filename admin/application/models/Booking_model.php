<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all bookings with room information
     * Includes earliest check-in and latest check-out from booking_items
     */
    public function get_all_bookings() {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->order_by('bookings.created_at', 'DESC');
        $bookings = $this->db->get()->result();
        
        // If booking_items table exists, get earliest check-in and latest check-out for each booking
        if ($this->db->table_exists('booking_items')) {
            foreach ($bookings as $booking) {
                $this->db->select('MIN(check_in) as earliest_checkin, MAX(check_out) as latest_checkout');
                $this->db->from('booking_items');
                $this->db->where('booking_id', $booking->id);
                $this->db->where('status !=', 'cancelled');
                $result = $this->db->get()->row();
                
                if ($result && $result->earliest_checkin) {
                    $booking->earliest_checkin = $result->earliest_checkin;
                    $booking->latest_checkout = $result->latest_checkout;
                } else {
                    // Fallback to booking's check_in/check_out if no booking_items
                    $booking->earliest_checkin = $booking->check_in;
                    $booking->latest_checkout = $booking->check_out;
                }
            }
        } else {
            // Fallback: use booking's check_in/check_out
            foreach ($bookings as $booking) {
                $booking->earliest_checkin = $booking->check_in;
                $booking->latest_checkout = $booking->check_out;
            }
        }
        
        return $bookings;
    }
    
    /**
     * Get booking by ID with room information
     */
    public function get_booking($id) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.price');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.id', $id);
        return $this->db->get()->row();
    }
    
    /**
     * Create a new booking
     */
    public function create_booking($data) {
        // Check if rooms field exists in bookings table
        $fields = $this->db->list_fields('bookings');
        $has_rooms_field = in_array('rooms', $fields);
        
        // If rooms field doesn't exist, remove it from data to prevent errors
        if (!$has_rooms_field && isset($data['rooms'])) {
            // Log warning but don't fail - allow booking to be created
            log_message('warning', 'Bookings table does not have rooms field. Booking created without rooms count.');
            unset($data['rooms']);
        }
        
        $this->db->insert('bookings', $data);
        
        // Check for database errors
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Database error creating booking: ' . $this->db->error()['message']);
            return false;
        }
        
        return $this->db->insert_id();
    }
    
    /**
     * Update booking
     */
    public function update_booking($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('bookings', $data);
    }
    
    /**
     * Delete booking
     * Also deletes related booking_items, payments, and reviews
     */
    public function delete_booking($id) {
        // Start transaction
        $this->db->trans_start();
        
        // Check if booking exists
        $booking = $this->get_booking($id);
        if (!$booking) {
            $this->db->trans_rollback();
            return false;
        }
        
        // Delete related booking_items first (if table exists)
        if ($this->db->table_exists('booking_items')) {
            $this->load->model('Booking_item_model');
            $this->Booking_item_model->delete_booking_items($id);
        }
        
        // Delete related payments (if table exists and has foreign key with CASCADE, this should be automatic)
        if ($this->db->table_exists('payments')) {
            $this->db->where('booking_id', $id);
            $this->db->delete('payments');
        }
        
        // Delete related reviews (if table exists and has foreign key with CASCADE, this should be automatic)
        if ($this->db->table_exists('reviews')) {
            $this->db->where('booking_id', $id);
            $this->db->delete('reviews');
        }
        
        // Finally, delete the booking itself
        $this->db->where('id', $id);
        $result = $this->db->delete('bookings');
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $result;
    }
    
    /**
     * Get total bookings count
     */
    public function get_bookings_count() {
        return $this->db->count_all('bookings');
    }
    
    /**
     * Get pending bookings count
     */
    public function get_pending_bookings_count() {
        $this->db->where('status', 'pending');
        return $this->db->count_all_results('bookings');
    }
    
    /**
     * Get confirmed bookings count
     */
    public function get_confirmed_bookings_count() {
        $this->db->where('status', 'confirmed');
        return $this->db->count_all_results('bookings');
    }
    
    /**
     * Get total revenue from confirmed bookings
     */
    public function get_total_revenue() {
        $this->db->select_sum('total_amount');
        $this->db->where('status', 'confirmed');
        $result = $this->db->get('bookings')->row();
        return $result->total_amount ? $result->total_amount : 0;
    }
    
    /**
     * Generate unique booking number
     */
    public function generate_booking_number() {
        $this->db->select_max('id');
        $result = $this->db->get('bookings')->row();
        $next_id = ($result->id ? $result->id : 0) + 1;
        return 'BK' . str_pad($next_id, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get booking by booking number
     */
    public function get_booking_by_number($booking_number) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.price');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.booking_number', $booking_number);
        return $this->db->get()->row();
    }
    
    /**
     * Check room availability for date range
     */
    public function check_room_availability($room_id, $check_in, $check_out, $exclude_booking_id = null, $requested_rooms = 1) {
        // Ensure dates are in YYYY-MM-DD format
        $check_in = date('Y-m-d', strtotime($check_in));
        $check_out = date('Y-m-d', strtotime($check_out));
        
        // Get room information to check available_rooms count
        $this->load->model('Room_model');
        $room = $this->Room_model->get_room($room_id);
        
        if (!$room) {
            return false; // Room doesn't exist
        }
        
        $available_rooms = isset($room->available_rooms) ? (int)$room->available_rooms : 1;
        
        // Count how many rooms are already booked for the overlapping dates
        // Count total rooms booked using the helper method
        $booked_count = $this->count_booked_rooms($room_id, $check_in, $check_out, $exclude_booking_id);
        
        // Check if there are enough available rooms
        $remaining_rooms = $available_rooms - $booked_count;
        
        return $remaining_rooms >= $requested_rooms;
    }
    
    /**
     * Get conflicting bookings for debugging
     */
    public function get_conflicting_bookings($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        // Ensure dates are in YYYY-MM-DD format
        $check_in = date('Y-m-d', strtotime($check_in));
        $check_out = date('Y-m-d', strtotime($check_out));
        
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.available_rooms');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.room_id', $room_id);
        $this->db->where('bookings.status !=', 'cancelled');
        $this->db->group_start();
        $this->db->where("DATE(bookings.check_in) < '{$check_out}'", NULL, FALSE);
        $this->db->where("DATE(bookings.check_out) > '{$check_in}'", NULL, FALSE);
        $this->db->group_end();
        if ($exclude_booking_id) {
            $this->db->where('bookings.id !=', $exclude_booking_id);
        }
        return $this->db->get()->result();
    }
    
    /**
     * Count booked rooms for a date range
     * Now uses booking_items table for accurate counting
     */
    public function count_booked_rooms($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        // Ensure dates are in YYYY-MM-DD format
        $check_in = date('Y-m-d', strtotime($check_in));
        $check_out = date('Y-m-d', strtotime($check_out));
        
        // Check if booking_items table exists
        if ($this->db->table_exists('booking_items')) {
            // Count booking items (each item = 1 room)
            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('booking_items.room_id', $room_id);
            $this->db->where('bookings.status !=', 'cancelled');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->group_start();
            $this->db->where("DATE(booking_items.check_in) < '{$check_out}'", NULL, FALSE);
            $this->db->where("DATE(booking_items.check_out) > '{$check_in}'", NULL, FALSE);
            $this->db->group_end();
            if ($exclude_booking_id) {
                $this->db->where('booking_items.booking_id !=', $exclude_booking_id);
            }
            return $this->db->count_all_results();
        }
        
        // Fallback: Check if bookings table has a 'rooms' column
        $fields = $this->db->list_fields('bookings');
        $has_rooms_field = in_array('rooms', $fields);
        
        if ($has_rooms_field) {
            // Sum the rooms field from all conflicting bookings
            $this->db->select_sum('rooms');
            $this->db->where('room_id', $room_id);
            $this->db->where('status !=', 'cancelled');
            $this->db->group_start();
            $this->db->where("DATE(check_in) < '{$check_out}'", NULL, FALSE);
            $this->db->where("DATE(check_out) > '{$check_in}'", NULL, FALSE);
            $this->db->group_end();
            if ($exclude_booking_id) {
                $this->db->where('id !=', $exclude_booking_id);
            }
            $result = $this->db->get('bookings')->row();
            return isset($result->rooms) ? (int)$result->rooms : 0;
        } else {
            // No rooms field, count each booking as 1 room
            $this->db->where('room_id', $room_id);
            $this->db->where('status !=', 'cancelled');
            $this->db->group_start();
            $this->db->where("DATE(check_in) < '{$check_out}'", NULL, FALSE);
            $this->db->where("DATE(check_out) > '{$check_in}'", NULL, FALSE);
            $this->db->group_end();
            if ($exclude_booking_id) {
                $this->db->where('id !=', $exclude_booking_id);
            }
            return $this->db->get('bookings')->num_rows();
        }
    }
    
    /**
     * Get available rooms for date range
     */
    public function get_available_rooms($check_in, $check_out, $guests = null) {
        // Get all rooms
        $this->load->model('Room_model');
        $all_rooms = $this->Room_model->get_all_rooms();
        
        $available_rooms = array();
        foreach ($all_rooms as $room) {
            if ($room->status != 'active') {
                continue;
            }
            
            // Check capacity if guests specified
            if ($guests && $room->capacity < $guests) {
                continue;
            }
            
            // Check availability
            if ($this->check_room_availability($room->id, $check_in, $check_out)) {
                $available_rooms[] = $room;
            }
        }
        
        return $available_rooms;
    }
    
    /**
     * Calculate total amount for booking
     */
    public function calculate_total_amount($room_id, $check_in, $check_out, $guests = 1) {
        $this->load->model('Room_model');
        $room = $this->Room_model->get_room($room_id);
        
        if (!$room) {
            return 0;
        }
        
        // Calculate nights
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $nights = $check_in_date->diff($check_out_date)->days;
        $nights = max($nights, 1);
        
        // Calculate total based on room price
        // Assuming price is per night for most rooms, per head for dormitory
        if (stripos($room->room_type, 'dormitory') !== false) {
            $total = $room->price * $guests * $nights;
        } else {
            $total = $room->price * $nights;
        }
        
        return $total;
    }
    
    /**
     * Get bookings by status
     */
    public function get_bookings_by_status($status) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.status', $status);
        $this->db->order_by('bookings.created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    /**
     * Get bookings by date range
     */
    public function get_bookings_by_date_range($start_date, $end_date) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.check_in >=', $start_date);
        $this->db->where('bookings.check_in <=', $end_date);
        $this->db->order_by('bookings.check_in', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Get bookings formatted for calendar display
     * Returns bookings with date range information for calendar rendering
     * Gets all bookings that overlap with the specified date range
     */
    public function get_bookings_for_calendar($start_date = null, $end_date = null) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.available_rooms');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.status !=', 'cancelled');
        
        // Get bookings that overlap with the date range
        // A booking overlaps if: check_in <= end_date AND check_out >= start_date
        if ($start_date && $end_date) {
            $this->db->where("DATE(bookings.check_in) <= '{$end_date}'", NULL, FALSE);
            $this->db->where("DATE(bookings.check_out) >= '{$start_date}'", NULL, FALSE);
        } elseif ($start_date) {
            $this->db->where("DATE(bookings.check_out) >= '{$start_date}'", NULL, FALSE);
        } elseif ($end_date) {
            $this->db->where("DATE(bookings.check_in) <= '{$end_date}'", NULL, FALSE);
        }
        
        $this->db->order_by('bookings.check_in', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Get room availability summary for a specific date
     * Returns array with room_id => array('available' => X, 'booked' => Y, 'remaining' => Z)
     */
    public function get_room_availability_for_date($date) {
        $this->load->model('Room_model');
        $all_rooms = $this->Room_model->get_all_rooms();
        
        $availability = array();
        
        foreach ($all_rooms as $room) {
            if ($room->status != 'active') {
                continue;
            }
            
            $available_rooms = isset($room->available_rooms) ? (int)$room->available_rooms : 1;
            
            // Count booked rooms for this specific date
            $booked_rooms = $this->count_booked_rooms_for_date($room->id, $date);
            $remaining_rooms = $available_rooms - $booked_rooms;
            
            $availability[$room->id] = array(
                'room_id' => $room->id,
                'room_name' => $room->room_name,
                'room_type' => $room->room_type,
                'available' => $available_rooms,
                'booked' => $booked_rooms,
                'remaining' => max(0, $remaining_rooms)
            );
        }
        
        return $availability;
    }
    
    /**
     * Count booked rooms for a specific date
     */
    public function count_booked_rooms_for_date($room_id, $date) {
        $date = date('Y-m-d', strtotime($date));
        
        // Check if booking_items table exists (more accurate)
        if ($this->db->table_exists('booking_items')) {
            $this->db->from('booking_items');
            $this->db->join('bookings', 'bookings.id = booking_items.booking_id', 'inner');
            $this->db->where('booking_items.room_id', $room_id);
            $this->db->where('bookings.status !=', 'cancelled');
            $this->db->where('booking_items.status !=', 'cancelled');
            $this->db->where("DATE(booking_items.check_in) <= '{$date}'", NULL, FALSE);
            $this->db->where("DATE(booking_items.check_out) > '{$date}'", NULL, FALSE);
            return $this->db->count_all_results();
        }
        
        // Check if bookings table has a 'rooms' column
        $fields = $this->db->list_fields('bookings');
        $has_rooms_field = in_array('rooms', $fields);
        
        if ($has_rooms_field) {
            // Sum the rooms field from all bookings that include this date
            $this->db->select_sum('rooms');
            $this->db->where('room_id', $room_id);
            $this->db->where('status !=', 'cancelled');
            $this->db->where("DATE(check_in) <= '{$date}'", NULL, FALSE);
            $this->db->where("DATE(check_out) > '{$date}'", NULL, FALSE);
            $result = $this->db->get('bookings')->row();
            return isset($result->rooms) ? (int)$result->rooms : 0;
        } else {
            // No rooms field, count each booking as 1 room
            $this->db->where('room_id', $room_id);
            $this->db->where('status !=', 'cancelled');
            $this->db->where("DATE(check_in) <= '{$date}'", NULL, FALSE);
            $this->db->where("DATE(check_out) > '{$date}'", NULL, FALSE);
            return $this->db->get('bookings')->num_rows();
        }
    }
    
    /**
     * Get room availability for date range
     * Returns array of dates with availability info for each room
     */
    public function get_room_availability_for_range($start_date, $end_date, $room_id = null) {
        $this->load->model('Room_model');
        
        // Get rooms to check
        if ($room_id) {
            $rooms = array($this->Room_model->get_room($room_id));
            $rooms = array_filter($rooms); // Remove null values
        } else {
            $rooms = $this->Room_model->get_all_rooms();
        }
        
        $availability_data = array();
        $current_date = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        // Loop through each date in the range
        while ($current_date <= $end) {
            $date_str = $current_date->format('Y-m-d');
            $availability_data[$date_str] = array();
            
            // Get availability for each room on this date
            foreach ($rooms as $room) {
                // Include all rooms, but mark inactive ones
                $available_rooms = isset($room->available_rooms) ? (int)$room->available_rooms : 1;
                
                // For inactive rooms, set booked to total to show as unavailable
                if ($room->status != 'active') {
                    $booked_rooms = $available_rooms; // Show as fully booked
                    $remaining_rooms = 0;
                    $status = 'inactive';
                } else {
                    $booked_rooms = $this->count_booked_rooms_for_date($room->id, $date_str);
                    $remaining_rooms = max(0, $available_rooms - $booked_rooms);
                    $status = $remaining_rooms > 0 ? ($remaining_rooms == $available_rooms ? 'available' : 'partial') : 'booked';
                }
                
                $availability_data[$date_str][$room->id] = array(
                    'room_id' => $room->id,
                    'room_name' => $room->room_name,
                    'room_type' => $room->room_type,
                    'room_code' => isset($room->room_code) ? $room->room_code : '',
                    'total_available' => $available_rooms,
                    'booked' => $booked_rooms,
                    'remaining' => $remaining_rooms,
                    'is_available' => $remaining_rooms > 0 && $room->status == 'active',
                    'status' => $status,
                    'room_status' => $room->status
                );
            }
            
            $current_date->modify('+1 day');
        }
        
        return $availability_data;
    }
    
    /**
     * Get daily sales data for a specific date
     * Returns bookings created or with check-in on the specified date
     */
    public function get_daily_sales($date) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.room_code, rooms.price');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->group_start();
        $this->db->where("DATE(bookings.created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(bookings.check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        $this->db->order_by('bookings.created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    /**
     * Get total revenue for a specific date
     * Sum of total_amount from bookings created or with check-in on the specified date
     */
    public function get_daily_total_revenue($date) {
        $this->db->select_sum('total_amount');
        $this->db->group_start();
        $this->db->where("DATE(created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        $result = $this->db->get('bookings')->row();
        return $result->total_amount ? $result->total_amount : 0;
    }
    
    /**
     * Get total bookings count for a specific date
     */
    public function get_daily_bookings_count($date) {
        $this->db->group_start();
        $this->db->where("DATE(created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        return $this->db->count_all_results('bookings');
    }
    
    /**
     * Get confirmed bookings count for a specific date
     */
    public function get_daily_confirmed_bookings_count($date) {
        $this->db->where('status', 'confirmed');
        $this->db->group_start();
        $this->db->where("DATE(created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        return $this->db->count_all_results('bookings');
    }
    
    /**
     * Get pending bookings count for a specific date
     */
    public function get_daily_pending_bookings_count($date) {
        $this->db->where('status', 'pending');
        $this->db->group_start();
        $this->db->where("DATE(created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        return $this->db->count_all_results('bookings');
    }
    
    /**
     * Get daily sales grouped by room type
     */
    public function get_daily_sales_by_room($date) {
        $this->db->select('rooms.room_type, rooms.room_name, COUNT(bookings.id) as booking_count, SUM(bookings.total_amount) as total_revenue');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->group_start();
        $this->db->where("DATE(bookings.created_at) = '{$date}'", NULL, FALSE);
        $this->db->or_where("DATE(bookings.check_in) = '{$date}'", NULL, FALSE);
        $this->db->group_end();
        $this->db->group_by('rooms.room_type, rooms.room_name');
        $this->db->order_by('total_revenue', 'DESC');
        return $this->db->get()->result();
    }
}

