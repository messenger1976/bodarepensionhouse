# OTP Account Activation + localStorage "Remember Me" Auth — Deployment Notes

This change upgrades the customer site in two ways:

1. **Token-based "remember me" auth** — Customer logins no longer depend only on
   the PHP session cookie. The server now issues an opaque 64-char bearer token
   (stored hashed in the DB) which the browser/WebView keeps in
   `localStorage` (`bodare_auth_token`) and sends as
   `Authorization: Bearer <token>` on every API request. This fixes the
   "sometimes logged out on the mobile app" problem caused by the OS/WebView
   dropping or expiring the session cookie. Cookie sessions are kept as a
   fallback, so existing desktop visitors and the admin panel are unaffected.
2. **OTP account activation** — New accounts are activated with a **6-digit
   emailed code** entered on a new `verify-account.php` page (no more clickable
   email link). The code expires after **15 minutes**, allows **5 wrong
   attempts** before being locked, and can be **resent after 60 seconds**
   (resending invalidates the old code). After a successful verification the
   guest is automatically signed in and taken to the dashboard. Legacy
   activation links that were already emailed keep working.

---

## 1. Database (run ONCE on the live/production DB)

Run these two files in order with the same MySQL account the site uses:

```bash
mysql -u<user> -p bodarepensionhouse < admin/sql/create_user_auth_tokens.sql
mysql -u<user> -p bodarepensionhouse < admin/sql/add_otp_attempts_to_email_verifications.sql
```

- `create_user_auth_tokens.sql` creates the `user_auth_tokens` table.
  The application **auto-creates this table on first login** if the DB user has
  `CREATE` privilege, but running the file is recommended.
- `add_otp_attempts_to_email_verifications.sql` adds the `attempts` column used
  for the 5-attempt OTP lock-out. If it fails with *"Duplicate column name"*,
  the column already exists — that is fine.
- Without the `attempts` column the app still works (wrong codes are simply not
  locked out) until the SQL is applied.

## 2. Upload the changed files

Backend (`admin/`):
- `application/libraries/Api_auth.php` *(new)*
- `application/models/User_token_model.php` *(new)*
- `application/models/Email_verification_model.php`
- `application/controllers/api/Auth.php`
- `application/controllers/api/User.php`
- `application/controllers/api/Booking.php`
- `application/controllers/api/Invoice.php`
- `application/controllers/api/Inquiry.php`
- `application/controllers/api/Push.php`
- `application/libraries/Form_security.php`
- `application/config/routes.php`

Frontend (public site root):
- `api-config.js`
- `booking-api.js`
- `script.js`
- `login.php`
- `verify-account.php` *(new page)*

## 3. Behavioural notes

- **Login page**: a *"Keep me signed in on this device"* checkbox (on by
  default) controls token lifetime: **30 days** checked / **12 hours**
  unchecked.
- **Logout** revokes the token server-side and removes it from localStorage.
- **Password change** signs out the account on every other device.
- **Registration** → success screen → redirected to
  `verify-account.php?email=...&registered=1` where the 6-digit code is typed.
  The verify page also lets guests **resend the code** (60 s cooldown) and shows
  remaining attempts on wrong entries.
- **Login with an unverified account** shows a link to the verification page.
- The old `activate-account.php?token=...` page still works for previously
  emailed link tokens during the transition period.

## 4. Suggested post-deploy checks

1. Register a new account → confirm you land on the code page and the email
   contains a 6-digit code.
2. Enter a wrong code 5 times → code is locked and a new one can be requested.
3. Enter the correct code → account activates and the guest is auto-logged in.
4. On the phone app: log in, close the app, reopen later → still signed in
   (token persisted in localStorage).
5. `Logout` → booking/invoice/profile pages then ask for login again.
