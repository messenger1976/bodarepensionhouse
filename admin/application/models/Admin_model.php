<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function login($username, $password) {
        $this->db->where('username', $username);
        $this->db->where('status', 'active');
        $query = $this->db->get('admins');
        
        if ($query->num_rows() == 1) {
            $admin = $query->row();
            if (password_verify($password, $admin->password)) {
                return $admin;
            }
        }
        return false;
    }
    
    public function get_admin($id) {
        $this->db->where('id', $id);
        return $this->db->get('admins')->row();
    }
    
    /**
     * Get all admins
     */
    public function get_all($status = null) {
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get('admins')->result();
    }
    
    /**
     * Get admin groups
     */
    public function get_admin_groups($admin_id) {
        $this->load->model('User_group_model');
        return $this->User_group_model->get_admin_groups($admin_id);
    }
    
    /**
     * Get admin roles (through groups)
     */
    public function get_admin_roles($admin_id) {
        $this->db->distinct();
        $this->db->select('roles.*');
        $this->db->from('roles');
        $this->db->join('group_roles', 'group_roles.role_id = roles.id');
        $this->db->join('admin_user_groups', 'admin_user_groups.group_id = group_roles.group_id');
        $this->db->where('admin_user_groups.admin_id', $admin_id);
        $this->db->where('roles.status', 'active');
        return $this->db->get()->result();
    }
    
    /**
     * Get admin permissions (through roles)
     */
    public function get_admin_permissions($admin_id) {
        $this->db->distinct();
        $this->db->select('permissions.*');
        $this->db->from('permissions');
        $this->db->join('role_permissions', 'role_permissions.permission_id = permissions.id');
        $this->db->join('roles', 'roles.id = role_permissions.role_id');
        $this->db->join('group_roles', 'group_roles.role_id = roles.id');
        $this->db->join('admin_user_groups', 'admin_user_groups.group_id = group_roles.group_id');
        $this->db->where('admin_user_groups.admin_id', $admin_id);
        $this->db->where('roles.status', 'active');
        return $this->db->get()->result();
    }
    
    /**
     * Check if admin has a specific permission
     */
    public function has_permission($admin_id, $permission_slug) {
        // If no permission system tables exist, return true for super admin
        if (!$this->db->table_exists('permissions')) {
            // Check if user is the first admin (super admin)
            $admin = $this->get_admin($admin_id);
            if ($admin && $admin->id == 1) {
                return true; // First admin is super admin
            }
            return false;
        }
        
        $this->db->select('permissions.id');
        $this->db->from('permissions');
        $this->db->join('role_permissions', 'role_permissions.permission_id = permissions.id');
        $this->db->join('roles', 'roles.id = role_permissions.role_id');
        $this->db->join('group_roles', 'group_roles.role_id = roles.id');
        $this->db->join('admin_user_groups', 'admin_user_groups.group_id = group_roles.group_id');
        $this->db->where('admin_user_groups.admin_id', $admin_id);
        $this->db->where('permissions.slug', $permission_slug);
        $this->db->where('roles.status', 'active');
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }
    
    /**
     * Check if admin has a specific role
     */
    public function has_role($admin_id, $role_slug) {
        if (!$this->db->table_exists('roles')) {
            return false;
        }
        
        $this->db->select('roles.id');
        $this->db->from('roles');
        $this->db->join('group_roles', 'group_roles.role_id = roles.id');
        $this->db->join('admin_user_groups', 'admin_user_groups.group_id = group_roles.group_id');
        $this->db->where('admin_user_groups.admin_id', $admin_id);
        $this->db->where('roles.slug', $role_slug);
        $this->db->where('roles.status', 'active');
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }
    
    /**
     * Check if admin is super admin
     * Super admin is determined by:
     * 1. Has 'super_admin' role, OR
     * 2. Is the first admin (admin_id = 1) as fallback
     */
    public function is_super_admin($admin_id) {
        // Check if admin_id is 1 (first admin is super admin)
        if ($admin_id == 1) {
            return true;
        }
        
        // Check if has super_admin role
        if ($this->db->table_exists('roles')) {
            return $this->has_role($admin_id, 'super_admin');
        }
        
        return false;
    }
    
    /**
     * Create a new admin
     */
    public function create($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('admins', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update admin
     */
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('admins', $data);
    }
    
    /**
     * Delete admin
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('admins');
    }
}

