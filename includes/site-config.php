<?php
/**
 * Shared site identity and URL helpers for public SEO markup.
 */
if (!function_exists('bodare_site_config')) {
    function bodare_site_config()
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

        $host = $_SERVER['HTTP_HOST'] ?? 'pensionhouse.bodarempc.com';

        $basePath = '';
        $documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $publicRoot = realpath(dirname(__DIR__));
        if ($documentRoot && $publicRoot && strpos($publicRoot, $documentRoot) === 0) {
            $basePath = str_replace('\\', '/', substr($publicRoot, strlen($documentRoot)));
        } else {
            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
            if ($scriptName !== '' && $scriptName[0] !== '/') {
                $scriptName = '/' . $scriptName;
            }
            $basePath = str_replace('\\', '/', dirname($scriptName));
            // site-config lives in /includes; never treat that folder as the public base.
            if (substr($basePath, -9) === '/includes') {
                $basePath = substr($basePath, 0, -9);
            }
        }

        $basePath = rtrim($basePath, '/');
        if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
            $basePath = '';
        }

        $baseUrl = ($isHttps ? 'https' : 'http') . '://' . $host . $basePath;

        $config = [
            'name' => 'BODARE Pension House',
            'short_name' => 'BODARE',
            'legal_name' => 'Bodare and Community Multi-Purpose Cooperative',
            'tagline' => 'Comfortable and affordable lodging in the heart of Tagbilaran City, Bohol',
            'base_url' => $baseUrl,
            'default_og_image' => $baseUrl . '/img/og-default.jpg',
            'default_og_image_width' => 1200,
            'default_og_image_height' => 630,
            'default_og_image_type' => 'image/jpeg',
            'logo_url' => $baseUrl . '/img/logo.png',
            'email' => 'bodarepensionhouse@yahoo.com',
            'phone_display' => '0950 533 7480',
            'phone_e164' => '+639505337480',
            'street_address' => 'BODARE MPC & Community Bldg, J.A. Clarin St., Dao District',
            'address_locality' => 'Tagbilaran City',
            'address_region' => 'Bohol',
            'postal_code' => '6300',
            'address_country' => 'PH',
            'geo' => [
                'latitude' => 9.6729,
                'longitude' => 123.8736,
            ],
            'price_range' => '$$',
            'amenities' => [
                'Free WiFi',
                'Free parking',
                'Air conditioning',
                '24-hour front desk',
                'Cable TV',
                'Private bathrooms',
            ],
            // Public social profiles used in LocalBusiness schema sameAs.
            'same_as' => [
                'https://www.facebook.com/bodarepensionhouse',
            ],
            'facebook_url' => 'https://www.facebook.com/bodarepensionhouse',
            'messenger_url' => 'https://m.me/bodarepensionhouse',
        ];

        return $config;
    }
}

if (!function_exists('bodare_absolute_url')) {
    function bodare_absolute_url($path = '')
    {
        $site = bodare_site_config();
        $path = ltrim((string) $path, '/');
        return $path === '' ? $site['base_url'] . '/' : $site['base_url'] . '/' . $path;
    }
}

