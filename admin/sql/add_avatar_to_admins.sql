-- Add avatar column to admins table
ALTER TABLE `admins` ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL AFTER `email`;

