-- Per-booking check-in / check-out times (defaults match booking_settings).
-- Run once against bodarepensionhouse.

ALTER TABLE `bookings`
  ADD COLUMN `check_in_time` TIME NULL DEFAULT '14:00:00' AFTER `check_out`,
  ADD COLUMN `check_out_time` TIME NULL DEFAULT '12:00:00' AFTER `check_in_time`;

UPDATE `bookings`
SET
  `check_in_time` = COALESCE(`check_in_time`, '14:00:00'),
  `check_out_time` = COALESCE(`check_out_time`, '12:00:00')
WHERE `check_in_time` IS NULL OR `check_out_time` IS NULL;
