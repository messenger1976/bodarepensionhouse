<?php
/**
 * Database Verification Script for Booking Items
 * 
 * This script checks if the booking_items table exists and shows its current state.
 * Run this from your browser: http://yourdomain.com/admin/verify_booking_items.php
 * 
 * IMPORTANT: Delete this file after verification for security reasons.
 */

// Define BASEPATH to bypass CodeIgniter security check
if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/system/');
}

// Load database configuration
require_once(__DIR__ . '/application/config/database.php');

// Get database connection details
$db_config = $db['default'];
$hostname = $db_config['hostname'];
$username = $db_config['username'];
$password = $db_config['password'];
$database = $db_config['database'];
$dbdriver = $db_config['dbdriver'];

// Connect to database
try {
    if ($dbdriver === 'mysqli') {
        $conn = new mysqli($hostname, $username, $password, $database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    } else {
        // For PDO
        $dsn = "mysql:host=$hostname;dbname=$database;charset=utf8";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Booking Items Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .sql-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Booking Items Database Verification</h1>";

// Check if booking_items table exists
$table_exists = false;
if ($dbdriver === 'mysqli') {
    $result = $conn->query("SHOW TABLES LIKE 'booking_items'");
    $table_exists = ($result && $result->num_rows > 0);
} else {
    $stmt = $conn->query("SHOW TABLES LIKE 'booking_items'");
    $table_exists = ($stmt && $stmt->rowCount() > 0);
}

if ($table_exists) {
    echo "<div class='status success'><strong>✓ SUCCESS:</strong> The <code>booking_items</code> table exists in the database.</div>";
    
    // Get table structure
    if ($dbdriver === 'mysqli') {
        $result = $conn->query("DESCRIBE booking_items");
        echo "<h2>Table Structure</h2>";
        echo "<table>";
        echo "<tr><th>Field Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><code>{$row['Field']}</code></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count total booking items
        $result = $conn->query("SELECT COUNT(*) as count FROM booking_items");
        $row = $result->fetch_assoc();
        $total_items = $row['count'];
    } else {
        $stmt = $conn->query("DESCRIBE booking_items");
        echo "<h2>Table Structure</h2>";
        echo "<table>";
        echo "<tr><th>Field Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td><code>{$row['Field']}</code></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count total booking items
        $stmt = $conn->query("SELECT COUNT(*) as count FROM booking_items");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_items = $row['count'];
    }
    echo "<div class='status info'><strong>Total Booking Items:</strong> $total_items</div>";
    
    // Get recent booking items
    if ($total_items > 0) {
        $sql = "SELECT booking_items.*, bookings.booking_number, bookings.guest_name 
                FROM booking_items 
                LEFT JOIN bookings ON bookings.id = booking_items.booking_id 
                ORDER BY booking_items.id DESC 
                LIMIT 20";
        
        if ($dbdriver === 'mysqli') {
            $result = $conn->query($sql);
            $recent_items = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $conn->query($sql);
            $recent_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo "<h2>Recent Booking Items (Last 20)</h2>";
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Booking ID</th>
                <th>Booking #</th>
                <th>Guest</th>
                <th>Room Name</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Nights</th>
                <th>Price/Night</th>
                <th>Subtotal</th>
                <th>Status</th>
              </tr>";
        
        foreach ($recent_items as $item) {
            echo "<tr>";
            echo "<td>{$item['id']}</td>";
            echo "<td>{$item['booking_id']}</td>";
            echo "<td>" . (isset($item['booking_number']) ? $item['booking_number'] : 'N/A') . "</td>";
            echo "<td>" . (isset($item['guest_name']) ? htmlspecialchars($item['guest_name']) : 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($item['room_name']) . "</td>";
            echo "<td>" . date('M d, Y', strtotime($item['check_in'])) . "</td>";
            echo "<td>" . date('M d, Y', strtotime($item['check_out'])) . "</td>";
            echo "<td>{$item['nights']}</td>";
            echo "<td>₱" . number_format($item['price_per_night'], 2) . "</td>";
            echo "<td><strong>₱" . number_format($item['subtotal'], 2) . "</strong></td>";
            echo "<td><span class='code'>{$item['status']}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check bookings without items
        $sql = "SELECT bookings.id, bookings.booking_number, bookings.guest_name, bookings.rooms, bookings.created_at
                FROM bookings
                LEFT JOIN booking_items ON booking_items.booking_id = bookings.id
                WHERE booking_items.id IS NULL
                ORDER BY bookings.id DESC
                LIMIT 10";
        
        if ($dbdriver === 'mysqli') {
            $result = $conn->query($sql);
            $bookings_without_items = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $conn->query($sql);
            $bookings_without_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if (count($bookings_without_items) > 0) {
            echo "<h2>⚠️ Bookings Without Items (Last 10)</h2>";
            echo "<div class='status warning'>These bookings were created before the itemization feature was implemented.</div>";
            echo "<table>";
            echo "<tr><th>Booking ID</th><th>Booking #</th><th>Guest</th><th>Rooms</th><th>Created</th></tr>";
            foreach ($bookings_without_items as $booking) {
                echo "<tr>";
                echo "<td>{$booking['id']}</td>";
                echo "<td>" . (isset($booking['booking_number']) ? $booking['booking_number'] : 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($booking['guest_name']) . "</td>";
                echo "<td>" . (isset($booking['rooms']) ? $booking['rooms'] : 1) . "</td>";
                echo "<td>" . date('M d, Y H:i', strtotime($booking['created_at'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Statistics by booking
        $sql = "SELECT booking_id, COUNT(*) as item_count 
                FROM booking_items 
                GROUP BY booking_id 
                ORDER BY item_count DESC 
                LIMIT 10";
        
        if ($dbdriver === 'mysqli') {
            $result = $conn->query($sql);
            $stats = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $conn->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if (count($stats) > 0) {
            echo "<h2>Booking Item Statistics (Top 10)</h2>";
            echo "<table>";
            echo "<tr><th>Booking ID</th><th>Number of Items</th></tr>";
            foreach ($stats as $stat) {
                echo "<tr><td>{$stat['booking_id']}</td><td><strong>{$stat['item_count']}</strong></td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='status warning'><strong>⚠️ WARNING:</strong> The table exists but contains no booking items yet. Create a new booking to test the itemization feature.</div>";
    }
    
} else {
    echo "<div class='status error'><strong>✗ ERROR:</strong> The <code>booking_items</code> table does NOT exist in the database.</div>";
    echo "<h2>How to Fix</h2>";
    echo "<div class='status info'>";
    echo "<p><strong>Step 1:</strong> Run the SQL migration script to create the table.</p>";
    echo "<p><strong>Step 2:</strong> Execute the following SQL in your database:</p>";
    echo "</div>";
    
    // Read and display the SQL file
    $sql_file = __DIR__ . '/sql/create_booking_items_table.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        echo "<div class='sql-box'>" . htmlspecialchars($sql_content) . "</div>";
    } else {
        echo "<div class='status error'>SQL file not found at: <code>$sql_file</code></div>";
    }
}

// Check if Booking_item_model exists
$model_file = __DIR__ . '/application/models/Booking_item_model.php';
if (file_exists($model_file)) {
    echo "<div class='status success'><strong>✓ SUCCESS:</strong> Booking_item_model.php exists.</div>";
} else {
    echo "<div class='status error'><strong>✗ ERROR:</strong> Booking_item_model.php not found.</div>";
}

// Test database query directly
if ($table_exists) {
    echo "<h2>Direct Database Test</h2>";
    $test_booking_id = 1;
    $sql = "SELECT COUNT(*) as count FROM booking_items WHERE booking_id = ?";
    
    if ($dbdriver === 'mysqli') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $test_booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $test_count = $row['count'];
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$test_booking_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $test_count = $row['count'];
    }
    
    echo "<div class='status success'><strong>✓ SUCCESS:</strong> Direct database query works. Booking ID 1 has $test_count item(s).</div>";
}

// Close database connection
if ($dbdriver === 'mysqli') {
    $conn->close();
} else {
    $conn = null;
}

echo "<hr style='margin: 30px 0;'>";
echo "<div class='status warning'><strong>⚠️ SECURITY NOTE:</strong> Please delete this verification file (<code>verify_booking_items.php</code>) after you're done checking the database status.</div>";
echo "</div></body></html>";

