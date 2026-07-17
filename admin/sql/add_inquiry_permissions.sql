-- Fix Inquiries role permissions
-- Ensures inquiry access matches the intended roles:
-- Super Admin, Admin, Manager, Staff

-- Keep permission definitions current
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Inquiries', 'view_inquiries', 'Permission to view the inquiries inbox, conversation thread, and download attachments', 'inquiries'),
('Edit Inquiries', 'edit_inquiries', 'Permission to reply, update status, and check email replies', 'inquiries'),
('Delete Inquiries', 'delete_inquiries', 'Permission to permanently delete inquiries and their attachments', 'inquiries')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `module` = VALUES(`module`);

-- Assign all inquiry permissions to Super Admin, Admin, Manager, and Staff
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug IN ('super_admin', 'admin', 'manager', 'staff')
  AND p.slug IN ('view_inquiries', 'edit_inquiries', 'delete_inquiries')
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id
      AND rp.permission_id = p.id
  );
