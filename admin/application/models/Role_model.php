<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all roles
     */
    public function get_all($status = null) {
        // Reset any previous query
        $this->db->reset_query();
        
        // Only filter by status if explicitly provided
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('roles');
        
        // Debug logging (can be removed in production)
        log_message('debug', 'Role_model::get_all - Query: ' . $this->db->last_query());
        log_message('debug', 'Role_model::get_all - Rows: ' . $query->num_rows());
        
        return $query->result();
    }
    
    /**
     * Get role by ID
     */
    public function get($id) {
        $this->db->where('id', $id);
        return $this->db->get('roles')->row();
    }
    
    /**
     * Get role by slug
     */
    public function get_by_slug($slug) {
        $this->db->where('slug', $slug);
        return $this->db->get('roles')->row();
    }
    
    /**
     * Create a new role
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('roles', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update role
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('roles', $data);
    }
    
    /**
     * Delete role
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('roles');
    }
    
    /**
     * Get permissions for a specific role
     */
    public function get_role_permissions($role_id) {
        $this->db->select('permissions.*');
        $this->db->from('permissions');
        $this->db->join('role_permissions', 'role_permissions.permission_id = permissions.id');
        $this->db->where('role_permissions.role_id', $role_id);
        return $this->db->get()->result();
    }
    
    /**
     * Assign permissions to a role
     */
    public function assign_permissions_to_role($role_id, $permission_ids) {
        // Remove existing assignments
        $this->db->where('role_id', $role_id);
        $this->db->delete('role_permissions');
        
        // Add new assignments
        if (!empty($permission_ids)) {
            foreach ($permission_ids as $permission_id) {
                $this->db->insert('role_permissions', array(
                    'role_id' => $role_id,
                    'permission_id' => $permission_id,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
        }
        return true;
    }
    
    /**
     * Check if role has a specific permission
     */
    public function has_permission($role_id, $permission_slug) {
        $this->db->select('role_permissions.*');
        $this->db->from('role_permissions');
        $this->db->join('permissions', 'permissions.id = role_permissions.permission_id');
        $this->db->where('role_permissions.role_id', $role_id);
        $this->db->where('permissions.slug', $permission_slug);
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }
    
    /**
     * Add a single permission to a role (without removing existing ones)
     */
    public function add_permission_to_role($role_id, $permission_id) {
        // Check if already assigned
        $this->db->where('role_id', $role_id);
        $this->db->where('permission_id', $permission_id);
        $existing = $this->db->get('role_permissions')->row();
        
        if (!$existing) {
            $this->db->insert('role_permissions', array(
                'role_id' => $role_id,
                'permission_id' => $permission_id,
                'created_at' => date('Y-m-d H:i:s')
            ));
            return $this->db->insert_id();
        }
        return false; // Already exists
    }
    
    /**
     * Get Super Admin role ID (role with slug 'super_admin' or id = 1)
     */
    public function get_super_admin_role_id() {
        // First try to find by slug
        $super_admin = $this->get_by_slug('super_admin');
        if ($super_admin) {
            return $super_admin->id;
        }
        
        // Fallback to role_id = 1
        $role = $this->get(1);
        if ($role) {
            return $role->id;
        }
        
        return null;
    }
}

