<?php
/**
 * Canonical application timezone: Asia/Manila (Philippine Time, UTC+8).
 *
 * Include early from public pages (via site-config.php) and admin/index.php
 * so all date()/strtotime() writes and displays use Manila wall time.
 */
if (!defined('BODARE_TIMEZONE')) {
    define('BODARE_TIMEZONE', 'Asia/Manila');
}

if (!defined('BODARE_TIMEZONE_OFFSET')) {
    // Fixed offset — Asia/Manila has no DST.
    define('BODARE_TIMEZONE_OFFSET', '+08:00');
}

if (!function_exists('bodare_apply_timezone')) {
    /**
     * Set PHP default timezone to Asia/Manila (idempotent).
     */
    function bodare_apply_timezone()
    {
        if (date_default_timezone_get() !== BODARE_TIMEZONE) {
            date_default_timezone_set(BODARE_TIMEZONE);
        }
    }
}

if (!function_exists('bodare_apply_mysql_timezone')) {
    /**
     * Align a mysqli connection session with Asia/Manila.
     *
     * @param mysqli $connection
     * @return void
     */
    function bodare_apply_mysql_timezone($connection)
    {
        if (!$connection || !($connection instanceof mysqli)) {
            return;
        }
        @$connection->query("SET time_zone = '" . BODARE_TIMEZONE_OFFSET . "'");
    }
}

bodare_apply_timezone();
