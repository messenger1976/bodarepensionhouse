-- Add barangay field to customers table for profile address hierarchy support
ALTER TABLE `customers`
ADD COLUMN `barangay` VARCHAR(100) NULL DEFAULT NULL AFTER `city`;
