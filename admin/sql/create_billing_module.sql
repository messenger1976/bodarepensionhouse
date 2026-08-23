-- Billing & Payment Module for BODARE Pension House
-- Run this script to create invoices, events, enhanced payments, permissions, and roles

USE bodarepensionhouse;

-- ---------------------------------------------------------------------------
-- Events table (meetings, weddings, conferences, etc.)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_number` varchar(20) DEFAULT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_type` enum('meeting','wedding','birthday','conference','banquet','other') DEFAULT 'other',
  `venue` varchar(255) DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `organizer_email` varchar(100) DEFAULT NULL,
  `organizer_phone` varchar(20) DEFAULT NULL,
  `expected_guests` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('inquiry','confirmed','completed','cancelled') DEFAULT 'inquiry',
  `booking_id` int(11) DEFAULT NULL,
  `notes` text,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_status` (`status`),
  KEY `idx_event_booking` (`booking_id`),
  KEY `idx_event_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Invoices (room billing, event charges, additional fees)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `service_charge_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','issued','partial','paid','void','overdue') DEFAULT 'draft',
  `due_date` date DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `emailed_at` datetime DEFAULT NULL,
  `notes` text,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_number` (`invoice_number`),
  KEY `idx_invoice_booking` (`booking_id`),
  KEY `idx_invoice_event` (`event_id`),
  KEY `idx_invoice_status` (`status`),
  KEY `idx_invoice_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Invoice line items (rooms, events, extra charges)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('room','event','extra_service','minibar','laundry','damage','food','other') DEFAULT 'other',
  `description` varchar(500) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `booking_item_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`),
  KEY `idx_invoice_items_type` (`item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Enhance existing payments table
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','gcash','bank_transfer') DEFAULT 'cash',
  `payment_status` enum('pending','paid','refunded','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add invoice_id and admin tracking to payments (safe if columns already exist)
SET @dbname = DATABASE();
SET @tablename = 'payments';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'invoice_id') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `invoice_id` int(11) DEFAULT NULL AFTER `booking_id`, ADD KEY `idx_payments_invoice` (`invoice_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'admin_id') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `admin_id` int(11) DEFAULT NULL AFTER `notes`, ADD KEY `idx_payments_admin` (`admin_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'reference_number') > 0,
  'SELECT 1',
  'ALTER TABLE `payments` ADD COLUMN `reference_number` varchar(100) DEFAULT NULL AFTER `transaction_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Allow payments without booking (invoice-only payments)
SET @preparedStatement = (SELECT IF(
  (SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'booking_id' LIMIT 1) = 'YES',
  'SELECT 1',
  'ALTER TABLE `payments` MODIFY COLUMN `booking_id` int(11) DEFAULT NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ---------------------------------------------------------------------------
-- Permissions
-- ---------------------------------------------------------------------------
INSERT INTO `permissions` (`name`, `slug`, `description`, `module`) VALUES
('View Invoices', 'view_invoices', 'Permission to view room billing invoices and statements', 'billing'),
('Add Invoices', 'add_invoices', 'Permission to create invoices from bookings or events', 'billing'),
('Edit Invoices', 'edit_invoices', 'Permission to edit invoices and add additional charges', 'billing'),
('Delete Invoices', 'delete_invoices', 'Permission to void or delete invoices', 'billing'),
('View Payments', 'view_payments', 'Permission to view payment records', 'billing'),
('Add Payments', 'add_payments', 'Permission to record payments against invoices or bookings', 'billing'),
('Edit Payments', 'edit_payments', 'Permission to edit payment records', 'billing'),
('Delete Payments', 'delete_payments', 'Permission to delete or refund payment records', 'billing'),
('View Events', 'view_events', 'Permission to view hotel events and function bookings', 'events'),
('Add Events', 'add_events', 'Permission to create hotel events', 'events'),
('Edit Events', 'edit_events', 'Permission to edit hotel events', 'events'),
('Delete Events', 'delete_events', 'Permission to delete or cancel hotel events', 'events')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `module` = VALUES(`module`);

-- Assign all billing permissions to Super Admin
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'super_admin'
  AND p.slug IN (
    'view_invoices', 'add_invoices', 'edit_invoices', 'delete_invoices',
    'view_payments', 'add_payments', 'edit_payments', 'delete_payments',
    'view_events', 'add_events', 'edit_events', 'delete_events'
  )
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Assign billing permissions to Admin and Manager roles (if they exist)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug IN ('admin', 'manager')
  AND p.slug IN (
    'view_invoices', 'add_invoices', 'edit_invoices',
    'view_payments', 'add_payments', 'edit_payments',
    'view_events', 'add_events', 'edit_events'
  )
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Staff: view-only billing access
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'staff'
  AND p.slug IN ('view_invoices', 'view_payments', 'view_events')
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- ---------------------------------------------------------------------------
-- New roles for billing module
-- ---------------------------------------------------------------------------
INSERT INTO `roles` (`name`, `slug`, `description`, `status`) VALUES
('Billing Manager', 'billing_manager', 'Full access to invoices, payments, and event billing', 'active'),
('Front Desk Cashier', 'front_desk_cashier', 'Record payments, manage invoices, view events', 'active'),
('Event Coordinator', 'event_coordinator', 'Manage hotel events and related billing', 'active')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `status` = VALUES(`status`);

-- Billing Manager: all billing + event permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'billing_manager'
  AND p.slug IN (
    'view_invoices', 'add_invoices', 'edit_invoices', 'delete_invoices',
    'view_payments', 'add_payments', 'edit_payments', 'delete_payments',
    'view_events', 'add_events', 'edit_events', 'delete_events'
  )
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Front Desk Cashier: invoices + payments (no delete), view events
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'front_desk_cashier'
  AND p.slug IN (
    'view_invoices', 'add_invoices', 'edit_invoices',
    'view_payments', 'add_payments', 'edit_payments',
    'view_events'
  )
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Event Coordinator: events + invoice creation
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.slug = 'event_coordinator'
  AND p.slug IN (
    'view_events', 'add_events', 'edit_events', 'delete_events',
    'view_invoices', 'add_invoices', 'edit_invoices',
    'view_payments', 'add_payments'
  )
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` rp
    WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );
