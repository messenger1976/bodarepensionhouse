-- Add permissions for inquiries module
-- This script adds view, edit, and delete permissions for inquiries

-- Inquiries permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Inquiries', 'view_inquiries', 'Permission to view inquiries list and details', 'inquiries'),
('Edit Inquiries', 'edit_inquiries', 'Permission to edit inquiry status', 'inquiries'),
('Delete Inquiries', 'delete_inquiries', 'Permission to delete inquiries', 'inquiries')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign all inquiry permissions to Super Admin role (role_id = 1)
-- This ensures Super Admin has all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` 
WHERE `slug` IN ('view_inquiries', 'edit_inquiries', 'delete_inquiries')
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` 
    WHERE `role_permissions`.`role_id` = 1 
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: You may want to assign these permissions to other roles as needed

