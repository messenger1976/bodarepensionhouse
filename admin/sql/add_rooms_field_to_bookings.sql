-- Add rooms field to bookings table
-- This field stores the number of rooms booked in each booking
-- Used for conflict checking when multiple rooms of the same type exist

ALTER TABLE `bookings` 
ADD COLUMN `rooms` INT(11) NOT NULL DEFAULT 1 AFTER `guests`;

-- Update existing bookings to have at least 1 room
UPDATE `bookings` SET `rooms` = 1 WHERE `rooms` = 0 OR `rooms` IS NULL;

