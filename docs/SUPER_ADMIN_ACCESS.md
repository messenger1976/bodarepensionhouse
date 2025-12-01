# Super Admin Access Guide

## Quick Access

### Default Login Credentials
- **URL**: `http://localhost/bodarepensionhouse/admin/login`
- **Username**: `admin`
- **Password**: `admin123` (or `admin23` - check your database)

⚠️ **Important**: Change the default password immediately after first login!

---

## Setting Up Super Admin Access

### Step 1: Ensure Admin User Exists

If you don't have an admin user yet, use the password reset script:

1. Go to: `http://localhost/bodarepensionhouse/admin/reset_admin_password.php`
2. Click "Reset Admin Password"
3. This will create/update the admin user with password `admin123`

**OR** create manually via SQL:
```sql
INSERT INTO `admins` (`username`, `password`, `name`, `email`, `status`) 
VALUES ('admin', '$2y$10$...', 'Administrator', 'admin@bodarepensionhouse.com', 'active');
```

### Step 2: Assign Admin to Administrators Group

The admin user needs to be assigned to the "Administrators" group:

```sql
-- Check if admin user exists and get ID
SELECT id, username FROM `admins` WHERE `username` = 'admin';

-- Assign admin (ID 1) to Administrators group (ID 1)
INSERT INTO `admin_user_groups` (`admin_id`, `group_id`) 
VALUES (1, 1)
ON DUPLICATE KEY UPDATE `admin_id` = `admin_id`;
```

### Step 3: Verify Super Admin Role Setup

Ensure the "Administrators" group has the "Super Admin" role:

```sql
-- Check if Administrators group has Super Admin role
SELECT ug.name as group_name, r.name as role_name
FROM `user_groups` ug
JOIN `group_roles` gr ON gr.group_id = ug.id
JOIN `roles` r ON r.id = gr.role_id
WHERE ug.name = 'Administrators';

-- If not assigned, assign Super Admin role (role_id = 1) to Administrators group (group_id = 1)
INSERT INTO `group_roles` (`group_id`, `role_id`) 
VALUES (1, 1)
ON DUPLICATE KEY UPDATE `group_id` = `group_id`;
```

### Step 4: Verify Super Admin Has All Permissions

Ensure the Super Admin role has all permissions:

```sql
-- Check permissions for Super Admin role
SELECT COUNT(*) as permission_count
FROM `role_permissions`
WHERE `role_id` = 1;

-- If permissions are missing, assign all permissions to Super Admin
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE NOT EXISTS (
    SELECT 1 FROM `role_permissions` 
    WHERE `role_permissions`.`role_id` = 1 
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);
```

---

## Complete Setup Script

Run this SQL script to set up everything at once:

```sql
-- 1. Ensure admin user exists (update if exists, create if not)
INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `status`) 
VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@bodarepensionhouse.com', 'active')
ON DUPLICATE KEY UPDATE 
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `status` = 'active';

-- Note: The password hash above is for 'admin123'
-- To generate a new hash, use: SELECT PASSWORD('your_password');

-- 2. Assign admin to Administrators group
INSERT INTO `admin_user_groups` (`admin_id`, `group_id`) 
VALUES (1, 1)
ON DUPLICATE KEY UPDATE `admin_id` = `admin_id`;

-- 3. Assign Super Admin role to Administrators group
INSERT INTO `group_roles` (`group_id`, `role_id`) 
VALUES (1, 1)
ON DUPLICATE KEY UPDATE `group_id` = `group_id`;

-- 4. Assign all permissions to Super Admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`
WHERE NOT EXISTS (
    SELECT 1 FROM `role_permissions` 
    WHERE `role_permissions`.`role_id` = 1 
    AND `role_permissions`.`permission_id` = `permissions`.`id`
);
```

---

## Verify Super Admin Access

After setup, verify access:

1. **Login**: Go to `http://localhost/bodarepensionhouse/admin/login`
2. **Check Permissions**: After login, you should be able to:
   - Access Dashboard
   - View/Add/Edit/Delete Bookings
   - View/Add/Edit/Delete Rooms
   - View/Add/Edit/Delete Users
   - View/Add/Edit/Delete Groups
   - View/Add/Edit/Delete Roles

3. **Check User Details**: Go to Users → View your admin user
   - Should show "Administrators" group
   - Should show "Super Admin" role
   - Should show all permissions

---

## Troubleshooting

### Can't Login
- **Check if admin user exists**: 
  ```sql
  SELECT * FROM `admins` WHERE `username` = 'admin';
  ```
- **Reset password**: Use `reset_admin_password.php` script
- **Check status**: Ensure user status is 'active'

### Login Works But No Permissions
- **Check group assignment**:
  ```sql
  SELECT * FROM `admin_user_groups` WHERE `admin_id` = 1;
  ```
- **Check role assignment**:
  ```sql
  SELECT r.name FROM `roles` r
  JOIN `group_roles` gr ON gr.role_id = r.id
  JOIN `admin_user_groups` aug ON aug.group_id = gr.group_id
  WHERE aug.admin_id = 1;
  ```
- **Check permissions**:
  ```sql
  SELECT p.name FROM `permissions` p
  JOIN `role_permissions` rp ON rp.permission_id = p.id
  JOIN `roles` r ON r.id = rp.role_id
  JOIN `group_roles` gr ON gr.role_id = r.id
  JOIN `admin_user_groups` aug ON aug.group_id = gr.group_id
  WHERE aug.admin_id = 1;
  ```

### Missing Permissions
- Run the granular permissions SQL script: `admin/add_granular_permissions.sql`
- Ensure Super Admin role has all permissions assigned

---

## Security Recommendations

1. **Change Default Password**: Immediately after first login
2. **Delete Reset Script**: Remove `reset_admin_password.php` after setup
3. **Use Strong Passwords**: Minimum 12 characters with mixed case, numbers, and symbols
4. **Limit Super Admin Users**: Only assign Super Admin role to trusted administrators
5. **Regular Audits**: Periodically review user permissions and group assignments

---

## Quick Reference

| Item | Value |
|------|-------|
| Login URL | `/admin/login` |
| Default Username | `admin` |
| Default Password | `admin123` |
| Administrators Group ID | `1` |
| Super Admin Role ID | `1` |
| Reset Script | `/admin/reset_admin_password.php` |

