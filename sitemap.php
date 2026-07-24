<?php
/**
 * Dynamic XML sitemap for public indexable pages.
 */
require_once __DIR__ . '/includes/site-config.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$site = bodare_site_config();
$today = date('Y-m-d');

$pages = [
    [
        'loc' => bodare_absolute_url(),
        'lastmod' => $today,
        'changefreq' => 'weekly',
        'priority' => '1.0',
    ],
    [
        'loc' => bodare_absolute_url('rooms.php'),
        'lastmod' => $today,
        'changefreq' => 'weekly',
        'priority' => '0.9',
    ],
    [
        'loc' => bodare_absolute_url('amenities.php'),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.7',
    ],
    [
        'loc' => bodare_absolute_url('gallery.php'),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.7',
    ],
    [
        'loc' => bodare_absolute_url('contact.php'),
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.8',
    ],
];

foreach (bodare_room_codes() as $roomKey) {
    $pages[] = [
        'loc' => bodare_absolute_url('room-detail.php?room=' . rawurlencode($roomKey)),
        'lastmod' => $today,
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?php echo htmlspecialchars($page['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></loc>
    <lastmod><?php echo htmlspecialchars($page['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></lastmod>
    <changefreq><?php echo htmlspecialchars($page['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></changefreq>
    <priority><?php echo htmlspecialchars($page['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
