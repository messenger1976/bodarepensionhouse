<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tokens for the admin panel: password resets and account activation.
 */
class Admin_token_model extends CI_Model {

    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_ACTIVATION = 'activation';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create a token for an admin. Replaces any previous unused token of the same type.
     */
    public function create_token($admin_id, $type, $expires_at) {
        $this->db->where('admin_id', $admin_id);
        $this->db->where('type', $type);
        $this->db->delete('admin_tokens');

        $token = bin2hex(random_bytes(32));

        $this->db->insert('admin_tokens', array(
            'admin_id' => $admin_id,
            'token' => $token,
            'type' => $type,
            'expires_at' => $expires_at,
            'used' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ));

        return $this->db->affected_rows() ? $token : false;
    }

    /**
     * Get a valid (unused, unexpired) token row.
     */
    public function get_valid_token($token, $type) {
        $this->db->where('token', $token);
        $this->db->where('type', $type);
        $this->db->where('used', 0);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        return $this->db->get('admin_tokens')->row();
    }

    public function mark_as_used($token) {
        $this->db->where('token', $token);
        return $this->db->update('admin_tokens', array('used' => 1));
    }

    public function delete_for_admin($admin_id, $type) {
        $this->db->where('admin_id', $admin_id);
        $this->db->where('type', $type);
        return $this->db->delete('admin_tokens');
    }
}
