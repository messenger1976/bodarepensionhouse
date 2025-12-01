-- Create booking_items table to store individual room bookings
-- This allows one booking to have multiple rooms, each tracked separately

CREATE TABLE IF NOT EXISTS `booking_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) NOT NULL,
    `room_id` INT(11) NOT NULL,
    `room_name` VARCHAR(255) NOT NULL,
    `check_in` DATE NOT NULL,
    `check_out` DATE NOT NULL,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `nights` INT(11) NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_room_id` (`room_id`),
    INDEX `idx_dates` (`check_in`, `check_out`),
    CONSTRAINT `fk_booking_items_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_booking_items_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

