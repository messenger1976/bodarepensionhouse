-- Store guest count per booking item (each room line)
-- Needed so multi-room bookings keep accurate per-room guests from the frontend cart

ALTER TABLE `booking_items`
ADD COLUMN `guests` INT(11) NOT NULL DEFAULT 1 AFTER `nights`;

-- Backfill from parent booking when possible
UPDATE `booking_items` bi
INNER JOIN `bookings` b ON b.id = bi.booking_id
SET bi.guests = GREATEST(COALESCE(b.guests, 1), 1)
WHERE bi.guests IS NULL OR bi.guests < 1;
