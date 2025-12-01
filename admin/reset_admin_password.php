<?php
/**
 * Admin Password Reset & Setup Script
 * Access: http://localhost/bodarepensionhouse/admin/reset_admin_password.php
 * 
 * This script will:
 * 1. Check database connection
 * 2. Create or update the admin user
 * 3. Set the password to admin123
 * 
 * IMPORTANT: Delete this file after use for security!
 */

// Load database config without CodeIgniter security check
$db_config_path = __DIR__ . '/application/config/database.php';
if (!file_exists($db_config_path)) {
    die("Error: Database config not found at: $db_config_path");
}

// Read database config file and extract values (without loading CodeIgniter)
$config_content = file_get_contents($db_config_path);
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bodarepensionhouse';

// Extract database configuration values using regex (handles tabs, spaces, empty strings)
if (preg_match("/['\"]hostname['\"]\s*=>\s*['\"](.*?)['\"]/", $config_content, $matches)) {
    $hostname = $matches[1];
}
if (preg_match("/['\"]username['\"]\s*=>\s*['\"](.*?)['\"]/", $config_content, $matches)) {
    $username = $matches[1];
}
// Password might be empty, so handle that case
if (preg_match("/['\"]password['\"]\s*=>\s*['\"](.*?)['\"]/", $config_content, $matches)) {
    $password = $matches[1];
} else if (preg_match("/['\"]password['\"]\s*=>\s*['\"]['\"]/", $config_content)) {
    $password = ''; // Empty password
}
if (preg_match("/['\"]database['\"]\s*=>\s*['\"](.*?)['\"]/", $config_content, $matches)) {
    $database = $matches[1];
}

// Connect to database
$conn = mysqli_connect($hostname, $username, $password);

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// Check if database exists
$db_selected = mysqli_select_db($conn, $database);
if (!$db_selected) {
    echo "<h2>⚠️ Database '$database' does not exist!</h2>";
    echo "<p>Please create the database first:</p>";
    echo "<pre>CREATE DATABASE $database;</pre>";
    echo "<p>Or import the database schema file.</p>";
    mysqli_close($conn);
    exit;
}

$message = '';
$success = false;
$errors = array();
$info = array();

// Process reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset'])) {
    $new_password = $_POST['password'] ?? 'admin123';
    
    if (empty($new_password)) {
        $errors[] = "Password cannot be empty!";
    } else {
        // Check if admins table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
        if (mysqli_num_rows($table_check) == 0) {
            // Create admins table
            $create_table_sql = "CREATE TABLE IF NOT EXISTS `admins` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL,
              `password` varchar(255) NOT NULL,
              `name` varchar(100) NOT NULL,
              `email` varchar(100) NOT NULL,
              `status` enum('active','inactive') DEFAULT 'active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (mysqli_query($conn, $create_table_sql)) {
                $info[] = "✓ Admins table created successfully";
            } else {
                $errors[] = "Failed to create admins table: " . mysqli_error($conn);
            }
        }
        
        // Generate password hash
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Check if admin user exists
        $check_sql = "SELECT id FROM `admins` WHERE `username` = 'admin'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing admin
            $update_sql = "UPDATE `admins` SET 
                           `password` = '" . mysqli_real_escape_string($conn, $hashed) . "',
                           `name` = 'Administrator',
                           `email` = 'admin@bodarepensionhouse.com',
                           `status` = 'active'
                           WHERE `username` = 'admin'";
            
            if (mysqli_query($conn, $update_sql)) {
                $success = true;
                $message = "✅ Admin password updated successfully!<br>";
                $message .= "Username: <strong>admin</strong><br>";
                $message .= "Password: <strong>$new_password</strong>";
            } else {
                $errors[] = "Failed to update admin: " . mysqli_error($conn);
            }
        } else {
            // Create new admin user
            $insert_sql = "INSERT INTO `admins` (`username`, `password`, `name`, `email`, `status`) VALUES (
                           'admin',
                           '" . mysqli_real_escape_string($conn, $hashed) . "',
                           'Administrator',
                           'admin@bodarepensionhouse.com',
                           'active'
            )";
            
            if (mysqli_query($conn, $insert_sql)) {
                $success = true;
                $message = "✅ Admin user created successfully!<br>";
                $message .= "Username: <strong>admin</strong><br>";
                $message .= "Password: <strong>$new_password</strong>";
            } else {
                $errors[] = "Failed to create admin user: " . mysqli_error($conn);
            }
        }
    }
}

// Get diagnostic information
$diagnostics = array();

// Check database connection
$diagnostics[] = "✓ Database connection: OK";

// Check if database exists
$db_check = mysqli_query($conn, "SELECT DATABASE()");
if ($db_check) {
    $db_name = mysqli_fetch_array($db_check)[0];
    $diagnostics[] = "✓ Database: $db_name";
}

// Check if admins table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
if (mysqli_num_rows($table_check) > 0) {
    $diagnostics[] = "✓ Admins table: EXISTS";
    
    // Check if admin user exists
    $user_check = mysqli_query($conn, "SELECT id, username, name, email, status FROM `admins` WHERE `username` = 'admin'");
    if (mysqli_num_rows($user_check) > 0) {
        $admin_data = mysqli_fetch_assoc($user_check);
        $diagnostics[] = "✓ Admin user: EXISTS (Status: " . $admin_data['status'] . ")";
    } else {
        $diagnostics[] = "⚠ Admin user: DOES NOT EXIST";
    }
} else {
    $diagnostics[] = "⚠ Admins table: DOES NOT EXIST";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .diagnostics {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .diagnostics h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 16px;
        }
        .diagnostics ul {
            list-style: none;
            padding: 0;
        }
        .diagnostics li {
            padding: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:active {
            transform: translateY(0);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Password Reset</h1>
        <p class="subtitle">Reset or create the admin user account</p>
        
        <?php if (!empty($diagnostics)): ?>
        <div class="diagnostics">
            <h3>📊 System Diagnostics</h3>
            <ul>
                <?php foreach ($diagnostics as $diag): ?>
                <li><?php echo $diag; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>❌ Errors:</strong><br>
            <?php foreach ($errors as $error): ?>
            • <?php echo htmlspecialchars($error); ?><br>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($info)): ?>
        <div class="info">
            <?php foreach ($info as $inf): ?>
            <?php echo $inf; ?><br>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            This script should be deleted after use to prevent unauthorized access.
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">New Admin Password:</label>
                <input type="password" id="password" name="password" value="admin123" required>
            </div>
            <button type="submit" name="reset" class="btn">Reset Admin Password</button>
        </form>
        
        <div class="login-link">
            <a href="login">← Back to Login</a>
        </div>
    </div>
</body>
</html>

