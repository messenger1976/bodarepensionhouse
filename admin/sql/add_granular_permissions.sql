-- Add granular permissions for bookings, users, and rooms
-- This script adds separate add, edit, and delete permissions for each module

-- Bookings permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('Add Bookings', 'add_bookings', 'Permission to create new bookings', 'bookings'),
('Edit Bookings', 'edit_bookings', 'Permission to edit existing bookings', 'bookings'),
('Delete Bookings', 'delete_bookings', 'Permission to delete bookings', 'bookings')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Users permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('Add Users', 'add_users', 'Permission to create new admin users', 'users'),
('Edit Users', 'edit_users', 'Permission to edit existing admin users', 'users'),
('Delete Users', 'delete_users', 'Permission to delete admin users', 'users')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Rooms permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('Add Rooms', 'add_rooms', 'Permission to create new rooms', 'rooms'),
('Edit Rooms', 'edit_rooms', 'Permission to edit existing rooms', 'rooms'),
('Delete Rooms', 'delete_rooms', 'Permission to delete rooms', 'rooms')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Inquiries permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Inquiries', 'view_inquiries', 'Permission to view inquiries list and details', 'inquiries'),
('Edit Inquiries', 'edit_inquiries', 'Permission to edit inquiry status', 'inquiries'),
('Delete Inquiries', 'delete_inquiries', 'Permission to delete inquiries', 'inquiries')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign all granular permissions to Super Admin role (role_id = 1)
-- This ensures Super Admin has all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` 
WHERE `slug` IN ('add_bookings', 'edit_bookings', 'delete_bookings', 'add_users', 'edit_users', 'delete_users', 'add_rooms', 'edit_rooms', 'delete_rooms', 'view_inquiries', 'edit_inquiries', 'delete_inquiries')
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` 
    WHERE `role_permissions`.`role_id` = 1 
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: You may want to assign these permissions to other roles as needed
-- For example, if you want Managers to have add/edit but not delete:
-- INSERT INTO `role_permissions` (`role_id`, `permission_id`)
-- SELECT 3, `id` FROM `permissions` 
-- WHERE `slug` IN ('add_bookings', 'edit_bookings', 'add_rooms', 'edit_rooms')
-- AND NOT EXISTS (
--     SELECT 1 FROM `role_permissions` 
--     WHERE `role_permissions`.`role_id` = 3 
--     AND `role_permissions`.`permission_id` = `permissions`.`id`
-- );

