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
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname($scriptName), '/');
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
            'default_og_image' => $baseUrl . '/img/executive.jpg',
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
            // Add real profile URLs when available; empty values are omitted from schema.
            'same_as' => [],
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