if (!function_exists('bodare_room_catalog')) {
    function bodare_room_catalog()
    {
        return [
            'dormitory' => [
                'title' => 'Dormitory',
                'description' => 'Budget-friendly dormitory lodging at BODARE Pension House in Tagbilaran City. Ideal for groups with shared, comfortable accommodations.',
                'image' => 'img/dormitory.jpg',
                'price' => 299,
                'price_unit' => 'per head',
                'capacity' => 'Minimum 8 persons',
            ],
            'executive' => [
                'title' => 'Executive Room',
                'description' => 'Spacious Executive Room at BODARE Pension House in Tagbilaran City. Comfortable lodging for families and business travelers.',
                'image' => 'img/executive.jpg',
                'price' => 1999,
                'price_unit' => 'per night',
                'capacity' => 'Good for 4 persons',
            ],
            'ambassador' => [
                'title' => 'Ambassador Room',
                'description' => 'Cozy Ambassador Room at BODARE Pension House in Tagbilaran City. A comfortable retreat for couples and small families.',
                'image' => 'img/ambassador.jpg',
                'price' => 1399,
                'price_unit' => 'per night',
                'capacity' => 'Good for 3 persons',
            ],
            'deluxea' => [
                'title' => 'Deluxe A Room',
                'description' => 'Stylish Deluxe A Room at BODARE Pension House in Tagbilaran City with modern amenities for a relaxing stay.',
                'image' => 'img/deluxea.jpg',
                'price' => 1299,
                'price_unit' => 'per night',
                'capacity' => 'Good for 3 persons',
            ],
            'deluxeb' => [
                'title' => 'Deluxe B Room',
                'description' => 'Comfortable Deluxe B Room at BODARE Pension House in Tagbilaran City. Modern amenities for up to four guests.',
                'image' => 'img/deluxeb.jpg',
                'price' => 1199,
                'price_unit' => 'per night',
                'capacity' => 'Good for 4 persons',
            ],
            'standard' => [
                'title' => 'Standard Room',
                'description' => 'Affordable Standard Room at BODARE Pension House in Tagbilaran City. Clean, comfortable lodging for two guests.',
                'image' => 'img/standard.jpg',
                'price' => 999,
                'price_unit' => 'per night',
                'capacity' => 'Good for 2 persons',
            ],
        ];
    }
}

if (!function_exists('bodare_db_config')) {
    function bodare_db_config()
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $db = [];
        $active_group = 'default';
        $query_builder = true;
        $configFile = dirname(__DIR__) . '/admin/application/config/database.php';
        if (!is_readable($configFile)) {
            return null;
        }

        if (!defined('ENVIRONMENT')) {
            define('ENVIRONMENT', 'development');
        }
        if (!defined('BASEPATH')) {
            define('BASEPATH', dirname(__DIR__) . '/admin/system/');
        }

        include $configFile;
        $config = isset($db[$active_group]) && is_array($db[$active_group]) ? $db[$active_group] : null;
        return $config;
    }
}

if (!function_exists('bodare_db')) {
    function bodare_db()
    {
        static $mysqli = false;
        if ($mysqli !== false) {
            return $mysqli ?: null;
        }

        $config = bodare_db_config();
        if (!$config) {
            $mysqli = null;
            return null;
        }

        mysqli_report(MYSQLI_REPORT_OFF);
        $connection = @new mysqli(
            $config['hostname'] ?? 'localhost',
            $config['username'] ?? '',
            $config['password'] ?? '',
            $config['database'] ?? ''
        );

        if ($connection->connect_error) {
            $mysqli = null;
            return null;
        }

        $charset = $config['char_set'] ?? 'utf8mb4';
        $connection->set_charset($charset);
        $mysqli = $connection;
        return $mysqli;
    }
}

if (!function_exists('bodare_resolve_room_image')) {
    function bodare_resolve_room_image($roomCode, $fallbackRelative = '')
    {
        $candidates = [];
        if ($fallbackRelative !== '') {
            $candidates[] = ltrim((string) $fallbackRelative, '/');
        }
        $candidates[] = 'img/' . $roomCode . '.jpg';
        $candidates[] = 'img/' . $roomCode . '.png';

        $db = bodare_db();
        if ($db) {
            $stmt = $db->prepare(
                'SELECT ri.image_path
                 FROM rooms r
                 INNER JOIN room_images ri ON ri.room_id = r.id
                 WHERE r.room_code = ? AND r.status = "active"
                 ORDER BY ri.is_primary DESC, ri.display_order ASC
                 LIMIT 1'
            );
            if ($stmt) {
                $stmt->bind_param('s', $roomCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                $stmt->close();
                if (!empty($row['image_path'])) {
                    $path = ltrim((string) $row['image_path'], '/');
                    array_unshift($candidates, $path, 'admin/' . $path);
                }
            }
        }

        $root = dirname(__DIR__);
        foreach (array_unique($candidates) as $relative) {
            $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (is_readable($absolute)) {
                return $relative;
            }
        }

        return $fallbackRelative !== '' ? ltrim((string) $fallbackRelative, '/') : 'img/og-default.jpg';
    }
}

if (!function_exists('bodare_room_setting')) {
    /**
     * Get a room setting from the database, with fallback default.
     */
    function bodare_room_setting($key, $default = null)
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            $db = bodare_db();
            if ($db) {
                $result = $db->query('SELECT setting_key, setting_value FROM room_settings');
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $cache[$row['setting_key']] = $row['setting_value'];
                    }
                    $result->free();
                }
            }
        }

        return array_key_exists($key, $cache) ? $cache[$key] : $default;
    }
}

