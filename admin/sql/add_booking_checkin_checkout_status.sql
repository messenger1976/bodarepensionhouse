-- Add checked_in / checked_out statuses for guest arrival and departure.
-- Flow: pending → confirmed → checked_in → checked_out (closed)
-- Keep completed for backward compatibility with existing rows.

ALTER TABLE `bookings`
  MODIFY COLUMN `status` ENUM(
    'pending',
    'confirmed',
    'checked_in',
    'checked_out',
    'cancelled',
    'completed'
  ) DEFAULT 'pending';

ALTER TABLE `booking_items`
  MODIFY COLUMN `status` ENUM(
    'pending',
    'confirmed',
    'checked_in',
    'checked_out',
    'cancelled',
    'completed'
  ) NOT NULL DEFAULT 'pending';
