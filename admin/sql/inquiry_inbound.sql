-- Inbound email reply support for the Inquiries tool.

ALTER TABLE `inquiry_reply`
  ADD COLUMN `direction` varchar(10) NOT NULL DEFAULT 'outbound' AFTER `userid`,
  ADD COLUMN `sender_email` varchar(255) DEFAULT NULL AFTER `direction`,
  ADD COLUMN `sender_name` varchar(150) DEFAULT NULL AFTER `sender_email`,
  ADD COLUMN `imap_uid` varchar(64) DEFAULT NULL AFTER `email_sent`,
  ADD COLUMN `message_id` varchar(255) DEFAULT NULL AFTER `imap_uid`,
  ADD UNIQUE KEY `imap_uid` (`imap_uid`),
  ADD KEY `direction` (`direction`);

ALTER TABLE `email_smtp_settings`
  ADD COLUMN `imap_host` varchar(255) DEFAULT NULL AFTER `is_active`,
  ADD COLUMN `imap_port` smallint(5) unsigned NOT NULL DEFAULT 993 AFTER `imap_host`,
  ADD COLUMN `imap_crypto` varchar(10) NOT NULL DEFAULT 'ssl' AFTER `imap_port`,
  ADD COLUMN `imap_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0 AFTER `imap_crypto`;

-- Default IMAP host to the same mail server as SMTP for the contact profile.
UPDATE `email_smtp_settings`
SET `imap_host` = `smtp_host`,
    `imap_port` = 993,
    `imap_crypto` = 'ssl',
    `imap_enabled` = 1
WHERE `profile` = 'contact';