if (!function_exists('bodare_format_peso')) {
    function bodare_format_peso($amount)
    {
        return '₱' . number_format((float) $amount, 2);
    }
}

if (!function_exists('bodare_parse_booking_extra_services')) {
    function bodare_parse_booking_extra_services($raw)
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $services = [];
        foreach ($decoded as $service) {
            if (!is_array($service) || empty($service['name'])) {
                continue;
            }
            $services[] = [
                'name' => trim((string) $service['name']),
                'cost' => isset($service['cost']) ? (float) $service['cost'] : null,
            ];
        }

        return $services;
    }
}

if (!function_exists('bodare_is_extra_bed_service')) {
    function bodare_is_extra_bed_service($name)
    {
        return is_string($name) && stripos($name, 'extra bed') === 0;
    }
}

if (!function_exists('bodare_parse_extra_beds_from_notes')) {
    function bodare_parse_extra_beds_from_notes($notes)
    {
        if (!is_string($notes) || $notes === '') {
            return 0;
        }

        if (preg_match('/,\s*(\d+)\s+extra\s+beds?\b/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        if (preg_match('/Extra\s+Beds?:\s*(\d+)/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        if (preg_match('/Extra\s+Beds?:[^|]*?(\d+)\s+bed\(s\)/i', $notes, $matches)) {
            return max(0, (int) $matches[1]);
        }

        return 0;
    }
}

if (!function_exists('bodare_get_booking_confirmation')) {
    /**
     * Load booking details for the public confirmation page.
     */
    function bodare_get_booking_confirmation($booking_number)
    {
        $booking_number = trim((string) $booking_number);
        if ($booking_number === '') {
            return null;
        }

        $db = bodare_db();
        if (!$db) {
            return null;
        }

        $stmt = $db->prepare(
            'SELECT b.*, r.room_name, r.room_type
             FROM bookings b
             LEFT JOIN rooms r ON r.id = b.room_id
             WHERE b.booking_number = ?
             LIMIT 1'
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $booking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$booking) {
            return null;
        }

        $items = [];
        $itemsStmt = $db->prepare(
            'SELECT room_id, room_name, check_in, check_out, price_per_night, nights, guests, subtotal, status
             FROM booking_items
             WHERE booking_id = ?
             ORDER BY id ASC'
        );
        if ($itemsStmt) {
            $bookingId = (int) $booking['id'];
            $itemsStmt->bind_param('i', $bookingId);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            if ($itemsResult) {
                while ($row = $itemsResult->fetch_assoc()) {
                    $items[] = $row;
                }
                $itemsResult->free();
            }
            $itemsStmt->close();
        }

        $services = bodare_parse_booking_extra_services($booking['extra_services'] ?? null);
        $extraBedLines = [];
        $otherServices = [];

        foreach ($services as $service) {
            if (bodare_is_extra_bed_service($service['name'])) {
                $extraBedLines[] = $service;
            } else {
                $otherServices[] = $service;
            }
        }

        $roomsSubtotal = 0.0;
        foreach ($items as $item) {
            $roomsSubtotal += (float) ($item['subtotal'] ?? 0);
        }

        $otherServicesTotal = 0.0;
        foreach ($otherServices as $service) {
            $otherServicesTotal += (float) ($service['cost'] ?? 0);
        }

        $extraBedTotal = 0.0;
        foreach ($extraBedLines as $line) {
            $extraBedTotal += (float) ($line['cost'] ?? 0);
        }

        if (empty($extraBedLines)) {
            $extraBedsFromNotes = bodare_parse_extra_beds_from_notes($booking['notes'] ?? '');
            $totalAmount = (float) ($booking['total_amount'] ?? 0);
            $gap = round($totalAmount - $roomsSubtotal - $otherServicesTotal - $extraBedTotal, 2);

            if ($gap > 0) {
                $extraBedLines[] = [
                    'name' => 'Extra Bed',
                    'cost' => $gap,
                ];
                $extraBedTotal += $gap;
            } elseif ($extraBedsFromNotes > 0) {
                $nights = 1;
                if (!empty($items[0]['nights'])) {
                    $nights = max(1, (int) $items[0]['nights']);
                } elseif (!empty($booking['check_in']) && !empty($booking['check_out'])) {
                    $checkIn = new DateTime($booking['check_in']);
                    $checkOut = new DateTime($booking['check_out']);
                    $nights = max(1, (int) $checkIn->diff($checkOut)->days);
                }

                $extraBedPrice = (float) bodare_room_setting('extra_bed_price', 199);
                $computedExtraBedTotal = $extraBedsFromNotes * $extraBedPrice * $nights;
                $bedLabel = $extraBedsFromNotes === 1 ? 'bed' : 'beds';
                $nightLabel = $nights === 1 ? 'night' : 'nights';
                $roomLabel = !empty($items[0]['room_name']) ? $items[0]['room_name'] : ($booking['room_name'] ?? 'Room');

                $extraBedLines[] = [
                    'name' => "Extra Bed ({$extraBedsFromNotes} {$bedLabel} × {$nights} {$nightLabel} @ " . bodare_format_peso($extraBedPrice) . " — {$roomLabel})",
                    'cost' => $computedExtraBedTotal,
                ];
                $extraBedTotal += $computedExtraBedTotal;
            }
        }

        return [
            'booking' => $booking,
            'items' => $items,
            'extra_bed_lines' => $extraBedLines,
            'other_services' => $otherServices,
            'rooms_subtotal' => $roomsSubtotal,
            'extra_bed_total' => $extraBedTotal,
            'other_services_total' => $otherServicesTotal,
            'display_total' => $roomsSubtotal + $extraBedTotal + $otherServicesTotal,
        ];
    }
}

if (!function_exists('bodare_room_codes')) {
    /**
     * Active room codes from the database, falling back to the static catalog.
     */
    function bodare_room_codes()
    {
        static $codes = null;
        if ($codes !== null) {
            return $codes;
        }

        $codes = [];
        $db = bodare_db();
        if ($db) {
            $result = $db->query(
                'SELECT room_code FROM rooms WHERE status = "active" AND room_code IS NOT NULL AND room_code != "" ORDER BY id ASC'
            );
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $code = strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $row['room_code']));
                    if ($code !== '') {
                        $codes[] = $code;
                    }
                }
                $result->free();
            }
        }

        if (empty($codes)) {
            $codes = array_keys(bodare_room_catalog());
        }

        return $codes;
    }
}

