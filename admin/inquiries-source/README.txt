# BODARE MPC — Inquiries Tool Source Bundle

Created: 2026-07-17 20:40:41
Files included: 27

## Contents

This ZIP preserves the project-relative paths used by the Inquiries tool.

### Core admin module
- application/modules/dashboard/controllers/Inquiry.php
- application/modules/dashboard/views/Inquiry/allinquiries.php
- application/modules/dashboard/views/Inquiry/view.php

### Public Contact Us flow
- application/modules/home/controllers/Home.php
- application/modules/home/views/contact/contact.php
- application/modules/home/views/index.php

### Mail / IMAP / helpers
- application/libraries/Coop_mail.php
- application/libraries/Coop_imap.php
- application/helpers/chms_helper.php
- application/config/smtp_settings.php

### Email settings UI + related
- application/modules/dashboard/controllers/Website.php
- application/modules/dashboard/views/Website/emailsettings.php
- application/modules/access/controllers/Forgot.php (account mail profile)

### Dashboard integration
- application/modules/dashboard/views/Dashboard/sidebar_nav.php
- application/modules/dashboard/views/Dashboard/footer.php
- application/modules/dashboard/views/Dashboard/footer2.php
- application/language/english/dashboard_lang.php
- js/inquiry-poll.js
- js/iniDatatables.js

### Database migrations (run in order)
1. database/inquiry.sql
2. database/email_smtp_settings.sql
3. database/email_smtp_profiles.sql
4. database/inquiry_inbound.sql
5. database/inquiry_attachments.sql

### Attachment storage guards
- files/inquiry_attachments/.htaccess
- files/inquiry_attachments/index.html

### Documentation
- docs/INQUIRIES_TOOL.md

## How to use this bundle

1. Extract into your BODARE MPC project root (or copy files into matching paths).
2. Back up the database first.
3. Run the SQL scripts in the order listed above.
4. Ensure PHP IMAP and OpenSSL extensions are enabled.
5. Make files/inquiry_attachments/ and application/cache/ writable.
6. Configure Contact Us Mailer SMTP + IMAP under Dashboard > Website > Email/SMTP Settings.
7. Read docs/INQUIRIES_TOOL.md for full setup, workflows, and troubleshooting.

## Notes

- Some files also contain non-inquiry features (Home.php, Website.php, footer.php, chms_helper.php, dashboard_lang.php, iniDatatables.js, Forgot.php). They are included because they contain required Inquiries changes.
- Do not overwrite production smtp_settings.php encryption_key without re-saving SMTP passwords.
