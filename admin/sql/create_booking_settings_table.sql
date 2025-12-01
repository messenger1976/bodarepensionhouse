-- Create booking_settings table for storing booking configuration
-- Run this SQL script to create the settings table

CREATE TABLE IF NOT EXISTS `booking_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `booking_settings` (`setting_key`, `setting_value`) VALUES
('default_status', 'pending'),
('booking_number_prefix', 'BK'),
('min_booking_days', '1'),
('max_booking_days', '30'),
('check_in_time', '14:00'),
('check_out_time', '12:00'),
('cancellation_hours', '24'),
('require_payment', '0'),
('send_email_notifications', '0'),
('auto_confirm_bookings', '0'),
('tax_rate', '0'),
('service_charge', '0'),
('booking_notes', '')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

