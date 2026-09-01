<?php
/**
 * Google AdSense helpers.
 *
 * Loaded from includes/site-head.php. Set $enableAds = false on a page before
 * including site-head.php to skip ads (checkout, auth, dashboard, contact).
 */

if (!function_exists('adsense_config')) {
    function adsense_config()
    {
        static $config;
        if ($config === null) {
            $config = require __DIR__ . '/adsense-config.php';
        }
        return $config;
    }
}

if (!function_exists('adsense_publisher_id_valid')) {
    function adsense_publisher_id_valid($publisherId)
    {
        $publisherId = trim((string) $publisherId);
        if ($publisherId === '' || stripos($publisherId, 'XXXX') !== false) {
            return false;
        }
        return (bool) preg_match('/^ca-pub-\d{10,20}$/', $publisherId);
    }
}

if (!function_exists('adsense_page_allows_ads')) {
    function adsense_page_allows_ads()
    {
        return !isset($GLOBALS['enableAds']) || $GLOBALS['enableAds'] !== false;
    }
}

if (!function_exists('adsense_is_enabled')) {
    function adsense_is_enabled()
    {
        if (!adsense_page_allows_ads()) {
            return false;
        }

        $config = adsense_config();
        if (empty($config['enabled'])) {
            return false;
        }

        return adsense_publisher_id_valid($config['publisher_id'] ?? '');
    }
}

if (!function_exists('adsense_render_head')) {
    /**
     * Output AdSense verification meta + script for <head>.
     */
    function adsense_render_head()
    {
        $config = adsense_config();
        $publisherId = trim((string) ($config['publisher_id'] ?? ''));

        if (adsense_publisher_id_valid($publisherId) && adsense_page_allows_ads()) {
            echo '<meta name="google-adsense-account" content="'
                . htmlspecialchars($publisherId, ENT_QUOTES, 'UTF-8')
                . '">' . "\n";
        }

        if (!adsense_is_enabled()) {
            return;
        }

        $client = htmlspecialchars($publisherId, ENT_QUOTES, 'UTF-8');
        echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
            . $client . '" crossorigin="anonymous"></script>' . "\n";
    }
}

if (!function_exists('adsense_slot_id')) {
    function adsense_slot_id($slotKey)
    {
        $config = adsense_config();
        $slot = trim((string) ($config['slots'][$slotKey] ?? ''));
        if ($slot === '' || stripos($slot, 'XXXX') !== false) {
            return '';
        }
        return $slot;
    }
}

if (!function_exists('adsense_render_unit')) {
    /**
     * Render a responsive manual ad unit. No-op until slot ID is configured.
     *
     * @param string $slotKey Key from adsense-config.php slots array
     * @param string $class   Extra CSS classes for the wrapper
     */
    function adsense_render_unit($slotKey = 'in_content', $class = '')
    {
        if (!adsense_is_enabled()) {
            return;
        }

        $slot = adsense_slot_id($slotKey);
        if ($slot === '') {
            return;
        }

        $config = adsense_config();
        $client = htmlspecialchars($config['publisher_id'], ENT_QUOTES, 'UTF-8');
        $slotEsc = htmlspecialchars($slot, ENT_QUOTES, 'UTF-8');
        $wrapClass = trim('adsense-wrap ' . $class);

        echo '<div class="' . htmlspecialchars($wrapClass, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        echo '  <ins class="adsbygoogle"' . "\n";
        echo '       style="display:block"' . "\n";
        echo '       data-ad-client="' . $client . '"' . "\n";
        echo '       data-ad-slot="' . $slotEsc . '"' . "\n";
        echo '       data-ad-format="auto"' . "\n";
        echo '       data-full-width-responsive="true"></ins>' . "\n";
        echo '  <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>' . "\n";
        echo '</div>' . "\n";
    }
}
