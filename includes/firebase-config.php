<?php
/**
 * Firebase / FCM configuration for BODARE Pension House.
 * Gitignored — do not commit.
 */
return [
    // Set true after google-services.json is in mobile/android/app/ and APK is rebuilt.
    'push_enabled' => true,

    'project_id' => 'booking-system-b52f4',

    'service_account_json' => dirname(__DIR__) . '/admin/config/firebase-service-account.json',

    // Change this on production before enabling cron.
    'cron_secret' => 'change-me-to-a-long-random-string',
];
