<?php
/**
 * Dynamic XML sitemap for public indexable pages, served as /sitemap.xml (see .htaccess).
 * Room URLs come from active rooms in the database, so new rooms appear automatically.
 */
require_once __DIR__ . '/includes/site-config.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$site = bodare_site_config();

$fileLastmod = static function ($file) {
    $path = __DIR__ . '/' . $file;
    return is_file($path) ? date('Y-m-d', filemtime($path)) : date('Y-m-d');
};

$pages = [];
if (empty($site['seo_noindex'])) {
    $staticPages = [
        ['index.php', '', 'weekly', '1.0'],
        ['rooms.php', 'rooms.php', 'weekly', '0.9'],
        ['contact.php', 'contact.php', 'monthly', '0.8'],
        ['amenities.php', 'amenities.php', 'monthly', '0.7'],
        ['gallery.php', 'gallery.php', 'monthly', '0.7'],
        ['landing-page.php', 'landing-page.php', 'monthly', '0.6'],
    ];
    foreach ($staticPages as $page) {
        $entry = [
            'loc' => bodare_absolute_url($page[1]),
            'lastmod' => $fileLastmod($page[0]),
            'changefreq' => $page[2],
            'priority' => $page[3],
            'images' => [],
        ];
        if ($page[1] === '') {
            $entry['images'][] = ['loc' => $site['default_og_image'], 'title' => $site['name']];
        }
        $pages[] = $entry;
    }

    $rooms = [];
    $db = bodare_db();
    if ($db) {
        $result = $db->query(
            'SELECT room_code, room_name, updated_at FROM rooms
             WHERE status = "active" AND room_code IS NOT NULL AND room_code != ""
             ORDER BY id ASC'
        );
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $code = strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $row['room_code']));
                if ($code !== '') {
                    $rooms[$code] = $row;
                }
            }
            $result->free();
        }
    }
    if (empty($rooms)) {
        foreach (bodare_room_catalog() as $code => $room) {
            $rooms[$code] = ['room_name' => $room['title'], 'updated_at' => null];
        }
    }

    $detailLastmod = $fileLastmod('room-detail.php');
    foreach ($rooms as $code => $room) {
        $lastmod = $detailLastmod;
        if (!empty($room['updated_at']) && strtotime($room['updated_at'])) {
            $roomDate = date('Y-m-d', strtotime($room['updated_at']));
            $lastmod = max($lastmod, $roomDate);
        }
        $image = bodare_resolve_room_image($code, '');
        $pages[] = [
            'loc' => bodare_absolute_url('room-detail.php?room=' . rawurlencode($code)),
            'lastmod' => $lastmod,
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'images' => $image !== '' && is_file(__DIR__ . '/' . $image)
                ? [['loc' => bodare_absolute_url($image), 'title' => trim((string) $room['room_name']) . ' - ' . $site['name']]]
                : [],
        ];
    }
}

$x = static function ($value) {
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
};

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?php echo $x($page['loc']); ?></loc>
    <lastmod><?php echo $x($page['lastmod']); ?></lastmod>
    <changefreq><?php echo $x($page['changefreq']); ?></changefreq>
    <priority><?php echo $x($page['priority']); ?></priority>
<?php foreach ($page['images'] as $image): ?>
    <image:image>
      <image:loc><?php echo $x($image['loc']); ?></image:loc>
      <image:title><?php echo $x($image['title']); ?></image:title>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endforeach; ?>
</urlset>