if (!function_exists('bodare_room_price_unit')) {
    function bodare_room_price_unit($roomCode, $roomType = '')
    {
        $haystack = strtolower($roomCode . ' ' . $roomType);
        return strpos($haystack, 'dormitory') !== false ? 'per head' : 'per night';
    }
}

if (!function_exists('bodare_live_room')) {
    /**
     * Merge catalog defaults with live API/database room data for SEO and schema.
     */
    function bodare_live_room($roomCode)
    {
        $roomCode = strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $roomCode));
        if ($roomCode === '') {
            return null;
        }

        $catalog = bodare_room_catalog();
        $inCatalog = isset($catalog[$roomCode]);

        // Rooms added in the admin panel are not part of the static catalog,
        // so start from generic defaults and let live data fill them in.
        $room = $inCatalog ? $catalog[$roomCode] : [
            'title' => ucwords(trim(preg_replace('/[_-]+/', ' ', $roomCode))),
            'description' => '',
            'image' => '',
            'price' => 0,
            'price_unit' => 'per night',
            'capacity' => '',
        ];
        $room['code'] = $roomCode;
        $room['source'] = 'catalog';

        $live = null;
        $db = bodare_db();
        if ($db) {
            $stmt = $db->prepare(
                'SELECT room_code, room_name, room_type, price, capacity, description, amenities, status
                 FROM rooms
                 WHERE room_code = ? AND status = "active"
                 LIMIT 1'
            );
            if ($stmt) {
                $stmt->bind_param('s', $roomCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $live = $result ? $result->fetch_assoc() : null;
                $stmt->close();
            }
        }

        if (!$live) {
            $live = bodare_fetch_room_via_api($roomCode);
        }

        if (!$live && !$inCatalog) {
            return null;
        }

        if ($live) {
            $room['source'] = 'live';
            if (!empty($live['room_name'])) {
                $room['title'] = trim((string) $live['room_name']);
            }
            if (isset($live['price']) && $live['price'] !== '' && is_numeric($live['price'])) {
                $room['price'] = (float) $live['price'];
            }
            if (isset($live['capacity']) && $live['capacity'] !== '' && is_numeric($live['capacity'])) {
                $capacity = (int) $live['capacity'];
                $room['capacity'] = $capacity === 1
                    ? 'Good for 1 person'
                    : 'Good for ' . $capacity . ' persons';
                $room['capacity_value'] = $capacity;
            }
            if (!empty($live['description'])) {
                $room['description'] = trim((string) $live['description']);
                $room['seo_description'] = bodare_room_seo_description($room['title'], $room['description']);
            }
            if (!empty($live['amenities'])) {
                $room['amenities'] = array_values(array_filter(array_map('trim', preg_split('/[,;|]+/', (string) $live['amenities']))));
            }
            if (!empty($live['room_type'])) {
                $room['room_type'] = trim((string) $live['room_type']);
            }
        }

        if (empty($room['description'])) {
            $room['description'] = $room['title'] . ' offers a comfortable and well-appointed space for your stay at BODARE Pension House.';
        }
        if (empty($room['capacity'])) {
            $room['capacity'] = 'Capacity varies';
        }
        if (empty($room['seo_description'])) {
            $room['seo_description'] = bodare_room_seo_description($room['title'], $room['description']);
        }
        if (empty($room['amenities'])) {
            $room['amenities'] = [];
        }
        if (empty($room['capacity_value'])) {
            $room['capacity_value'] = null;
        }

        $room['price_unit'] = bodare_room_price_unit($roomCode, isset($room['room_type']) ? $room['room_type'] : '');
        $room['image'] = bodare_resolve_room_image($roomCode, $room['image']);

        return $room;
    }
}

