<?php
/**
 * API Endpoints Verification Script
 * This script tests all API endpoints to ensure they are working correctly
 * 
 * Usage: Access this file via browser or run via CLI: php test_api_endpoints.php
 */

// Set base URL - adjust this to match your server
//$base_url = 'http://localhost/bodarepensionhouse/admin/index.php/api';
$base_url = 'https://pensionhouse.bodarempc.com/admin/index.php/api';

// Test results storage
$results = [];
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

/**
 * Test an API endpoint
 */
function test_endpoint($name, $url, $method = 'GET', $data = null, $headers = []) {
    global $total_tests, $passed_tests, $failed_tests;
    
    $total_tests++;
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        }
    }
    
    $default_headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    $all_headers = array_merge($default_headers, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $all_headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $header_size);
    
    curl_close($ch);
    
    $success = ($http_code >= 200 && $http_code < 300) || $http_code === 401 || $http_code === 400;
    $is_json = json_decode($body) !== null;
    
    if ($success) {
        $passed_tests++;
    } else {
        $failed_tests++;
    }
    
    return [
        'name' => $name,
        'url' => $url,
        'method' => $method,
        'http_code' => $http_code,
        'success' => $success,
        'is_json' => $is_json,
        'response' => $body,
        'response_preview' => substr($body, 0, 200)
    ];
}

// Start testing
echo "<h1>API Endpoints Verification Report</h1>";
echo "<p>Base URL: <strong>$base_url</strong></p>";
echo "<p>Testing started at: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// ============================================
// AUTH ENDPOINTS
// ============================================
echo "<h2>1. Authentication Endpoints</h2>";

$results[] = test_endpoint(
    'Auth Check (GET)',
    "$base_url/auth/check",
    'GET'
);

$results[] = test_endpoint(
    'Auth Register (POST) - Should fail without data',
    "$base_url/auth/register",
    'POST',
    []
);

$results[] = test_endpoint(
    'Auth Login (POST) - Should fail without credentials',
    "$base_url/auth/login",
    'POST',
    []
);

$results[] = test_endpoint(
    'Auth Logout (POST)',
    "$base_url/auth/logout",
    'POST'
);

$results[] = test_endpoint(
    'Auth Forgot Password (POST) - Should fail without email',
    "$base_url/auth/forgot-password",
    'POST',
    []
);

$results[] = test_endpoint(
    'Auth Verify Reset Token (POST) - Should fail without token',
    "$base_url/auth/verify-reset-token",
    'POST',
    []
);

$results[] = test_endpoint(
    'Auth Reset Password (POST) - Should fail without token',
    "$base_url/auth/reset-password",
    'POST',
    []
);

// ============================================
// BOOKING ENDPOINTS
// ============================================
echo "<h2>2. Booking Endpoints</h2>";

$results[] = test_endpoint(
    'Get Rooms (GET)',
    "$base_url/booking/get_rooms",
    'GET'
);

$results[] = test_endpoint(
    'Check Availability (GET) - Should fail without dates',
    "$base_url/booking/availability",
    'GET'
);

$results[] = test_endpoint(
    'Check Availability (GET) - With dates',
    "$base_url/booking/availability?check_in=2025-12-01&check_out=2025-12-05",
    'GET'
);

$results[] = test_endpoint(
    'Get Availability (GET) - Should fail without date',
    "$base_url/booking/get_availability",
    'GET'
);

$results[] = test_endpoint(
    'Calculate Total (GET) - Should fail without params',
    "$base_url/booking/calculate",
    'GET'
);

$results[] = test_endpoint(
    'Create Booking (POST) - Should fail without data',
    "$base_url/booking/create",
    'POST',
    []
);

$results[] = test_endpoint(
    'My Bookings (GET) - Should fail without auth',
    "$base_url/booking/my-bookings",
    'GET'
);

$results[] = test_endpoint(
    'Get Booking by Number (GET) - Should fail without number',
    "$base_url/booking/number/",
    'GET'
);

// ============================================
// USER ENDPOINTS
// ============================================
echo "<h2>3. User Profile Endpoints</h2>";

$results[] = test_endpoint(
    'Get User Profile (GET) - Should fail without auth',
    "$base_url/user/profile",
    'GET'
);

$results[] = test_endpoint(
    'Update User Profile (POST) - Should fail without auth',
    "$base_url/user/update",
    'POST',
    []
);

// ============================================
// INQUIRY ENDPOINTS
// ============================================
echo "<h2>4. Inquiry Endpoints</h2>";

$results[] = test_endpoint(
    'Submit Inquiry (POST) - Should fail without auth',
    "$base_url/inquiry/submit",
    'POST',
    []
);

// ============================================
// DISPLAY RESULTS
// ============================================
echo "<hr>";
echo "<h2>Test Results Summary</h2>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>Endpoint</th><th>Method</th><th>HTTP Code</th><th>Status</th><th>Response Type</th><th>Preview</th>";
echo "</tr>";

foreach ($results as $result) {
    $status_color = $result['success'] ? '#d4edda' : '#f8d7da';
    $status_text = $result['success'] ? '✓ PASS' : '✗ FAIL';
    $json_status = $result['is_json'] ? 'JSON' : 'Not JSON';
    
    echo "<tr style='background: $status_color;'>";
    echo "<td><strong>{$result['name']}</strong></td>";
    echo "<td>{$result['method']}</td>";
    echo "<td>{$result['http_code']}</td>";
    echo "<td><strong>$status_text</strong></td>";
    echo "<td>$json_status</td>";
    echo "<td>" . htmlspecialchars($result['response_preview']) . "...</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Total Tests:</strong> $total_tests</p>";
echo "<p style='color: green;'><strong>Passed:</strong> $passed_tests</p>";
echo "<p style='color: red;'><strong>Failed:</strong> $failed_tests</p>";
echo "<p><strong>Success Rate:</strong> " . round(($passed_tests / $total_tests) * 100, 2) . "%</p>";

echo "<hr>";
echo "<h2>Notes</h2>";
echo "<ul>";
echo "<li>✓ PASS means the endpoint is accessible and returns a valid response (even if it's an error response for missing data/auth)</li>";
echo "<li>✗ FAIL means the endpoint returned an unexpected HTTP code (like 404, 500, etc.)</li>";
echo "<li>401/400 responses are considered PASS because they indicate the endpoint exists and is handling requests correctly</li>";
echo "<li>404 responses indicate the endpoint route is not working</li>";
echo "</ul>";

echo "<p><strong>Testing completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

