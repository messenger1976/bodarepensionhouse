-- Payment method detail columns (card last4/exp, bank transfer fields)
-- Run once in phpMyAdmin / MySQL.
-- Safe to re-run: only adds missing columns.

USE bodarepensionhouse;

SET @dbname = DATABASE();
SET @tablename = 'payments';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'card_last4') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `card_last4` varchar(4) DEFAULT NULL AFTER `reference_number`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'card_exp') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `card_exp` varchar(7) DEFAULT NULL AFTER `card_last4`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'bank_name') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `bank_name` varchar(100) DEFAULT NULL AFTER `card_exp`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'bank_account_name') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `bank_account_name` varchar(150) DEFAULT NULL AFTER `bank_name`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'bank_account_number') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `bank_account_number` varchar(50) DEFAULT NULL AFTER `bank_account_name`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'bank_transfer_date') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `bank_transfer_date` date DEFAULT NULL AFTER `bank_account_number`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