if (!function_exists('bodare_room_seo_description')) {
    function bodare_room_seo_description($title, $description)
    {
        $clean = preg_replace('/\s+/', ' ', trim(strip_tags((string) $description)));
        $prefix = $title . ' at BODARE Pension House in Tagbilaran City, Bohol. ';
        $combined = $prefix . $clean;
        if (strlen($combined) <= 160) {
            return $combined;
        }
        return rtrim(substr($combined, 0, 157)) . '...';
    }
}

if (!function_exists('bodare_fetch_room_via_api')) {
    function bodare_fetch_room_via_api($roomCode)
    {
        $site = bodare_site_config();
        $candidates = [
            $site['base_url'] . '/admin/index.php/api/booking/get_room_by_code/' . rawurlencode($roomCode),
            $site['base_url'] . '/admin/api/booking/get_room_by_code/' . rawurlencode($roomCode),
        ];

        foreach ($candidates as $url) {
            $payload = bodare_http_get_json($url);
            if (!$payload || empty($payload['success']) || empty($payload['room'])) {
                continue;
            }

            $room = $payload['room'];
            if (is_object($room)) {
                $room = (array) $room;
            }
            if (!is_array($room)) {
                continue;
            }

            return [
                'room_code' => $room['room_code'] ?? $roomCode,
                'room_name' => $room['room_name'] ?? '',
                'room_type' => $room['room_type'] ?? '',
                'price' => $room['price'] ?? null,
                'capacity' => $room['capacity'] ?? null,
                'description' => $room['description'] ?? '',
                'amenities' => $room['amenities'] ?? '',
                'status' => $room['status'] ?? 'active',
            ];
        }

        return null;
    }
}

