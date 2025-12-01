<?php
/**
 * Rehash User Passwords Utility
 * This script identifies and fixes password hashing issues
 * 
 * WARNING: This script should only be run if you have access to plain text passwords
 * or if you want to mark users for password reset.
 * 
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

// Load database config
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

// Set charset
$conn->set_charset("utf8mb4");

$message = '';
$success = false;
$errors = [];

// Check if we should process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['check_passwords'])) {
        // Just check, don't modify
        $action = 'check';
    } elseif (isset($_POST['mark_for_reset'])) {
        // Mark users with unhashed passwords for password reset
        $action = 'mark_reset';
    } elseif (isset($_POST['hash_plaintext'])) {
        // Hash plain text passwords (ONLY if you have the plain text stored)
        $action = 'hash';
    } else {
        $action = 'check';
    }
    
    // Get all users
    $result = $conn->query("SELECT id, email, password, status FROM users");
    
    if ($result) {
        $users_to_fix = [];
        $users_ok = [];
        
        while ($row = $result->fetch_assoc()) {
            $password = $row['password'];
            $is_hashed = (strlen($password) >= 60 && 
                         (strpos($password, '$2y$') === 0 || 
                          strpos($password, '$2a$') === 0 || 
                          strpos($password, '$2b$') === 0));
            
            if (!$is_hashed) {
                $users_to_fix[] = $row;
            } else {
                $users_ok[] = $row;
            }
        }
        
        if ($action == 'check') {
            $message = "Found " . count($users_to_fix) . " users with unhashed passwords and " . count($users_ok) . " users with properly hashed passwords.";
        } elseif ($action == 'mark_reset') {
            // Mark users for password reset by setting a flag or changing status
            $updated = 0;
            foreach ($users_to_fix as $user) {
                // Option 1: Set a flag in a new column (if it exists)
                // Option 2: Change password to a random hash that can't be verified
                // Option 3: Set status to inactive temporarily
            
                // We'll set password to a random hash that forces reset
                $reset_hash = password_hash(uniqid('reset_', true) . time(), PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'active' WHERE id = ?");
                $stmt->bind_param("si", $reset_hash, $user['id']);
                if ($stmt->execute()) {
                    $updated++;
                }
                $stmt->close();
            }
            $success = true;
            $message = "Marked " . $updated . " users for password reset. They will need to use 'Forgot Password' to reset.";
        } elseif ($action == 'hash') {
            // WARNING: This only works if passwords are stored in plain text
            // and you want to hash them. This is ONLY safe during initial migration.
            $updated = 0;
            $failed = 0;
            
            foreach ($users_to_fix as $user) {
                $plain_password = $user['password'];
                
                // Check if it looks like a plain text password (reasonable length, no $ prefix)
                if (strlen($plain_password) < 60 && strpos($plain_password, '$') !== 0) {
                    // Hash the plain text password
                    $hashed = password_hash($plain_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashed, $user['id']);
                    if ($stmt->execute()) {
                        $updated++;
                    } else {
                        $failed++;
                        $errors[] = "Failed to update user ID " . $user['id'] . ": " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $failed++;
                    $errors[] = "User ID " . $user['id'] . " password doesn't look like plain text. Skipping.";
                }
            }
            
            $success = true;
            $message = "Hashed " . $updated . " plain text passwords. " . ($failed > 0 ? $failed . " failed." : "");
        }
    } else {
        $errors[] = "Failed to query users: " . $conn->error;
    }
}

// Get current status
$result = $conn->query("SELECT id, email, password, status, 
    LENGTH(password) as pwd_len,
    CASE 
        WHEN password LIKE '\$2y\$%' THEN 'BCrypt (2y)'
        WHEN password LIKE '\$2a\$%' THEN 'BCrypt (2a)'
        WHEN password LIKE '\$2b\$%' THEN 'BCrypt (2b)'
        WHEN LENGTH(password) < 50 THEN 'Plain Text (Unhashed)'
        ELSE 'Unknown Format'
    END as hash_type
    FROM users 
    ORDER BY id DESC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Rehash User Passwords</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; border-radius: 4px; margin: 20px 0; color: #721c24; }
        .success { background: #d4edda; border: 1px solid #28a745; padding: 15px; border-radius: 4px; margin: 20px 0; color: #155724; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .ok { color: green; }
        .warning-text { color: orange; }
        .error-text { color: red; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; border: none; border-radius: 4px; }
        .btn-check { background: #007bff; color: white; }
        .btn-reset { background: #ffc107; color: black; }
        .btn-hash { background: #28a745; color: white; }
        button:hover { opacity: 0.9; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { flex: 1; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .stat-box h3 { margin: 0 0 10px 0; }
        .stat-number { font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Rehash User Passwords Utility</h1>
        
        <div class="warning">
            <strong>⚠️ WARNING:</strong> This tool should only be used during development or migration. 
            Delete this file after use for security reasons!
        </div>
        
        <?php if ($message): ?>
            <div class="<?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php
        if ($result && $result->num_rows > 0):
            $hashed_count = 0;
            $unhashed_count = 0;
            $users_data = [];
            
            while ($row = $result->fetch_assoc()) {
                $users_data[] = $row;
                if (strpos($row['hash_type'], 'BCrypt') !== false) {
                    $hashed_count++;
                } else {
                    $unhashed_count++;
                }
            }
        ?>
        
        <div class="stats">
            <div class="stat-box">
                <h3>Total Users</h3>
                <div class="stat-number"><?php echo count($users_data); ?></div>
            </div>
            <div class="stat-box" style="background: #d4edda;">
                <h3>Properly Hashed</h3>
                <div class="stat-number" style="color: green;"><?php echo $hashed_count; ?></div>
            </div>
            <div class="stat-box" style="background: <?php echo $unhashed_count > 0 ? '#fff3cd' : '#d4edda'; ?>;">
                <h3>Needs Fixing</h3>
                <div class="stat-number" style="color: <?php echo $unhashed_count > 0 ? 'orange' : 'green'; ?>;"><?php echo $unhashed_count; ?></div>
            </div>
        </div>
        
        <h2>User Password Status</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Password Length</th>
                    <th>Hash Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_data as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['status']); ?></td>
                        <td><?php echo htmlspecialchars($user['pwd_len']); ?></td>
                        <td class="<?php 
                            echo strpos($user['hash_type'], 'BCrypt') !== false ? 'ok' : 
                                (strpos($user['hash_type'], 'Plain Text') !== false ? 'error-text' : 'warning-text'); 
                        ?>">
                            <?php echo htmlspecialchars($user['hash_type']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($unhashed_count > 0): ?>
            <h2>Actions</h2>
            <div class="warning">
                <strong>Found <?php echo $unhashed_count; ?> user(s) with unhashed passwords.</strong>
                <p>Choose an action:</p>
            </div>
            
            <form method="POST" style="margin: 20px 0;">
                <button type="submit" name="check_passwords" class="btn-check">🔍 Re-check Passwords</button>
            </form>
            
            <form method="POST" style="margin: 20px 0;" onsubmit="return confirm('This will mark users for password reset. They will need to use Forgot Password. Continue?');">
                <button type="submit" name="mark_for_reset" class="btn-reset">🔑 Mark Users for Password Reset</button>
                <p style="font-size: 12px; color: #666;">This sets a random hash that forces users to reset their password via "Forgot Password".</p>
            </form>
            
            <form method="POST" style="margin: 20px 0;" onsubmit="return confirm('⚠️ WARNING: This will hash plain text passwords. Only use if passwords are stored in plain text and you want to hash them. This is typically only safe during initial migration. Continue?');">
                <button type="submit" name="hash_plaintext" class="btn-hash">🔐 Hash Plain Text Passwords</button>
                <p style="font-size: 12px; color: #666;">⚠️ Only use this if passwords are stored in plain text and you want to hash them. This is typically only safe during initial migration.</p>
            </form>
        <?php else: ?>
            <div class="success">
                <strong>✓ All passwords are properly hashed!</strong>
            </div>
        <?php endif; ?>
        
        <?php else: ?>
            <p>No users found in database.</p>
        <?php endif; ?>
        
        <hr style="margin: 40px 0;">
        <p style="color: #666; font-size: 12px;">
            <strong>Note:</strong> After fixing passwords, delete this file for security!
        </p>
    </div>
</body>
</html>

<?php
$conn->close();
?>

