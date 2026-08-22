-- Add extra bed price setting to room_settings
INSERT INTO `room_settings` (`setting_key`, `setting_value`) VALUES
('extra_bed_price', '199')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