if (!function_exists('bodare_http_get_json')) {
    function bodare_http_get_json($url)
    {
        if (!function_exists('curl_init')) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 3,
                    'header' => "Accept: application/json\r\n",
                    'ignore_errors' => true,
                ],
            ]);
            $raw = @file_get_contents($url, false, $context);
            if ($raw === false) {
                return null;
            }
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $status >= 400) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('bodare_og_image_meta')) {
    function bodare_og_image_meta($imageUrlOrPath = null)
    {
        $site = bodare_site_config();
        $defaults = [
            'url' => $site['default_og_image'],
            'width' => (int) $site['default_og_image_width'],
            'height' => (int) $site['default_og_image_height'],
            'type' => $site['default_og_image_type'],
        ];

        if ($imageUrlOrPath === null || $imageUrlOrPath === '') {
            return $defaults;
        }

        $image = (string) $imageUrlOrPath;
        $relative = $image;
        if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
            $base = rtrim($site['base_url'], '/') . '/';
            if (strpos($image, $base) === 0) {
                $relative = substr($image, strlen($base));
            } else {
                return [
                    'url' => $image,
                    'width' => $defaults['width'],
                    'height' => $defaults['height'],
                    'type' => $defaults['type'],
                ];
            }
        }

        $relative = ltrim($relative, '/');
        $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $url = (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0)
            ? $image
            : bodare_absolute_url($relative);

        $meta = [
            'url' => $url,
            'width' => $defaults['width'],
            'height' => $defaults['height'],
            'type' => $defaults['type'],
        ];

        if (is_readable($absolutePath)) {
            $size = @getimagesize($absolutePath);
            if (is_array($size)) {
                $meta['width'] = (int) $size[0];
                $meta['height'] = (int) $size[1];
                if (!empty($size['mime'])) {
                    $meta['type'] = $size['mime'];
                }
            }
        }

        return $meta;
    }
}

if (!function_exists('bodare_business_json_ld')) {
    function bodare_business_json_ld(array $overrides = [])
    {
        $site = bodare_site_config();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            '@id' => bodare_absolute_url() . '#lodging',
            'name' => $site['name'],
            'alternateName' => $site['legal_name'],
            'description' => $site['tagline'],
            'url' => bodare_absolute_url(),
            'image' => [
                $site['default_og_image'],
                bodare_absolute_url('img/ambassador.jpg'),
                bodare_absolute_url('img/deluxea.jpg'),
            ],
            'logo' => $site['logo_url'],
            'telephone' => $site['phone_e164'],
            'email' => $site['email'],
            'priceRange' => $site['price_range'],
            'currenciesAccepted' => 'PHP',
            'paymentAccepted' => 'Cash, Visa, GCash',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['street_address'],
                'addressLocality' => $site['address_locality'],
                'addressRegion' => $site['address_region'],
                'postalCode' => $site['postal_code'],
                'addressCountry' => $site['address_country'],
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $site['geo']['latitude'],
                'longitude' => $site['geo']['longitude'],
            ],
            'hasMap' => 'https://www.google.com/maps?q=BODARE%20MPC%20%26%20Community%20Bldg%2C%20J.A.%20Clarin%20St.%2C%20Dao%20District%2C%20Tagbilaran%20City%2C%20Bohol%2C%20Philippines%206300',
            'amenityFeature' => array_map(static function ($amenity) {
                return [
                    '@type' => 'LocationFeatureSpecification',
                    'name' => $amenity,
                    'value' => true,
                ];
            }, $site['amenities']),
            'parentOrganization' => [
                '@type' => 'Organization',
                'name' => $site['legal_name'],
                'url' => 'https://bodarempc.com/',
            ],
        ];

        if (!empty($site['same_as'])) {
            $data['sameAs'] = array_values(array_filter($site['same_as']));
        }

        return array_replace_recursive($data, $overrides);
    }
}
