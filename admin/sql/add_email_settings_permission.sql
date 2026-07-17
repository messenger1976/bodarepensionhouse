-- Add permission for Email/SMTP Settings
-- Appears under Roles so it can be assigned to any role

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('Manage Email/SMTP Settings', 'manage_email_settings', 'Permission to view and update Contact Us and Account email/SMTP/IMAP settings', 'email_settings')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`), `module` = VALUES(`module`);

-- Assign to Super Admin (role_id = 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE `slug` = 'manage_email_settings'
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 1
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Assign to Admin (role_id = 2)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`
WHERE `slug` = 'manage_email_settings'
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 2
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);
