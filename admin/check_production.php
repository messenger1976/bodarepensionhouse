<?php
/**
 * Production Environment Diagnostic Script
 * 
 * Access this file via browser to check your production server configuration
 * Example: https://pensionhouse.bodarempc.com/admin/check_production.php
 * 
 * IMPORTANT: Delete this file after checking, as it exposes system information
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Production Check</title>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;}</style>";
echo "</head><body>";
echo "<h1>Production Environment Diagnostic</h1>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check PHP version
$php_version = phpversion();
echo "<h2>1. PHP Configuration</h2>";
echo "<p>PHP Version: <strong>$php_version</strong> ";
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<span class='ok'>✓ OK</span></p>";
} else {
    echo "<span class='error'>✗ Requires PHP 7.4+</span></p>";
}

// Check if CodeIgniter files exist
echo "<h2>2. CodeIgniter Files</h2>";
$required_files = [
    'index.php',
    'application/config/config.php',
    'application/config/database.php',
    'system/core/CodeIgniter.php'
];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p class='ok'>✓ $file exists</p>";
    } else {
        echo "<p class='error'>✗ $file NOT FOUND</p>";
    }
}

// Check writable directories
echo "<h2>3. File Permissions</h2>";
$writable_dirs = [
    'application/cache',
    'application/logs'
];
foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='ok'>✓ $dir is writable</p>";
        } else {
            echo "<p class='error'>✗ $dir is NOT writable (chmod 755 needed)</p>";
        }
    } else {
        echo "<p class='error'>✗ $dir directory does not exist</p>";
    }
}

// Check database connection
echo "<h2>4. Database Connection</h2>";
if (file_exists('application/config/database.php')) {
    require_once 'application/config/database.php';
    
    if (isset($db) && isset($db['default'])) {
        $db_config = $db['default'];
        $conn = @new mysqli(
            $db_config['hostname'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );
        
        if ($conn->connect_error) {
            echo "<p class='error'>✗ Database Connection Failed: " . $conn->connect_error . "</p>";
        } else {
            echo "<p class='ok'>✓ Database Connection: OK</p>";
            
            // Check admin users
            echo "<h3>Admin Users in Database:</h3>";
            $result = $conn->query("SELECT id, username, name, email, status FROM admins ORDER BY id");
            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Status</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    $name_display = !empty($row['name']) ? $row['name'] : '<em style="color:orange;">empty</em>';
                    $status_class = $row['status'] == 'active' ? 'ok' : 'error';
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>$name_display</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td class='$status_class'>" . $row['status'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ No admin users found in database</p>";
            }
            $conn->close();
        }
    } else {
        echo "<p class='error'>✗ Database configuration not found</p>";
    }
} else {
    echo "<p class='error'>✗ database.php config file not found</p>";
}

// Check session configuration
echo "<h2>5. Session Configuration</h2>";
session_start();
$session_id = session_id();
if ($session_id) {
    echo "<p class='ok'>✓ Session ID: $session_id</p>";
    
    // Test session write
    $_SESSION['test_production_check'] = 'test_value_' . time();
    if (isset($_SESSION['test_production_check'])) {
        echo "<p class='ok'>✓ Session write/read: Working</p>";
        echo "<p>Session save path: " . session_save_path() . "</p>";
        echo "<p>Session name: " . session_name() . "</p>";
    } else {
        echo "<p class='error'>✗ Session write/read: NOT Working</p>";
    }
} else {
    echo "<p class='error'>✗ Session not starting</p>";
}

// Check .htaccess
echo "<h2>6. .htaccess Configuration</h2>";
if (file_exists('.htaccess')) {
    echo "<p class='ok'>✓ .htaccess file exists</p>";
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<p class='ok'>✓ mod_rewrite rules found</p>";
    } else {
        echo "<p class='warning'>⚠ mod_rewrite rules not found</p>";
    }
} else {
    echo "<p class='warning'>⚠ .htaccess file not found</p>";
}

// Check API directory
echo "<h2>7. API Controllers</h2>";
$api_controllers = [
    'application/controllers/api/Auth.php',
    'application/controllers/api/Booking.php',
    'application/controllers/api/User.php',
    'application/controllers/api/Inquiry.php'
];
foreach ($api_controllers as $controller) {
    if (file_exists($controller)) {
        echo "<p class='ok'>✓ $controller exists</p>";
    } else {
        echo "<p class='error'>✗ $controller NOT FOUND</p>";
    }
}

// Check base URL detection
echo "<h2>8. URL Configuration</h2>";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$detected_base = $protocol . $host . $script_path . '/';
echo "<p>Detected Base URL: <strong>$detected_base</strong></p>";
echo "<p>Current URL: <strong>" . $protocol . $host . $_SERVER['REQUEST_URI'] . "</strong></p>";

// Check environment
echo "<h2>9. Environment</h2>";
if (file_exists('index.php')) {
    $index_content = file_get_contents('index.php');
    if (preg_match("/define\s*\(\s*['\"]ENVIRONMENT['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $index_content, $matches)) {
        $env = $matches[1];
        echo "<p>Environment: <strong>$env</strong> ";
        if ($env == 'production') {
            echo "<span class='ok'>✓ Set to production</span></p>";
        } else {
            echo "<span class='warning'>⚠ Set to $env (should be 'production' for live server)</span></p>";
        }
    }
}

// Security warning
echo "<hr>";
echo "<h2 style='color:red;'>⚠️ SECURITY WARNING</h2>";
echo "<p><strong>Please delete this file (check_production.php) after checking!</strong></p>";
echo "<p>This file exposes sensitive system information and should not be left on a production server.</p>";

echo "</body></html>";
?>

