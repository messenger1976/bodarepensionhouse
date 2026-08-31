<?php
/**
 * Firebase / FCM helpers for the public site and API.
 */
if (!function_exists('bodare_firebase_config')) {
    function bodare_firebase_config()
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $defaults = [
            'push_enabled' => false,
            'project_id' => '',
            'service_account_json' => '',
        ];

        $path = __DIR__ . '/firebase-config.php';
        if (is_file($path)) {
            $loaded = require $path;
            if (is_array($loaded)) {
                $config = array_merge($defaults, $loaded);
                return $config;
            }
        }

        $config = $defaults;
        return $config;
    }
}

if (!function_exists('bodare_push_enabled')) {
    function bodare_push_enabled()
    {
        $config = bodare_firebase_config();
        return !empty($config['push_enabled']);
    }
}

if (!function_exists('bodare_firebase_project_id')) {
    function bodare_firebase_project_id()
    {
        $config = bodare_firebase_config();
        return trim((string) ($config['project_id'] ?? ''));
    }
}
