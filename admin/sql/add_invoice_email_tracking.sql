-- Optional: track when invoice was last emailed to guest
USE bodarepensionhouse;

SET @dbname = DATABASE();
SET @tablename = 'invoices';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'emailed_at') > 0,
  'SELECT 1',
  'ALTER TABLE `invoices` ADD COLUMN `emailed_at` datetime DEFAULT NULL AFTER `issued_at`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
