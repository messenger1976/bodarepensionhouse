-- Activity Logs module permissions
-- Grants access to the System Activity Logs screens in the admin panel.
-- Run after create_activity_logs.sql.

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Activity Logs', 'view_activity_logs', 'Permission to view and filter the system activity logs', 'activity_logs'),
('Export Activity Logs', 'export_activity_logs', 'Permission to export the activity log list to CSV', 'activity_logs'),
('Delete Activity Logs', 'delete_activity_logs', 'Permission to purge or clear activity log entries', 'activity_logs')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign activity log permissions to the Super Admin role (role_id = 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE `slug` IN ('view_activity_logs', 'export_activity_logs', 'delete_activity_logs')
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 1
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: grant to other roles as needed, for example Managers (read-only):
-- INSERT INTO `role_permissions` (`role_id`, `permission_id`)
-- SELECT 3, `id` FROM `permissions`
-- WHERE `slug` IN ('view_activity_logs', 'export_activity_logs')
-- AND NOT EXISTS (
--     SELECT 1 FROM `role_permissions`
--     WHERE `role_permissions`.`role_id` = 3
--     AND `role_permissions`.`permission_id` = `permissions`.`id`
-- );
