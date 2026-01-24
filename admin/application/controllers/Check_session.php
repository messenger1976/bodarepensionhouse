<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Check_session extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
    }
    
    public function index() {
        echo "<h2>Production Session Configuration Check</h2>";
        echo "<pre>";
        
        // Check session configuration
        echo "=== SESSION CONFIGURATION ===\n";
        echo "Session Driver: " . $this->config->item('sess_driver') . "\n";
        echo "Session Cookie Name: " . $this->config->item('sess_cookie_name') . "\n";
        echo "Session Save Path: " . ($this->config->item('sess_save_path') ?: 'PHP Default') . "\n";
        echo "Session Expiration: " . $this->config->item('sess_expiration') . " seconds\n\n";
        
        // Check cookie configuration
        echo "=== COOKIE CONFIGURATION ===\n";
        echo "Cookie Domain: " . ($this->config->item('cookie_domain') ?: 'Empty (any domain)') . "\n";
        echo "Cookie Path: " . $this->config->item('cookie_path') . "\n";
        echo "Cookie Secure: " . ($this->config->item('cookie_secure') ? 'TRUE (HTTPS required)' : 'FALSE (HTTP allowed)') . "\n";
        echo "Cookie HttpOnly: " . ($this->config->item('cookie_httponly') ? 'TRUE' : 'FALSE') . "\n";
        echo "Cookie SameSite: " . $this->config->item('cookie_samesite') . "\n\n";
        
        // Check server environment
        echo "=== SERVER ENVIRONMENT ===\n";
        echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'YES' : 'NO') . "\n";
        echo "SERVER_PORT: " . (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'Not set') . "\n";
        echo "HTTP_X_FORWARDED_PROTO: " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'Not set') . "\n";
        echo "HTTP_HOST: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'Not set') . "\n";
        echo "REQUEST_URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Not set') . "\n\n";
        
        // Check session directory
        echo "=== SESSION DIRECTORY ===\n";
        $sessions_path = APPPATH . 'cache/sessions';
        echo "Sessions Path: " . $sessions_path . "\n";
        echo "Directory Exists: " . (is_dir($sessions_path) ? 'YES' : 'NO') . "\n";
        if (is_dir($sessions_path)) {
            echo "Directory Writable: " . (is_writable($sessions_path) ? 'YES' : 'NO') . "\n";
            echo "Directory Permissions: " . substr(sprintf('%o', fileperms($sessions_path)), -4) . "\n";
        } else {
            echo "⚠️ WARNING: Session directory does not exist!\n";
            echo "Attempting to create...\n";
            if (@mkdir($sessions_path, 0755, true)) {
                echo "✓ Directory created successfully!\n";
            } else {
                echo "✗ Failed to create directory. Check permissions.\n";
            }
        }
        echo "\n";
        
        // Check current session
        echo "=== CURRENT SESSION ===\n";
        echo "Session ID: " . session_id() . "\n";
        $cookie_name = $this->config->item('sess_cookie_name') ?: 'bodare_admin_session';
        echo "Session Cookie Name: " . $cookie_name . "\n";
        echo "Session Cookie Value: " . (isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : 'NOT SET') . "\n";
        echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n\n";
        
        // Check session data
        echo "=== SESSION DATA ===\n";
        $admin_id = $this->session->userdata('admin_id');
        $admin_name = $this->session->userdata('admin_name');
        $admin_username = $this->session->userdata('admin_username');
        $admin_logged_in = $this->session->userdata('admin_logged_in');
        echo "admin_id: " . ($admin_id ?: 'NOT SET') . "\n";
        echo "admin_username: " . ($admin_username ?: 'NOT SET') . "\n";
        echo "admin_name: " . ($admin_name ?: 'NOT SET') . "\n";
        echo "admin_logged_in: " . ($admin_logged_in ? 'TRUE' : 'FALSE') . "\n\n";
        
        // Test session write
        echo "=== SESSION WRITE TEST ===\n";
        $test_key = 'test_' . time();
        $this->session->set_userdata($test_key, 'test_value');
        $test_value = $this->session->userdata($test_key);
        if ($test_value === 'test_value') {
            echo "✓ Session write/read: SUCCESS\n";
            $this->session->unset_userdata($test_key);
        } else {
            echo "✗ Session write/read: FAILED\n";
            echo "This indicates sessions are not working properly!\n";
        }
        
        // Check database connection
        echo "\n=== DATABASE CHECK ===\n";
        if ($admin_id) {
            $this->load->model('Admin_model');
            $admin = $this->Admin_model->get_admin($admin_id);
            if ($admin) {
                echo "Admin found in database:\n";
                echo "ID: " . $admin->id . "\n";
                echo "Username: " . $admin->username . "\n";
                echo "Name: " . ($admin->name ?: 'N/A') . "\n";
            } else {
                echo "⚠️ Admin ID {$admin_id} not found in database\n";
            }
        } else {
            echo "No admin_id in session\n";
        }
        
        echo "\n=== RECOMMENDATIONS ===\n";
        if (!is_dir($sessions_path) || !is_writable($sessions_path)) {
            echo "1. Create and set permissions on: " . $sessions_path . "\n";
            echo "   Command: mkdir -p " . $sessions_path . " && chmod 755 " . $sessions_path . "\n";
        }
        $cookie_secure = $this->config->item('cookie_secure');
        $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                   (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
                   (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        if (!$cookie_secure && $is_https) {
            echo "2. ⚠️ CRITICAL: cookie_secure is FALSE but HTTPS is detected!\n";
            echo "   Cookies will NOT be set properly over HTTPS.\n";
            echo "   This is likely the main issue!\n";
        }
        if (empty($admin_id)) {
            echo "3. No admin session found. Try logging in and check again.\n";
        }
        
        echo "</pre>";
        echo "<p><a href='" . base_url('login') . "'>Go to Login</a> | <a href='" . base_url('dashboard') . "'>Go to Dashboard</a></p>";
    }
}
