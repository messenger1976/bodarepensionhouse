-- PayMongo Setup module permissions
-- Grants access to Admin → PayMongo Setup (API keys, webhook secret, enable/disable, auto-confirm).
-- Run on the admin database after deploying the module files.
-- The PayMongo values themselves stay in `booking_settings` (see add_paymongo_settings.sql).

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View PayMongo Setup', 'view_paymongo_setup', 'Permission to open PayMongo Setup and see whether online payments are enabled and configured', 'paymongo_setup'),
('Manage PayMongo Setup', 'manage_paymongo_setup', 'Permission to change PayMongo keys, webhook secret, enable/disable and auto-confirm', 'paymongo_setup')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign both permissions to the Super Admin role (role_id = 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE `slug` IN (
    'view_paymongo_setup',
    'manage_paymongo_setup'
)
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 1
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: grant to other roles via Roles → Edit, or for example Managers (view only):
-- INSERT INTO `role_permissions` (`role_id`, `permission_id`)
-- SELECT r.id, p.id FROM `roles` r
-- CROSS JOIN `permissions` p
-- WHERE r.slug = 'manager'
-- AND p.slug IN ('view_paymongo_setup')
-- AND NOT EXISTS (
--     SELECT 1 FROM `role_permissions` rp
--     WHERE rp.role_id = r.id AND rp.permission_id = p.id
-- );
