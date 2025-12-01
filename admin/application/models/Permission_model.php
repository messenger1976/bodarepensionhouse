<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all permissions
     */
    public function get_all($module = null) {
        if ($module !== null) {
            $this->db->where('module', $module);
        }
        $this->db->order_by('module', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get('permissions')->result();
    }
    
    /**
     * Get permission by ID
     */
    public function get($id) {
        $this->db->where('id', $id);
        return $this->db->get('permissions')->row();
    }
    
    /**
     * Get permission by slug
     */
    public function get_by_slug($slug) {
        $this->db->where('slug', $slug);
        return $this->db->get('permissions')->row();
    }
    
    /**
     * Create a new permission
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('permissions', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update permission
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('permissions', $data);
    }
    
    /**
     * Delete permission
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('permissions');
    }
    
    /**
     * Get permissions grouped by module
     */
    public function get_by_module() {
        $permissions = $this->get_all();
        $grouped = array();
        
        foreach ($permissions as $permission) {
            $module = $permission->module ? $permission->module : 'general';
            if (!isset($grouped[$module])) {
                $grouped[$module] = array();
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
}

