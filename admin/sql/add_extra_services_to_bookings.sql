-- Store selected cart extra services as JSON on each booking
ALTER TABLE `bookings`
ADD COLUMN `extra_services` TEXT NULL AFTER `notes`;
