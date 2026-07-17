<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_verification_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create_token($email, $token, $expires_at) {
        $this->db->where('email', $email);
        $this->db->delete('email_verifications');

        return $this->db->insert('email_verifications', array(
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires_at,
            'used' => 0
        ));
    }

    public function get_token($token) {
        $this->db->where('token', $token);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        return $this->db->get('email_verifications')->row();
    }

    public function mark_as_used($token) {
        $this->db->where('token', $token);
        return $this->db->update('email_verifications', array('used' => 1));
    }

    public function delete_by_email($email) {
        $this->db->where('email', $email);
        return $this->db->delete('email_verifications');
    }
}
