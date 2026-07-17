-- Adds support for multiple SMTP sender profiles:
--   'contact' (id 1) -> Contact Us / inquiry emails (contactus@bodarempc.com)
--   'account' (id 2) -> Registration / password reset / system emails (account@bodarempc.com)

ALTER TABLE `email_smtp_settings`
  ADD COLUMN `profile` varchar(20) NOT NULL DEFAULT 'contact' AFTER `id`,
  ADD UNIQUE KEY `profile` (`profile`);

UPDATE `email_smtp_settings` SET `profile` = 'contact' WHERE `id` = 1;

-- Seed the account profile from the contact profile. The SMTP password is
-- copied as a placeholder; if account@ uses a different password, update it
-- in Dashboard -> Website -> Email/SMTP Settings (Account tab).
INSERT INTO `email_smtp_settings`
  (`id`, `profile`, `protocol`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`,
   `smtp_crypto`, `smtp_timeout`, `from_email`, `from_name`, `mailtype`, `charset`,
   `newline`, `crlf`, `is_active`, `created_at`, `updated_at`)
SELECT
  2, 'account', `protocol`, `smtp_host`, `smtp_port`,
  'account@bodarempc.com', `smtp_pass`, `smtp_crypto`, `smtp_timeout`,
  'account@bodarempc.com', `from_name`, `mailtype`, `charset`,
  `newline`, `crlf`, `is_active`, NOW(), NOW()
FROM `email_smtp_settings`
WHERE `id` = 1
  AND NOT EXISTS (SELECT 1 FROM (SELECT `id` FROM `email_smtp_settings` WHERE `id` = 2) AS t);
