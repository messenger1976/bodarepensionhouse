<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends MY_Controller {
    
    protected $admin_id;
    protected $admin_data;
    
    public function __construct() {
        parent::__construct();
        
        // Prevent browser caching for Firefox and other browsers
        // Use CodeIgniter's output class to set headers properly
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        $this->output->set_header('ETag: "' . md5(time()) . '"');
        
        // Also set raw headers as backup (must be before any output)
        if (!headers_sent()) {
            header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        }
        
        $this->load->library('session');
        $this->load->model('Admin_model');
        
        // Check if user is logged in
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('login');
        }
        
        // Load admin data - ensure admin_id is valid
        $session_admin_id = $this->session->userdata('admin_id');
        
        // Validate admin_id exists and is numeric
        if (empty($session_admin_id) || !is_numeric($session_admin_id)) {
            // Invalid session, destroy it and redirect to login
            log_message('error', 'Invalid admin_id in session: ' . var_export($session_admin_id, true));
            $this->session->sess_destroy();
            redirect('login');
        }
        
        // Cast to integer to ensure type consistency
        $this->admin_id = (int)$session_admin_id;
        
        
        // Get admin data from database using the session admin_id
        $this->admin_data = $this->Admin_model->get_admin($this->admin_id);
        
        // If admin not found in database, destroy session and redirect
        if (!$this->admin_data) {
            log_message('error', 'Admin not found in database for admin_id: ' . $this->admin_id);
            $this->session->sess_destroy();
            redirect('login');
        }
        
        // CRITICAL: Verify that the admin_id from session matches the admin_data->id from database
        // This prevents session hijacking or data corruption
        $db_admin_id = (int)$this->admin_data->id;
        if ($db_admin_id != $this->admin_id) {
            // Mismatch detected - destroy session and redirect to login
            log_message('error', 'Session admin_id mismatch: Session has ' . $this->admin_id . ' but database returned admin_id ' . $db_admin_id . ' (username: ' . $this->admin_data->username . ')');
            $this->session->sess_destroy();
            redirect('login');
        }
        
        // Only refresh session data if username or name changed (to avoid unnecessary writes)
        $current_username = $this->session->userdata('admin_username');
        $current_name = $this->session->userdata('admin_name');
        $new_name = $this->admin_data->name ? $this->admin_data->name : $this->admin_data->username;
        
        if ($current_username != $this->admin_data->username || $current_name != $new_name) {
            // Refresh session data from database to ensure it's up-to-date
            $session_data = array(
                'admin_id' => $this->admin_id, // Keep the same admin_id from session
                'admin_username' => $this->admin_data->username,
                'admin_name' => $new_name,
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

