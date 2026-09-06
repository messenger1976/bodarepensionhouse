-- Database Backup module permissions
-- Grants access to Admin → Backup Database tool in the admin panel.
-- Run on the admin database after deploying the module files.

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Database Backup', 'view_database_backup', 'Permission to open the backup tool and download .sql files', 'database_backup'),
('Create Database Backup', 'create_database_backup', 'Permission to generate a new .sql database backup', 'database_backup'),
('Upload Database Backup', 'upload_database_backup', 'Permission to upload a .sql file into the backup vault', 'database_backup'),
('Delete Database Backup', 'delete_database_backup', 'Permission to delete a backup file from the vault', 'database_backup'),
('Restore Database Backup', 'restore_database_backup', 'Permission to restore the live database from a .sql backup (destructive)', 'database_backup')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Assign all backup permissions to the Super Admin role (role_id = 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE `slug` IN (
    'view_database_backup',
    'create_database_backup',
    'upload_database_backup',
    'delete_database_backup',
    'restore_database_backup'
)
AND NOT EXISTS (
    SELECT 1 FROM `role_permissions`
    WHERE `role_permissions`.`role_id` = 1
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);

-- Note: grant to other roles via Roles → Edit, or for example Managers (view/create only):
-- INSERT INTO `role_permissions` (`role_id`, `permission_id`)
-- SELECT r.id, p.id FROM `roles` r
-- CROSS JOIN `permissions` p
-- WHERE r.slug = 'manager'
-- AND p.slug IN ('view_database_backup', 'create_database_backup')
-- AND NOT EXISTS (
--     SELECT 1 FROM `role_permissions` rp
--     WHERE rp.role_id = r.id AND rp.permission_id = p.id
-- );
