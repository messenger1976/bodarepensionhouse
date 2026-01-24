<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Admin_model');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    public function index() {
        // Redirect to login if index is accessed
        $this->login();
    }
    
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }
        
        if ($this->input->post()) {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $username = $this->input->post('username');
                $password = $this->input->post('password');
                
                $admin = $this->Admin_model->login($username, $password);
                
                if ($admin) {
                    // IMPORTANT: Use the exact admin ID from the database query result
                    $admin_id = (int)$admin->id;
                    $admin_username = $admin->username;
                    $admin_name = $admin->name ? $admin->name : $admin->username;
                    
                    log_message('debug', 'Login attempt for admin_id: ' . $admin_id . ' (username: ' . $admin_username . ')');
                    
                    // Clear any existing admin session data first
                    $this->session->unset_userdata(array('admin_id', 'admin_username', 'admin_name', 'admin_logged_in'));
                    
                    // Regenerate session ID to prevent session fixation and ensure clean session
                    // sess_regenerate with TRUE parameter destroys old session data
                    $this->session->sess_regenerate(TRUE);
                    
                    // Set new session data with the logged-in admin's information
                    $session_data = array(
                        'admin_id' => $admin_id,
                        'admin_username' => $admin_username,
                        'admin_name' => $admin_name,
                        'admin_logged_in' => TRUE
                    );
                    
                    // Set session data
                    $this->session->set_userdata($session_data);
                    
                    // Force session write by accessing it
                    $test_id = $this->session->userdata('admin_id');
                    
                    // Verify session was set correctly
                    if ($test_id != $admin_id) {
                        log_message('error', 'CRITICAL: Session mismatch after login! Expected admin_id ' . $admin_id . ' but got ' . var_export($test_id, true));
                        $this->session->set_flashdata('error', 'Session error occurred. Please try logging in again.');
                        redirect('login');
                        return;
                    }
                    
                    log_message('debug', 'Login successful - Session verified for admin_id: ' . $admin_id . ' (username: ' . $admin_username . ', name: ' . $admin_name . ')');
                    
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Invalid username or password');
                }
            }
        }
        
        $data['title'] = 'Admin Login';
        $this->load->view('admin/auth/login', $data);
    }
    
    public function logout() {
        // Get cookie settings from config
        $cookie_name = $this->config->item('sess_cookie_name') ?: 'bodare_admin_session';
        $cookie_path = $this->config->item('cookie_path') ?: '/';
        $cookie_domain = $this->config->item('cookie_domain') ?: '';
        // Get cookie_secure from config (auto-detects HTTPS)
        $cookie_secure = $this->config->item('cookie_secure');
        if ($cookie_secure === null || $cookie_secure === false) {
            // Auto-detect HTTPS if not set
            $cookie_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
                            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        }
        $cookie_httponly = $this->config->item('cookie_httponly') !== false ? true : false;
        
        // Unset all session data first
        $this->session->unset_userdata(array(
            'admin_id',
            'admin_username',
            'admin_name',
            'admin_logged_in'
        ));
        
        // Delete the session cookie explicitly by setting it to expire in the past
        // Try multiple variations to ensure it's deleted in all scenarios
        if ($cookie_domain) {
            setcookie($cookie_name, '', time() - 3600, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
        } else {
            // Try without domain
            setcookie($cookie_name, '', time() - 3600, $cookie_path, '', $cookie_secure, $cookie_httponly);
            // Also try with empty domain string explicitly
            setcookie($cookie_name, '', time() - 3600, $cookie_path, false, $cookie_secure, $cookie_httponly);
        }
        
        // Also unset from $_COOKIE superglobal
        if (isset($_COOKIE[$cookie_name])) {
            unset($_COOKIE[$cookie_name]);
        }
        
        // Destroy the session (this also clears all session data)
        $this->session->sess_destroy();
        
        redirect('login');
    }
}

