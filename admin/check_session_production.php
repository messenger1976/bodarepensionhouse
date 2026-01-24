<?php
/**
 * Production Session Check Script
 * Access: https://pensionhouse.bodarempc.com/admin/check_session_production.php
 * DELETE AFTER TESTING
 */

// Bootstrap CodeIgniter
require_once(__DIR__ . '/index.php');

$CI =& get_instance();
$CI->load->library('session');
$CI->load->database();

echo "<h2>Production Session Configuration Check</h2>";
echo "<pre>";

// Check session configuration
echo "=== SESSION CONFIGURATION ===\n";
echo "Session Driver: " . $CI->config->item('sess_driver') . "\n";
echo "Session Cookie Name: " . $CI->config->item('sess_cookie_name') . "\n";
echo "Session Save Path: " . ($CI->config->item('sess_save_path') ?: 'PHP Default') . "\n";
echo "Session Expiration: " . $CI->config->item('sess_expiration') . " seconds\n\n";

// Check cookie configuration
echo "=== COOKIE CONFIGURATION ===\n";
echo "Cookie Domain: " . ($CI->config->item('cookie_domain') ?: 'Empty (any domain)') . "\n";
echo "Cookie Path: " . $CI->config->item('cookie_path') . "\n";
echo "Cookie Secure: " . ($CI->config->item('cookie_secure') ? 'TRUE (HTTPS required)' : 'FALSE (HTTP allowed)') . "\n";
echo "Cookie HttpOnly: " . ($CI->config->item('cookie_httponly') ? 'TRUE' : 'FALSE') . "\n";
echo "Cookie SameSite: " . $CI->config->item('cookie_samesite') . "\n\n";

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
echo "Session Cookie: " . (isset($_COOKIE[$CI->config->item('sess_cookie_name')]) ? $_COOKIE[$CI->config->item('sess_cookie_name')] : 'NOT SET') . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n\n";

// Check session data
echo "=== SESSION DATA ===\n";
$admin_id = $CI->session->userdata('admin_id');
$admin_name = $CI->session->userdata('admin_name');
$admin_logged_in = $CI->session->userdata('admin_logged_in');
echo "admin_id: " . ($admin_id ?: 'NOT SET') . "\n";
echo "admin_name: " . ($admin_name ?: 'NOT SET') . "\n";
echo "admin_logged_in: " . ($admin_logged_in ? 'TRUE' : 'FALSE') . "\n\n";

// Test session write
echo "=== SESSION WRITE TEST ===\n";
$test_key = 'test_' . time();
$CI->session->set_userdata($test_key, 'test_value');
$test_value = $CI->session->userdata($test_key);
if ($test_value === 'test_value') {
    echo "✓ Session write/read: SUCCESS\n";
    $CI->session->unset_userdata($test_key);
} else {
    echo "✗ Session write/read: FAILED\n";
    echo "This indicates sessions are not working properly!\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if (!is_dir($sessions_path) || !is_writable($sessions_path)) {
    echo "1. Create and set permissions on: " . $sessions_path . "\n";
    echo "   Command: mkdir -p " . $sessions_path . " && chmod 755 " . $sessions_path . "\n";
}
if (!$CI->config->item('cookie_secure') && (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    echo "2. ⚠️ WARNING: cookie_secure is FALSE but HTTPS is detected!\n";
    echo "   Cookies may not be set properly over HTTPS.\n";
}
if (empty($CI->session->userdata('admin_id'))) {
    echo "3. No admin session found. Try logging in and check again.\n";
}

echo "</pre>";
echo "<p><a href='" . base_url('login') . "'>Go to Login</a></p>";
?>
