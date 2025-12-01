<?php
/**
 * Command Line Password Rehashing Script
 * 
 * Usage: php rehash_passwords_cli.php [options]
 * 
 * Options:
 *   --check          Only check, don't modify
 *   --mark-reset     Mark users for password reset
 *   --hash           Hash plain text passwords (use with caution!)
 *   --dry-run        Show what would be done without making changes
 * 
 * Example:
 *   php rehash_passwords_cli.php --check
 *   php rehash_passwords_cli.php --mark-reset --dry-run
 */

// Only allow CLI execution
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line.\n");
}

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
    die("Connection failed: " . $conn->connect_error . "\n");
}

$conn->set_charset("utf8mb4");

// Parse command line arguments
$options = getopt("", ["check", "mark-reset", "hash", "dry-run"]);
$dry_run = isset($options['dry-run']);

echo "=== Password Rehashing Utility ===\n\n";

// Get all users
$result = $conn->query("SELECT id, email, password, status FROM users");

if (!$result) {
    die("Error querying users: " . $conn->error . "\n");
}

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

echo "Total users: " . (count($users_ok) + count($users_to_fix)) . "\n";
echo "Properly hashed: " . count($users_ok) . "\n";
echo "Needs fixing: " . count($users_to_fix) . "\n\n";

if (count($users_to_fix) > 0) {
    echo "Users with unhashed passwords:\n";
    foreach ($users_to_fix as $user) {
        echo "  - ID: {$user['id']}, Email: {$user['email']}, Status: {$user['status']}\n";
    }
    echo "\n";
}

// Process based on options
if (isset($options['check'])) {
    echo "✓ Check complete. No changes made.\n";
} elseif (isset($options['mark-reset'])) {
    if ($dry_run) {
        echo "[DRY RUN] Would mark " . count($users_to_fix) . " users for password reset.\n";
    } else {
        $updated = 0;
        foreach ($users_to_fix as $user) {
            $reset_hash = password_hash(uniqid('reset_', true) . time(), PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $reset_hash, $user['id']);
            if ($stmt->execute()) {
                $updated++;
                echo "✓ Marked user {$user['id']} ({$user['email']}) for password reset\n";
            } else {
                echo "✗ Failed to update user {$user['id']}: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
        echo "\n✓ Marked $updated users for password reset.\n";
    }
} elseif (isset($options['hash'])) {
    if ($dry_run) {
        echo "[DRY RUN] Would hash " . count($users_to_fix) . " plain text passwords.\n";
    } else {
        $updated = 0;
        $failed = 0;
        
        foreach ($users_to_fix as $user) {
            $plain_password = $user['password'];
            
            if (strlen($plain_password) < 60 && strpos($plain_password, '$') !== 0) {
                $hashed = password_hash($plain_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed, $user['id']);
                if ($stmt->execute()) {
                    $updated++;
                    echo "✓ Hashed password for user {$user['id']} ({$user['email']})\n";
                } else {
                    $failed++;
                    echo "✗ Failed to update user {$user['id']}: " . $stmt->error . "\n";
                }
                $stmt->close();
            } else {
                $failed++;
                echo "⚠ Skipped user {$user['id']} - password doesn't look like plain text\n";
            }
        }
        
        echo "\n✓ Hashed $updated passwords. " . ($failed > 0 ? "$failed failed." : "") . "\n";
    }
} else {
    echo "No action specified. Use --check, --mark-reset, or --hash\n";
    echo "Add --dry-run to see what would be done without making changes.\n";
}

$conn->close();
echo "\nDone.\n";

