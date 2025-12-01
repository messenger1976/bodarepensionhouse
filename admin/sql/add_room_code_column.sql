-- Add room_code column to rooms table
-- This migration adds the room_code field to link rooms in rooms.php
-- Run this SQL script to add the room_code column

USE bodarepensionhouse;

-- Check if column exists before adding (MySQL 5.7+ compatible)
SET @dbname = DATABASE();
SET @tablename = 'rooms';
SET @columnname = 'room_code';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` VARCHAR(50) NULL AFTER `room_type`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index on room_code for faster lookups (if it doesn't exist)
SET @indexname = 'idx_room_code';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`', @columnname, '`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing rooms with room_code based on room_name (if room_code is NULL)
-- This is a one-time migration for existing data
UPDATE `rooms` 
SET `room_code` = LOWER(REPLACE(REPLACE(REPLACE(`room_name`, ' ', ''), '-', ''), 'Room', ''))
WHERE `room_code` IS NULL OR `room_code` = '';

-- Make room_code required for new entries (set default for existing NULL values first)
-- For existing NULL values, set a default based on ID
UPDATE `rooms`
SET `room_code` = CONCAT('room_', `id`)
WHERE `room_code` IS NULL OR `room_code` = '';

-- Optional: Make room_code unique (uncomment if you want unique room codes)
-- ALTER TABLE `rooms`
-- ADD UNIQUE KEY `unique_room_code` (`room_code`);

