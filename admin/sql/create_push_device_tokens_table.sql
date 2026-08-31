-- FCM device tokens for Capacitor mobile app push notifications.
-- Run once on the Bodare database.

CREATE TABLE IF NOT EXISTS `push_device_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `fcm_token` varchar(512) NOT NULL,
  `platform` enum('android','ios','web') NOT NULL DEFAULT 'android',
  `device_label` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fcm_token` (`fcm_token`(191)),
  KEY `idx_user_active` (`user_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
