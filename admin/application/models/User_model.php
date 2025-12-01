<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Register a new user
     */
    public function register($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        // Trim and lowercase email for consistency
        $email = trim(strtolower($email));
        
        $this->db->where('LOWER(email)', strtolower($email));
        $query = $this->db->get('users');
        
        if ($query->num_rows() == 1) {
            $user = $query->row();
            
            // Check if user is active
            if ($user->status != 'active') {
                return false;
            }
            
            // Verify password
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function get_user($id) {
        $this->db->where('id', $id);
        return $this->db->get('users')->row();
    }
    
    /**
     * Get user by email
     */
    public function get_user_by_email($email) {
        $this->db->where('LOWER(email)', strtolower(trim($email)));
        return $this->db->get('users')->row();
    }
    
    /**
     * Update user
     */
    public function update_user($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
    
    /**
     * Check if email exists
     */
    public function email_exists($email, $exclude_id = null) {
        $this->db->where('LOWER(email)', strtolower(trim($email)));
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('users')->num_rows() > 0;
    }
    
    /**
     * Get user bookings
     */
    public function get_user_bookings($user_id) {
        $this->db->select('bookings.*, rooms.room_name, rooms.room_type, rooms.price');
        $this->db->from('bookings');
        $this->db->join('rooms', 'rooms.id = bookings.room_id', 'left');
        $this->db->where('bookings.user_id', $user_id);
        $this->db->order_by('bookings.created_at', 'DESC');
        return $this->db->get()->result();
    }
}

