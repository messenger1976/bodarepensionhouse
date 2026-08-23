-- Auto-invoice settings for confirmed bookings
USE bodarepensionhouse;

INSERT INTO `booking_settings` (`setting_key`, `setting_value`) VALUES
('auto_create_invoice', '0'),
('auto_issue_invoice', '1'),
('auto_email_invoice', '0')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
