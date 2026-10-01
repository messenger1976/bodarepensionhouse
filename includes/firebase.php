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
            'cron_secret' => '',
            'web_push_enabled' => false,
            'web' => [],
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

if (!function_exists('bodare_firebase_web_config')) {
    /**
     * Public Firebase web config for browser (PWA) push, or null when web push is off
     * or incomplete. All values here are public by design — never add server secrets.
     */
    function bodare_firebase_web_config()
    {
        $config = bodare_firebase_config();
        if (empty($config['push_enabled']) || empty($config['web_push_enabled'])) {
            return null;
        }

        $web = is_array($config['web'] ?? null) ? $config['web'] : [];
        $out = [
            'apiKey' => trim((string) ($web['apiKey'] ?? '')),
            'appId' => trim((string) ($web['appId'] ?? '')),
            'messagingSenderId' => trim((string) ($web['messagingSenderId'] ?? '')),
            'vapidKey' => trim((string) ($web['vapidKey'] ?? '')),
            'projectId' => bodare_firebase_project_id(),
        ];

        foreach ($out as $value) {
            if ($value === '') {
                return null;
            }
        }
        return $out;
    }
}
