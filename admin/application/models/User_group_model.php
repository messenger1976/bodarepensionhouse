<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_group_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all user groups
     */
    public function get_all($status = null) {
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get('user_groups')->result();
    }
    
    /**
     * Get user group by ID
     */
    public function get($id) {
        $this->db->where('id', $id);
        return $this->db->get('user_groups')->row();
    }
    
    /**
     * Create a new user group
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('user_groups', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update user group
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('user_groups', $data);
    }
    
    /**
     * Delete user group
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('user_groups');
    }
    
    /**
     * Get groups for a specific admin
     */
    public function get_admin_groups($admin_id) {
        $this->db->select('user_groups.*');
        $this->db->from('user_groups');
        $this->db->join('admin_user_groups', 'admin_user_groups.group_id = user_groups.id');
        $this->db->where('admin_user_groups.admin_id', $admin_id);
        $this->db->where('user_groups.status', 'active');
        return $this->db->get()->result();
    }
    
    /**
     * Assign groups to an admin
     */
    public function assign_groups_to_admin($admin_id, $group_ids) {
        // Remove existing assignments
        $this->db->where('admin_id', $admin_id);
        $this->db->delete('admin_user_groups');
        
        // Add new assignments
        if (!empty($group_ids)) {
            foreach ($group_ids as $group_id) {
                $this->db->insert('admin_user_groups', array(
                    'admin_id' => $admin_id,
                    'group_id' => $group_id,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
        }
        return true;
    }
    
    /**
     * Get roles for a specific group
     */
    public function get_group_roles($group_id) {
        $this->db->select('roles.*');
        $this->db->from('roles');
        $this->db->join('group_roles', 'group_roles.role_id = roles.id');
        $this->db->where('group_roles.group_id', $group_id);
        $this->db->where('roles.status', 'active');
        return $this->db->get()->result();
    }
    
    /**
     * Assign roles to a group
     */
    public function assign_roles_to_group($group_id, $role_ids) {
        // Remove existing assignments
        $this->db->where('group_id', $group_id);
        $this->db->delete('group_roles');
        
        // Add new assignments
        if (!empty($role_ids)) {
            foreach ($role_ids as $role_id) {
                $this->db->insert('group_roles', array(
                    'group_id' => $group_id,
                    'role_id' => $role_id,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
        }
        return true;
    }
}

