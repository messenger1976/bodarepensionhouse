<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inquiry_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new inquiry
     */
    public function create($data) {
        try {
            // Remove created_at if already set to avoid duplicate
            if (isset($data['created_at'])) {
                unset($data['created_at']);
            }
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $this->db->insert('inquiries', $data);
            
            if ($result) {
                return $this->db->insert_id();
            } else {
                log_message('error', 'Inquiry insert failed: ' . $this->db->error()['message']);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Inquiry creation exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get inquiry by ID
     */
    public function get_inquiry($id) {
        $this->db->where('id', $id);
        return $this->db->get('inquiries')->row();
    }
    
    /**
     * Get inquiries by user ID
     */
    public function get_user_inquiries($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('inquiries')->result();
    }
    
    /**
     * Get all inquiries (for admin)
     */
    public function get_all($status = null) {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('inquiries')->result();
    }
    
    /**
     * Update inquiry
     */
    public function update_inquiry($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('inquiries', $data);
    }
    
    /**
     * Delete inquiry
     */
    public function delete_inquiry($id) {
        $this->db->where('id', $id);
        return $this->db->delete('inquiries');
    }
}

