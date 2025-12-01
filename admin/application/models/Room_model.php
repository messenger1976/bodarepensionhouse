<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Room_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all rooms
     */
    public function get_all_rooms() {
        $this->db->order_by('id', 'ASC');
        return $this->db->get('rooms')->result();
    }
    
    /**
     * Get room by ID
     */
    public function get_room($id) {
        $this->db->where('id', $id);
        return $this->db->get('rooms')->row();
    }
    
    /**
     * Get room by room_code
     */
    public function get_room_by_code($room_code) {
        $this->db->where('room_code', $room_code);
        $this->db->where('status', 'active');
        return $this->db->get('rooms')->row();
    }
    
    /**
     * Create a new room
     */
    public function create_room($data) {
        $this->db->insert('rooms', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update room
     */
    public function update_room($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('rooms', $data);
    }
    
    /**
     * Delete room
     */
    public function delete_room($id) {
        $this->db->where('id', $id);
        return $this->db->delete('rooms');
    }
    
    /**
     * Get total rooms count
     */
    public function get_rooms_count() {
        return $this->db->count_all('rooms');
    }
    
    /**
     * Get active rooms only
     */
    public function get_active_rooms() {
        $this->db->where('status', 'active');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('rooms')->result();
    }
    
    /**
     * Get rooms by type
     */
    public function get_rooms_by_type($room_type) {
        $this->db->where('room_type', $room_type);
        $this->db->where('status', 'active');
        return $this->db->get('rooms')->result();
    }
    
    /**
     * Get rooms by status
     */
    public function get_rooms_by_status($status) {
        $this->db->where('status', $status);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('rooms')->result();
    }
    
    /**
     * Check if room exists
     */
    public function room_exists($id) {
        $this->db->where('id', $id);
        return $this->db->get('rooms')->num_rows() > 0;
    }
    
    /**
     * Get available room count for a date range
     */
    public function get_available_room_count($room_id, $check_in, $check_out) {
        $this->load->model('Booking_model');
        
        // Get total rooms of this type
        $room = $this->get_room($room_id);
        if (!$room) {
            return 0;
        }
        
        // Get booked rooms count for date range
        $this->db->where('room_id', $room_id);
        $this->db->where('status !=', 'cancelled');
        $this->db->group_start();
        $this->db->where('check_in <=', $check_out);
        $this->db->where('check_out >=', $check_in);
        $this->db->group_end();
        $booked_count = $this->db->count_all_results('bookings');
        
        // For now, assuming 1 room per room_id
        // If you have multiple rooms of same type, adjust this logic
        return max(0, 1 - $booked_count);
    }
}

