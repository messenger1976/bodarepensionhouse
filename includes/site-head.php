<?php
require_once __DIR__ . '/site-config.php';
require_once __DIR__ . '/firebase.php';
require_once __DIR__ . '/adsense.php';

if (!isset($enableAds)) {
    $enableAds = true;
}
$GLOBALS['enableAds'] = $enableAds;

$site = bodare_site_config();

$defaultPageSeo = [
    'title' => $site['name'],
    'description' => $site['tagline'],
    'canonical_path' => '',
    'robots' => 'index,follow,max-image-preview:large',
    'og_type' => 'website',
    'og_image' => $site['default_og_image'],
    'og_image_alt' => $site['name'] . ' in Tagbilaran City, Bohol',
    'include_bootstrap_icons' => true,
    'include_business_schema' => false,
    'json_ld' => [],
    'extra_head' => '',
];

$pageSeo = isset($pageSeo) && is_array($pageSeo)
    ? array_merge($defaultPageSeo, $pageSeo)
    : $defaultPageSeo;

$pageTitle = trim((string) $pageSeo['title']);
$pageDescription = trim((string) $pageSeo['description']);
$canonicalPath = ltrim((string) $pageSeo['canonical_path'], '/');
$canonicalUrl = bodare_absolute_url($canonicalPath === '' || $canonicalPath === 'index.php' ? '' : $canonicalPath);
$ogImageInput = (string) $pageSeo['og_image'];
$ogMeta = bodare_og_image_meta($ogImageInput !== '' ? $ogImageInput : null);
$ogImage = $ogMeta['url'];
$ogImageWidth = (int) $ogMeta['width'];
$ogImageHeight = (int) $ogMeta['height'];
$ogImageType = (string) $ogMeta['type'];
$robots = (string) $pageSeo['robots'];
$ogType = (string) $pageSeo['og_type'];
$ogImageAlt = (string) $pageSeo['og_image_alt'];

$jsonLdBlocks = [];
if (!empty($pageSeo['include_business_schema'])) {
    $jsonLdBlocks[] = bodare_business_json_ld();
}
if (!empty($pageSeo['json_ld'])) {
    if (isset($pageSeo['json_ld'][0]) && is_array($pageSeo['json_ld'][0])) {
        foreach ($pageSeo['json_ld'] as $block) {
            if (is_array($block)) {
                $jsonLdBlocks[] = $block;
            }
        }
    } elseif (is_array($pageSeo['json_ld'])) {
        $jsonLdBlocks[] = $pageSeo['json_ld'];
    }
}

$h = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="app-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $h($pageTitle); ?></title>
    <meta name="description" content="<?php echo $h($pageDescription); ?>">
    <meta name="robots" content="<?php echo $h($robots); ?>">
    <link rel="canonical" href="<?php echo $h($canonicalUrl); ?>">

    <meta property="og:locale" content="en_PH">
    <meta property="og:type" content="<?php echo $h($ogType); ?>">
    <meta property="og:site_name" content="<?php echo $h($site['name']); ?>">
    <meta property="og:title" content="<?php echo $h($pageTitle); ?>">
    <meta property="og:description" content="<?php echo $h($pageDescription); ?>">
    <meta property="og:url" content="<?php echo $h($canonicalUrl); ?>">
    <meta property="og:image" content="<?php echo $h($ogImage); ?>">
    <meta property="og:image:secure_url" content="<?php echo $h($ogImage); ?>">
    <meta property="og:image:type" content="<?php echo $h($ogImageType); ?>">
    <meta property="og:image:width" content="<?php echo $h($ogImageWidth); ?>">
    <meta property="og:image:height" content="<?php echo $h($ogImageHeight); ?>">
    <meta property="og:image:alt" content="<?php echo $h($ogImageAlt); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $h($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo $h($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo $h($ogImage); ?>">
    <meta name="twitter:image:alt" content="<?php echo $h($ogImageAlt); ?>">

    <meta name="theme-color" content="#065f46">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo $h($site['short_name']); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="geo.region" content="PH-BOH">
    <meta name="geo.placename" content="Tagbilaran City">
    <meta name="author" content="<?php echo $h($site['legal_name']); ?>">

    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16.png">
    <link rel="apple-touch-icon" href="img/logo.png">
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="stylesheet" href="style.css?v=<?php echo is_file(dirname(__DIR__) . '/style.css') ? filemtime(dirname(__DIR__) . '/style.css') : time(); ?>">
    <link rel="stylesheet" href="app-shell.css?v=<?php echo is_file(dirname(__DIR__) . '/app-shell.css') ? filemtime(dirname(__DIR__) . '/app-shell.css') : time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (window.tailwind) {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            bodare: { emerald: '#065f46', gold: '#facc15', ink: '#022c22' }
                        }
                    }
                }
            };
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@200;300;400;600;700;800&display=swap" rel="stylesheet">
<?php if (!empty($pageSeo['include_bootstrap_icons'])): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<?php endif; ?>
<?php if (!empty($pageSeo['extra_head'])): ?>
    <?php echo $pageSeo['extra_head']; ?>
<?php endif; ?>
<?php foreach ($jsonLdBlocks as $jsonLdBlock): ?>
    <script type="application/ld+json"><?php echo json_encode($jsonLdBlock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?></script>
<?php endforeach; ?>
<?php adsense_render_head(); ?>
    <script>
        window.BODARE_EXTRA_BED_PRICE = <?php echo json_encode((float) bodare_room_setting('extra_bed_price', 199)); ?>;
        window.BODARE_CHECK_IN_TIME = <?php echo json_encode((string) bodare_booking_setting('check_in_time', '14:00')); ?>;
        window.BODARE_CHECK_OUT_TIME = <?php echo json_encode((string) bodare_booking_setting('check_out_time', '12:00')); ?>;
        if (window.Capacitor) {
            document.documentElement.classList.add('is-capacitor');
        }
        window.BODARE_PUSH_ENABLED = <?php echo json_encode(bodare_push_enabled()); ?>;
    </script>
    <script src="native-bridge.js?v=<?php echo is_file(dirname(__DIR__) . '/native-bridge.js') ? filemtime(dirname(__DIR__) . '/native-bridge.js') : time(); ?>"></script>
</head>
