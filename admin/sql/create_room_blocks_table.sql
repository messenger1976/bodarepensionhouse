-- Room date blocks: take some or all units of a room type off sale for a date range
-- (maintenance, renovation, owner use, private hire, holidays).
--
-- start_date and end_date are the first and last NIGHT blocked (both inclusive).
-- A stay from check_in to check_out (exclusive) is affected when any of its nights
-- falls inside [start_date, end_date].
-- rooms_blocked NULL means every unit of the room type is blocked.

CREATE TABLE IF NOT EXISTS `room_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rooms_blocked` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_room_blocks_room_dates` (`room_id`, `start_date`, `end_date`),
  CONSTRAINT `fk_room_blocks_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
