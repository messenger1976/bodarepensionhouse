<?php
/**
 * Fix User Passwords Utility
 * This script helps diagnose and fix password hashing issues
 * 
 * WARNING: This is a diagnostic tool. Delete after use!
 */

// Load CodeIgniter
define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');

// Simple database connection
require_once(__DIR__ . '/application/config/database.php');

$db_config = $db['default'];
$conn = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>User Password Diagnostic Tool</h2>";

// Check users
$result = $conn->query("SELECT id, email, password, status, 
    LENGTH(password) as pwd_len,
    CASE 
        WHEN password LIKE '\$2y\$%' THEN 'BCrypt (2y)'
        WHEN password LIKE '\$2a\$%' THEN 'BCrypt (2a)'
        WHEN password LIKE '\$2b\$%' THEN 'BCrypt (2b)'
        WHEN LENGTH(password) < 50 THEN 'Too Short (Not Hashed)'
        ELSE 'Unknown Format'
    END as hash_type
    FROM users 
    ORDER BY id DESC 
    LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<h3>User Password Status:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Email</th><th>Status</th><th>Password Length</th><th>Hash Type</th><th>Action</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $hash_type = $row['hash_type'];
        $is_hashed = strpos($hash_type, 'BCrypt') !== false;
        $status_color = $is_hashed ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pwd_len']) . "</td>";
        echo "<td style='color: $status_color;'>" . htmlspecialchars($hash_type) . "</td>";
        echo "<td>";
        if (!$is_hashed) {
            echo "<span style='color: red;'>⚠️ Password not hashed!</span>";
        } else {
            echo "<span style='color: green;'>✓ OK</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

echo "<hr>";
echo "<h3>Test Password Verification</h3>";
echo "<form method='POST' style='margin: 20px 0;'>";
echo "<p><label>Email: <input type='email' name='test_email' required></label></p>";
echo "<p><label>Password: <input type='password' name='test_password' required></label></p>";
echo "<p><button type='submit' name='test'>Test Login</button></p>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test'])) {
    $test_email = trim(strtolower($_POST['test_email']));
    $test_password = $_POST['test_password'];
    
    $stmt = $conn->prepare("SELECT id, email, password, status FROM users WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $test_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        echo "<h4>Test Results:</h4>";
        echo "<p><strong>User Found:</strong> " . htmlspecialchars($user['email']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($user['status']) . "</p>";
        echo "<p><strong>Password Hash:</strong> " . htmlspecialchars(substr($user['password'], 0, 30)) . "...</p>";
        echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . " characters</p>";
        
        $verify = password_verify($test_password, $user['password']);
        echo "<p><strong>Password Verification:</strong> " . 
             ($verify ? '<span style="color: green;">✓ SUCCESS</span>' : '<span style="color: red;">✗ FAILED</span>') . 
             "</p>";
        
        if (!$verify) {
            echo "<p style='color: red;'><strong>Issue:</strong> Password verification failed. This could mean:</p>";
            echo "<ul>";
            echo "<li>The password in the database is not properly hashed</li>";
            echo "<li>The password was changed after registration</li>";
            echo "<li>There's a mismatch between stored and entered password</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>User not found with email: " . htmlspecialchars($test_email) . "</p>";
    }
    
    $stmt->close();
}

$conn->close();

echo "<hr>";
echo "<p><strong>Note:</strong> Delete this file after testing for security!</p>";

