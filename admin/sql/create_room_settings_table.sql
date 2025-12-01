-- Create room_settings table for storing room configuration
-- Run this SQL script to create the settings table

CREATE TABLE IF NOT EXISTS `room_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `room_settings` (`setting_key`, `setting_value`) VALUES
('default_status', 'active'),
('default_capacity', '2'),
('max_capacity', '10'),
('price_currency', '₱'),
('price_display_format', 'per_night'),
('room_types', 'Standard
Deluxe
Executive
Suite
Dormitory'),
('amenities_list', 'WiFi
Television
Private Bathroom
Air Conditioning
Hot Water'),
('image_upload_path', 'img/rooms/'),
('max_images_per_room', '5'),
('allow_online_booking', '1'),
('show_availability_calendar', '1'),
('room_notes', '')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

