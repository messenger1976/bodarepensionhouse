-- Add available_rooms field to rooms table
-- This field stores the number of rooms available for booking
-- Used for conflict checking when multiple rooms of the same type exist

ALTER TABLE `rooms` 
ADD COLUMN `available_rooms` INT(11) NOT NULL DEFAULT 1 AFTER `capacity`;

-- Update existing rooms to have at least 1 available room
UPDATE `rooms` SET `available_rooms` = 1 WHERE `available_rooms` = 0 OR `available_rooms` IS NULL;

