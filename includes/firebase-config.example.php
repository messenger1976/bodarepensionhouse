<?php
/**
 * Firebase / FCM configuration (copy to firebase-config.php and edit).
 *
 * Client push (Capacitor app):
 *   - Add google-services.json to mobile/android/app/ and rebuild the APK.
 *   - Set push_enabled => true below and deploy this file as firebase-config.php.
 *
 * Server push (booking alerts from PHP):
 *   - Firebase Console → Project settings → Service accounts → Generate new private key.
 *   - Save the JSON outside the web root (see service_account_json path).
 *   - Set project_id to match your Firebase project.
 *
 * Do not commit firebase-config.php or service account JSON.
 */
return [
    // Set true after google-services.json is in the Android app and APK is rebuilt.
    'push_enabled' => false,

    // Firebase project ID (same as in google-services.json / Firebase Console).
    'project_id' => 'booking-system-b52f4',

    // Absolute path to the Firebase Admin service account JSON (server-side send only).
    'service_account_json' => dirname(__DIR__) . '/admin/config/firebase-service-account.json',

    // Secret for cron URLs (check-in reminders). Required for /admin/index.php/cron/check_in_reminders?key=...
    'cron_secret' => 'change-me-to-a-long-random-string',
];
