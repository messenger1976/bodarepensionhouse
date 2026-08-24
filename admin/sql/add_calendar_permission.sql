-- Calendar module permission
-- Adds view_calendar and assigns it to ALL roles by default.
-- Staff can still revoke access per role under Roles → Permissions.

INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Calendar', 'view_calendar', 'Permission to view the unified hotel calendar (room bookings and events)', 'calendar')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `module` = VALUES(`module`);

-- Default: grant View Calendar to every role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE p.slug = 'view_calendar'
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );
