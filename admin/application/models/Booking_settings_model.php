<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_settings_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all booking settings
     */
    public function get_all_settings() {
        $settings = $this->db->get('booking_settings')->result();
        $result = array();
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = $setting->setting_value;
        }
        return $result;
    }
    
    /**
     * Get a specific setting
     */
    public function get_setting($key, $default = null) {
        $this->db->where('setting_key', $key);
        $result = $this->db->get('booking_settings')->row();
        return $result ? $result->setting_value : $default;
    }
    
    /**
     * Update a setting
     */
    public function update_setting($key, $value) {
        $this->db->where('setting_key', $key);
        $exists = $this->db->get('booking_settings')->row();
        
        if ($exists) {
            $this->db->where('setting_key', $key);
            return $this->db->update('booking_settings', array(
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        } else {
            return $this->db->insert('booking_settings', array(
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }
    }
    
    /**
     * Update multiple settings
     */
    public function update_settings($settings) {
        foreach ($settings as $key => $value) {
            $this->update_setting($key, $value);
        }
        return true;
    }
}

