# System Activity Logs / Audit Trail

A central activity log that records **every meaningful action** across the
public website, the admin panel, the API, and background/system events. It is
an append-only audit trail (never edited through normal app flow) backed by a
single `activity_logs` table shared by both the main site (`bodarepensionhouse`
DB) and the CodeIgniter admin panel.

---

## 1. Schema

Apply `admin/sql/create_activity_logs.sql` (creates the table). Apply
`admin/sql/add_activity_logs_permission.sql` to grant the admin panel the
permissions needed to view/export/delete logs (granted to Super Admin by default).

Columns:

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | auto increment |
| `log_type` | varchar(30) | `page_view`, `auth`, `crud`, `api`, `system`, `security` |
| `module` | varchar(60) | logical module, e.g. `bookings`, `users`, `rooms`, `inquiries`, `auth`, `payments`, `website` |
| `action` | varchar(60) | verb, e.g. `create`, `update`, `delete`, `login`, `failed_login`, `status_change`, `export` |
| `description` | text | human-readable summary |
| `entity_type` | varchar(60) | affected record type, e.g. `booking`, `room`, `customer` |
| `entity_id` | bigint | PK of the affected record |
| `actor_type` | varchar(20) | `admin`, `customer`, `guest`, `api`, `system`, `vendor` |
| `actor_id` | bigint | admin/user id |
| `actor_name` | varchar(150) | snapshot of the actor's display name (survives deletion) |
| `ip_address`, `user_agent`, `request_method`, `request_url`, `referrer` | | request context |
| `old_values` | text | JSON snapshot **before** the change |
| `new_values` | text | JSON snapshot **after** the change |
| `status` | varchar(20) | `success`, `failed` |
| `severity` | varchar(20) | `info`, `warning`, `critical` |
| `metadata` | text | free-form JSON (e.g. `{ "password_changed": true }`) |
| `created_at` | datetime | `DEFAULT CURRENT_TIMESTAMP` |

Indexes exist on `log_type`, `module`, `action`, `actor`, `entity`, `status`,
`severity` and `created_at` to keep listing/filtering fast as the table grows.

> **Security note:** sensitive values (passwords, PayMongo secret keys) are
> stripped or redacted before snapshots are stored. Never log plaintext
> credentials.

---

## 2. Admin panel: `Activity_log` library

File: `admin/application/libraries/Activity_log.php` — a best-effort writer
that never throws (a logging failure can't break the request).

```php
// Generic row
$this->activity_log->log('crud', 'bookings', 'create', 'Booking #BK000001 created', [
    'entity_type' => 'booking', 'entity_id' => 42, 'old' => $before, 'new' => $after,
]);

// Page view (auto-called - see §4)
$this->activity_log->page_view();

// CRUD with before/after snapshots
$this->activity_log->crud('users', 'update', 'admin_user', 7, 'User updated', $old, $new);

// Auth event (login/failed_login/logout/register/reset/activate/forgot_password)
$this->activity_log->auth_event('failed_login', 'Invalid credentials for admin', [
    'status' => 'failed', 'severity' => 'warning',
]);
```

Maintenance:

```php
$this->activity_log->purge_old(); // delete rows older than the retention window
$this->activity_log->clear($beforeDate); // delete rows before a date (or all if null)
```

Config: `admin/application/config/activity_log.php`

```php
$config['activity_log'] = [
    'enabled' => TRUE,
    'log_admin_page_views' => TRUE,
    'log_public_page_views' => TRUE,
    'ignore_paths' => 'assets/|css/|js/|...',
    'retention_days' => 90,      // NULL/0 keeps everything
    'max_description_length' => 1000,
    'max_url_length' => 500,
    'max_user_agent_length' => 255,
];
```

---

## 3. Main website helper

File: `includes/activity-log.php` (loaded by `includes/site-head.php`). It uses
the same `activity_logs` table via the site's mysqli connection and is silent
on failure.

```php
bodare_activity_log('crud', 'contact', 'create', 'Inquiry submitted', [
    'entity_type' => 'inquiry', 'entity_id' => 12,
]);

bodare_log_page_view(); // auto page-view (guests/anonymous), skips bots & assets
```

To disable public-site logging set the constant before any output:

```php
define('BODARE_ACTIVITY_LOG_ENABLED', false);
```

---

## 4. Automatic capture

| Where | What | Filtering |
|---|---|---|
| `Admin_Controller::__construct()` | every authenticated admin **page view** (GET) | ignores `ignore_paths`; skips `login`/`logout` |
| `includes/site-head.php` | every public **page view** | skips crawlers + asset URLs |
| `admin/Auth.php` | login, failed_login, logout, forgot_password, reset_password, register, activate | — |
| `api/Auth.php` | customer register/login/failed_login/logout/forgot/reset/activate | — |
| Admin CRUD controllers | `Users`, `Roles`, `Groups`, `Rooms`, `Bookings`, `Customers`, `Inquiries`, `Invoices`, `Payments`, `Events`, `Profile`, settings | before/after snapshots |
| API controllers | `Booking` (create/cancel), `Inquiry` (submit), `Payment` (webhook), `User` (profile/password) | — |

---

## 5. Admin panel UI

**Activity Logs** (sidebar, gated by `view_activity_logs`):

- **List / filter** — filter by type, module, actor, status, severity, date
  range, free-text search (description, module, action, actor, IP, URL).
- **View** — inspect a single log with old/new value snapshots (JSON) and
  metadata.
- **Export CSV** (requires `export_activity_logs`) — downloads the current
  filtered result set.
- **Clear Logs** (requires `delete_activity_logs`) — purge entries older than a
  date, or everything (confirm dialog).

The dashboard shows a **Recent Activity** widget with a 7-day summary by log
type.

Routes: `activity_logs`, `activity_logs/view/(:num)`, `activity_logs/export`,
`activity_logs/clear`.

---

## 6. Permissions

| Slug | Allows |
|---|---|
| `view_activity_logs` | view the log list + detail |
| `export_activity_logs` | download CSV |
| `delete_activity_logs` | clear/purge rows |

Granted to Super Admin (role_id = 1) by the seed SQL. Grant to other roles as
needed (e.g. managers read-only).

---

## 7. Retention & cron

Set `retention_days` (default 90). Purge entries with the protected cron
endpoint (requires `cron_secret` in `includes/firebase-config.php`):

```
GET /admin/index.php/cron/purge_activity_logs?key=YOUR_SECRET
```

This calls `Activity_log::purge_old()` and returns the number of rows deleted.
Schedule it daily (e.g. cron) so the table doesn't grow without bound.

---

## 8. Adding your own events

1. Load the library (admin/API controllers already load it):
   `$this->load->library('activity_log');`
2. Call the appropriate method, e.g.
   `$this->activity_log->crud('module', 'action', 'entity_type', $entity_id, 'Description', $old, $new);`
3. For the public site use `bodare_activity_log($type, $module, $action, $description, $opts);`

See `admin/application/models/Activity_log_model.php` for query helpers used by
the UI.
