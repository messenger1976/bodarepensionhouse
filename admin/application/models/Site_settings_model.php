<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Website identity / SEO / contact / social settings (`site_settings`).
 * The public site reads the same table through includes/site-config.php.
 */
class Site_settings_model extends CI_Model {

    private $table = 'site_settings';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function table_exists() {
        return $this->db->table_exists($this->table);
    }

    public function get_all_settings() {
        $result = array();
        if (!$this->table_exists()) {
            return $result;
        }
        foreach ($this->db->get($this->table)->result() as $setting) {
            $result[$setting->setting_key] = $setting->setting_value;
        }
        return $result;
    }

    public function get_setting($key, $default = null) {
        if (!$this->table_exists()) {
            return $default;
        }
        $row = $this->db->where('setting_key', $key)->get($this->table)->row();
        return $row ? $row->setting_value : $default;
    }

    public function update_setting($key, $value) {
        $now = date('Y-m-d H:i:s');
        $exists = $this->db->where('setting_key', $key)->get($this->table)->row();
        if ($exists) {
            return $this->db->where('setting_key', $key)->update($this->table, array(
                'setting_value' => $value,
                'updated_at' => $now
            ));
        }
        return $this->db->insert($this->table, array(
            'setting_key' => $key,
            'setting_value' => $value,
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_settings($settings) {
        $this->db->trans_start();
        foreach ($settings as $key => $value) {
            $this->update_setting($key, $value);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
