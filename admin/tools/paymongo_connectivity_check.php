<?php
/**
 * One-off connectivity check: php admin/tools/paymongo_connectivity_check.php
 * Does not send your secret key to any third party except a GET-less TCP/HTTPS probe to api.paymongo.com.
 */
$targets = array(
    'https://api.paymongo.com/',
    'https://api.paymongo.com/v1/checkout_sessions'
);

echo "PayMongo connectivity check\n";
echo "PHP " . PHP_VERSION . " | curl=" . (function_exists('curl_version') ? curl_version()['version'] : 'no') . "\n\n";

foreach ($targets as $url) {
    echo "=== $url ===\n";
    if (!function_exists('curl_init')) {
        echo "cURL extension missing\n\n";
        continue;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTP_VERSION => defined('CURL_HTTP_VERSION_1_1') ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_NONE,
        CURLOPT_IPRESOLVE => defined('CURL_IPRESOLVE_V4') ? CURL_IPRESOLVE_V4 : CURL_IPRESOLVE_WHATEVER,
    ));
    curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ip = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    curl_close($ch);
    echo "HTTP=$http errno=$errno error=" . ($error !== '' ? $error : '(none)') . " ip=$ip\n\n";
}
