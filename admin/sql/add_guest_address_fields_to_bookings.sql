-- Add guest address fields to bookings table
-- This migration adds address, city, province, country, and zipcode fields for guest information

ALTER TABLE `bookings`
ADD COLUMN `guest_address` TEXT NULL DEFAULT NULL AFTER `guest_phone`,
ADD COLUMN `guest_city` VARCHAR(100) NULL DEFAULT NULL AFTER `guest_address`,
ADD COLUMN `guest_province` VARCHAR(100) NULL DEFAULT NULL AFTER `guest_city`,
ADD COLUMN `guest_country` VARCHAR(100) NULL DEFAULT NULL AFTER `guest_province`,
ADD COLUMN `guest_zipcode` VARCHAR(20) NULL DEFAULT NULL AFTER `guest_country`;

-- Add index on country for potential filtering/reporting
ALTER TABLE `bookings`
ADD INDEX `idx_guest_country` (`guest_country`);

