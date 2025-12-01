<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Room_image_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all images for a room
     */
    public function get_room_images($room_id) {
        $this->db->where('room_id', $room_id);
        $this->db->order_by('display_order', 'ASC');
        $this->db->order_by('is_primary', 'DESC');
        return $this->db->get('room_images')->result();
    }
    
    /**
     * Get primary image for a room
     */
    public function get_primary_image($room_id) {
        $this->db->where('room_id', $room_id);
        $this->db->where('is_primary', 1);
        $this->db->limit(1);
        return $this->db->get('room_images')->row();
    }
    
    /**
     * Get image by ID
     */
    public function get_image($id) {
        $this->db->where('id', $id);
        return $this->db->get('room_images')->row();
    }
    
    /**
     * Add image to room
     */
    public function add_image($data) {
        $this->db->insert('room_images', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update image
     */
    public function update_image($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('room_images', $data);
    }
    
    /**
     * Delete image
     */
    public function delete_image($id) {
        $image = $this->get_image($id);
        if ($image) {
            // Delete physical file
            $file_path = FCPATH . $image->image_path;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
            // Delete database record
            $this->db->where('id', $id);
            return $this->db->delete('room_images');
        }
        return false;
    }
    
    /**
     * Set primary image
     */
    public function set_primary_image($room_id, $image_id) {
        // Remove primary flag from all images of this room
        $this->db->where('room_id', $room_id);
        $this->db->update('room_images', array('is_primary' => 0));
        
        // Set new primary image
        $this->db->where('id', $image_id);
        $this->db->where('room_id', $room_id);
        return $this->db->update('room_images', array('is_primary' => 1));
    }
    
    /**
     * Update display order
     */
    public function update_display_order($image_id, $display_order) {
        $this->db->where('id', $image_id);
        return $this->db->update('room_images', array('display_order' => $display_order));
    }
    
    /**
     * Get image count for room
     */
    public function get_image_count($room_id) {
        $this->db->where('room_id', $room_id);
        return $this->db->count_all_results('room_images');
    }
}

