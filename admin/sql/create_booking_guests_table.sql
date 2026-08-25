-- Guest names list per booking (one booking → many named guests)
-- Run once against the bodarepensionhouse database

CREATE TABLE IF NOT EXISTS `booking_guests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `age` INT(11) DEFAULT NULL,
    `gender` ENUM('Male','Female','Other') DEFAULT NULL,
    `date_of_birth` DATE DEFAULT NULL,
    `contact_no` VARCHAR(50) DEFAULT NULL,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_booking_id` (`booking_id`),
    CONSTRAINT `fk_booking_guests_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
