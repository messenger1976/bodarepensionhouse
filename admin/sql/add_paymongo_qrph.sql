-- PayMongo QRPH payment support
-- Run once in phpMyAdmin / MySQL.

USE bodarepensionhouse;

-- Allow qrph as a payment method (safe if already present)
ALTER TABLE `payments`
  MODIFY COLUMN `payment_method` enum('cash','card','gcash','bank_transfer','qrph') DEFAULT 'cash';

-- Optional lookup columns for QRPH intents
SET @dbname = DATABASE();
SET @tablename = 'payments';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'paymongo_intent_id') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `paymongo_intent_id` varchar(100) DEFAULT NULL AFTER `transaction_id`, ADD KEY `idx_payments_paymongo_intent` (`paymongo_intent_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'paymongo_client_key') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `paymongo_client_key` varchar(191) DEFAULT NULL AFTER `paymongo_intent_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'qrph_expires_at') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `qrph_expires_at` datetime DEFAULT NULL AFTER `paymongo_client_key`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

INSERT INTO `booking_settings` (`setting_key`, `setting_value`) VALUES
('paymongo_enabled', '0'),
('paymongo_secret_key', ''),
('paymongo_public_key', ''),
('paymongo_webhook_secret', ''),
('paymongo_confirm_on_paid', '1')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
