<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_item_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new booking item
     */
    public function create_booking_item($data) {
        // Check if table exists
        if (!$this->db->table_exists('booking_items')) {
            error_log('ERROR: booking_items table does not exist in create_booking_item');
            return false;
        }
        
        // Validate required fields
        $required_fields = array('booking_id', 'room_id', 'room_name', 'check_in', 'check_out', 'price_per_night', 'nights', 'subtotal');
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                error_log('ERROR: Missing required field in booking_item_data: ' . $field);
                return false;
            }
        }
        
        $result = $this->db->insert('booking_items', $data);
        
        if (!$result) {
            $error = $this->db->error();
            error_log('ERROR inserting booking_item: ' . print_r($error, true));
            error_log('Data attempted: ' . print_r($data, true));
            return false;
        }
        
        $insert_id = $this->db->insert_id();
        error_log('Successfully inserted booking_item with ID: ' . $insert_id);
        return $insert_id;
    }
    
    /**
     * Get all booking items for a booking
     */
    public function get_booking_items($booking_id) {
        $this->db->select('booking_items.*, rooms.room_name, rooms.room_type, rooms.room_code');
        $this->db->from('booking_items');
        $this->db->join('rooms', 'rooms.id = booking_items.room_id', 'left');
        $this->db->where('booking_items.booking_id', $booking_id);
        $this->db->order_by('booking_items.id', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Get booking item by ID
     */
    public function get_booking_item($id) {
        $this->db->select('booking_items.*, rooms.room_name, rooms.room_type, rooms.room_code');
        $this->db->from('booking_items');
        $this->db->join('rooms', 'rooms.id = booking_items.room_id', 'left');
        $this->db->where('booking_items.id', $id);
        return $this->db->get()->row();
    }
    
    /**
     * Update booking item
     */
    public function update_booking_item($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('booking_items', $data);
    }
    
    /**
     * Delete booking item
     */
    public function delete_booking_item($id) {
        $this->db->where('id', $id);
        return $this->db->delete('booking_items');
    }
    
    /**
     * Delete all items for a booking
     */
    public function delete_booking_items($booking_id) {
        $this->db->where('booking_id', $booking_id);
        return $this->db->delete('booking_items');
    }
    
    /**
     * Count booking items for a booking
     */
    public function count_booking_items($booking_id) {
        $this->db->where('booking_id', $booking_id);
        return $this->db->count_all_results('booking_items');
    }
    
    /**
     * Get total amount for all items in a booking
     */
    public function get_booking_items_total($booking_id) {
        $this->db->select_sum('subtotal');
        $this->db->where('booking_id', $booking_id);
        $result = $this->db->get('booking_items')->row();
        return $result->subtotal ? $result->subtotal : 0;
    }
}

