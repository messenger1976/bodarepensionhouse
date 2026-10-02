-- Site Settings module (Admin → Site Settings / profile menu → Settings)
-- Website identity, SEO, logo, address, contact info and social media links.
-- The public site (includes/site-config.php) reads these rows and falls back to its
-- built-in defaults for any key that is missing or blank.
-- Run on the admin database after deploying the module files. Safe to re-run.

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed with the values the public site used before this module existed.
-- INSERT IGNORE keeps anything an admin has already saved.
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'BODARE Pension House'),
('site_short_name', 'BODARE'),
('legal_name', 'Bodare and Community Multi-Purpose Cooperative'),
('tagline', 'Comfortable and affordable lodging in the heart of Tagbilaran City, Bohol'),
('about_text', 'Bodare and Community Multi-Purpose Cooperative offers comfortable and affordable lodging in the heart of Tagbilaran City, providing a welcoming stay for all our guests.'),
('meta_title', ''),
('meta_description', ''),
('meta_keywords', 'BODARE Pension House, Tagbilaran City hotel, Bohol lodging, affordable rooms Tagbilaran, pension house Bohol'),
('meta_tags', ''),
('seo_noindex', '0'),
('google_site_verification', ''),
('bing_site_verification', ''),
('google_analytics_id', ''),
('logo_path', ''),
('og_image_path', ''),
('street_address', 'BODARE MPC & Community Bldg, J.A. Clarin St., Dao District'),
('address_locality', 'Tagbilaran City'),
('address_region', 'Bohol'),
('postal_code', '6300'),
('address_country', 'PH'),
('geo_latitude', '9.656042'),
('geo_longitude', '123.867535'),
('map_url', ''),
('contact_email', 'bodarepensionhouse@yahoo.com'),
('contact_phone', '0950 533 7480'),
('contact_phone_e164', '+639505337480'),
('contact_phone_alt', ''),
('business_hours', 'Front desk open 24 hours'),
('facebook_url', 'https://www.facebook.com/bodarepensionhouse'),
('messenger_url', 'https://m.me/bodarepensionhouse'),
('instagram_url', ''),
('tiktok_url', ''),
('youtube_url', ''),
('x_url', ''),
('linkedin_url', '');

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Site Settings', 'view_site_settings', 'Permission to open Site Settings and see the website name, SEO, logo, address, contact and social media settings', 'site_settings'),
('Manage Site Settings', 'manage_site_settings', 'Permission to change the website name, SEO, logo, address, contact and social media settings', 'site_settings')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign both permissions to the Super Admin role (role_id = 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE `slug` IN (
    'view_site_settings',
    'manage_site_settings'
)
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 1
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Grant to other roles via Roles → Edit ("Site_settings Permissions").
