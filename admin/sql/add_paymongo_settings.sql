-- PayMongo online payment settings (GCash via Hosted Checkout)
-- Run once in phpMyAdmin / MySQL.

USE bodarepensionhouse;

INSERT INTO `booking_settings` (`setting_key`, `setting_value`) VALUES
('paymongo_enabled', '0'),
('paymongo_secret_key', ''),
('paymongo_public_key', ''),
('paymongo_webhook_secret', ''),
('paymongo_confirm_on_paid', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
