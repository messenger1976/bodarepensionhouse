<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_reset_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a password reset token
     */
    public function create_token($email, $token, $expires_at) {
        // Delete any existing tokens for this email
        $this->db->where('email', $email);
        $this->db->delete('password_resets');
        
        // Create new token
        $data = array(
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires_at,
            'used' => 0
        );
        
        return $this->db->insert('password_resets', $data);
    }
    
    /**
     * Get token by token string
     */
    public function get_token($token) {
        $this->db->where('token', $token);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        return $this->db->get('password_resets')->row();
    }
    
    /**
     * Mark token as used
     */
    public function mark_as_used($token) {
        $this->db->where('token', $token);
        return $this->db->update('password_resets', array('used' => 1));
    }
    
    /**
     * Clean up expired tokens
     */
    public function cleanup_expired() {
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        $this->db->or_where('used', 1);
        return $this->db->delete('password_resets');
    }
    
    /**
     * Get token by email
     */
    public function get_token_by_email($email) {
        $this->db->where('email', $email);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('password_resets')->row();
    }
}

