-- ============================================================================
-- Customer "remember me" bearer auth tokens
-- ----------------------------------------------------------------------------
-- Replaces the cookie/PHP-session-only login for the customer portal & mobile
-- app. After login the API returns an opaque 64-char token which the client
-- keeps in localStorage and sends as `Authorization: Bearer <token>` on every
-- request. The token is stored here hashed (SHA-256) so a DB leak cannot be
-- replayed, and tokens can be revoked server-side (logout / password change).
--
-- Run this ONCE against the same MySQL database as the rest of the system
-- (bodarepensionhouse):
--     mysql -u<user> -p bodarepensionhouse < admin/sql/create_user_auth_tokens.sql
--
-- NOTE: the application also auto-creates this table on first use if the
-- database user has CREATE privilege, so running this file is optional but
-- recommended.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `user_auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'users.id of the customer account',
  `token_hash` char(64) NOT NULL COMMENT 'SHA-256 hex of the raw bearer token',
  `expires_at` datetime NOT NULL COMMENT 'UTC server time when the token expires',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token_hash` (`token_hash`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key (optional; enable if your schema uses FK constraints elsewhere):
-- ALTER TABLE `user_auth_tokens`
--   ADD CONSTRAINT `user_auth_tokens_ibfk_1`
--   FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
