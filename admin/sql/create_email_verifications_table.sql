-- Email Verifications Table
-- Stores account activation tokens sent after customer registration

USE bodarepensionhouse;

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: mark existing active logins as already verified
-- UPDATE users SET email_verified = 1 WHERE status = 'active' AND (email_verified IS NULL OR email_verified = 0);
