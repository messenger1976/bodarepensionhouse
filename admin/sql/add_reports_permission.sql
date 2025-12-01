-- Add permission for Reports module
-- This script adds view_reports permission to control access to reports
-- 
-- Reports included:
-- - Daily Sales Report: View daily sales data, booking statistics, revenue totals, 
--   and sales breakdown by room type for a specific date

-- Reports permission
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Reports', 'view_reports', 'Permission to view and access reports (Daily Sales Report)', 'reports')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign view_reports permission to Super Admin role (role_id = 1)
-- This ensures Super Admin has access to reports
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` 
WHERE `slug` = 'view_reports'
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` 
    WHERE `role_permissions`.`role_id` = 1 
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: You may want to assign this permission to other roles as needed
-- For example, if you want Managers to have access to reports:
-- INSERT INTO `role_permissions` (`role_id`, `permission_id`)
-- SELECT 3, `id` FROM `permissions` 
-- WHERE `slug` = 'view_reports'
-- AND NOT EXISTS (
--     SELECT 1 FROM `role_permissions` 
--     WHERE `role_permissions`.`role_id` = 3 
--     AND `role_permissions`.`permission_id` = `permissions`.`id`
-- );

