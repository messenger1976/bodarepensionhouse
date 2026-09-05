-- System Activity Logs / Audit Trail
-- Central table that records every meaningful activity across the main website,
-- the admin panel, API calls, and background/system events.
--
-- Run this after the main schema. Applied via phpMyAdmin / mysql CLI.
--
-- The table is write-optimized and append-only. Never UPDATE or DELETE rows here
-- through normal application flow -- use the retention purger for cleanup.

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_type` varchar(30) NOT NULL DEFAULT 'system',
  `module` varchar(60) NOT NULL DEFAULT '',
  `action` varchar(60) NOT NULL DEFAULT '',
  `description` text,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_type` varchar(20) NOT NULL DEFAULT 'guest',
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT 'GET',
  `request_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `old_values` text,
  `new_values` text,
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `severity` varchar(20) NOT NULL DEFAULT 'info',
  `metadata` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`),
  KEY `idx_actor` (`actor_type`, `actor_id`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_status` (`status`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- log_type values (documented in docs/ACTIVITY_LOGS.md):
--   page_view, auth, crud, api, system, security
-- actor_type values:
--   admin, customer, guest, system, api, vendor
-- status values:
--   success, failed
-- severity values:
--   info, warning, critical
