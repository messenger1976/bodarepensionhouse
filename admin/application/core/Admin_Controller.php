<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends MY_Controller {
    
    protected $admin_id;
    protected $admin_data;
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Admin_model');
        
        // Check if user is logged in
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('login');
        }
        
        // Load admin data
        $this->admin_id = $this->session->userdata('admin_id');
        $this->admin_data = $this->Admin_model->get_admin($this->admin_id);
        
        // Refresh session data from database to ensure it's up-to-date
        if ($this->admin_data) {
            $session_data = array(
                'admin_id' => $this->admin_data->id,
                'admin_username' => $this->admin_data->username,
                'admin_name' => $this->admin_data->name ? $this->admin_data->name : $this->admin_data->username,
                'admin_logged_in' => TRUE
            );
            $this->session->set_userdata($session_data);
        }
    }
    
    /**
     * Check if current admin has a specific permission
     * 
     * @param string $permission_slug The permission slug to check
     * @return bool
     */
    protected function has_permission($permission_slug) {
        return $this->Admin_model->has_permission($this->admin_id, $permission_slug);
    }
    
    /**
     * Check if current admin has a specific role
     * 
     * @param string $role_slug The role slug to check
     * @return bool
     */
    protected function has_role($role_slug) {
        return $this->Admin_model->has_role($this->admin_id, $role_slug);
    }
    
    /**
     * Require a specific permission, redirect if not authorized
     * 
     * @param string $permission_slug The permission slug required
     * @param string $redirect_url URL to redirect if unauthorized
     */
    protected function require_permission($permission_slug, $redirect_url = 'dashboard') {
        if (!$this->has_permission($permission_slug)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect($redirect_url);
        }
    }
    
    /**
     * Require a specific role, redirect if not authorized
     * 
     * @param string $role_slug The role slug required
     * @param string $redirect_url URL to redirect if unauthorized
     */
    protected function require_role($role_slug, $redirect_url = 'dashboard') {
        if (!$this->has_role($role_slug)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect($redirect_url);
        }
    }
    
    /**
     * Get all permissions for current admin
     * 
     * @return array
     */
    protected function get_admin_permissions() {
        return $this->Admin_model->get_admin_permissions($this->admin_id);
    }
    
    /**
     * Get all roles for current admin
     * 
     * @return array
     */
    protected function get_admin_roles() {
        return $this->Admin_model->get_admin_roles($this->admin_id);
    }
    
    /**
     * Get all groups for current admin
     * 
     * @return array
     */
    protected function get_admin_groups() {
        return $this->Admin_model->get_admin_groups($this->admin_id);
    }
    
    /**
     * Check if current admin is super admin
     * 
     * @return bool
     */
    protected function is_super_admin() {
        return $this->Admin_model->is_super_admin($this->admin_id);
    }
}

