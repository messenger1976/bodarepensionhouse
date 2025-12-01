<?php
/**
 * Password Test Utility
 * This file helps test if password hashing and verification is working correctly
 * 
 * Usage: Access via browser: http://yourdomain.com/admin/test_password.php
 * 
 * WARNING: Delete this file after testing for security!
 */

// Simple test without CodeIgniter
$test_password = 'test123';
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);

echo "<h2>Password Hashing Test</h2>";
echo "<p><strong>Test Password:</strong> " . htmlspecialchars($test_password) . "</p>";
echo "<p><strong>Generated Hash:</strong> " . htmlspecialchars($test_hash) . "</p>";

$verify_result = password_verify($test_password, $test_hash);
echo "<p><strong>Verification Result:</strong> " . ($verify_result ? '<span style="color: green;">SUCCESS</span>' : '<span style="color: red;">FAILED</span>') . "</p>";

// Test with database connection
echo "<hr><h3>Database Connection Test</h3>";

// Try to connect to database
$config_file = __DIR__ . '/application/config/database.php';
if (file_exists($config_file)) {
    include($config_file);
    
    if (isset($db['default'])) {
        $db_config = $db['default'];
        
        try {
            $conn = new mysqli(
                $db_config['hostname'],
                $db_config['username'],
                $db_config['password'],
                $db_config['database']
            );
            
            if ($conn->connect_error) {
                echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
            } else {
                echo "<p style='color: green;'>✓ Database connection successful!</p>";
                
                // Check users table structure
                $result = $conn->query("DESCRIBE users");
                if ($result) {
                    echo "<h4>Users Table Structure:</h4>";
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                // Check if there are any users
                $result = $conn->query("SELECT id, email, status, 
                    CASE WHEN password LIKE '\$2y\$%' THEN 'Hashed' 
                         WHEN password LIKE '\$2a\$%' THEN 'Hashed (2a)' 
                         ELSE 'Not Hashed' END as password_status,
                    LENGTH(password) as password_length
                    FROM users LIMIT 5");
                
                if ($result && $result->num_rows > 0) {
                    echo "<h4>Sample Users (first 5):</h4>";
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>ID</th><th>Email</th><th>Status</th><th>Password Status</th><th>Password Length</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['password_status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['password_length']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No users found in database.</p>";
                }
                
                $conn->close();
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>Database config not found in expected format.</p>";
    }
} else {
    echo "<p style='color: orange;'>Database config file not found.</p>";
}

echo "<hr><p><strong>Note:</strong> Delete this file after testing for security!</p>";

