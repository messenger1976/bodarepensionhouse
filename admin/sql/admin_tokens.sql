-- Tokens for admin panel: password resets and new-account activation
-- Database: boholislandtours

CREATE TABLE IF NOT EXISTS `admin_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `type` ENUM('password_reset','activation') NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_admin_type` (`admin_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
