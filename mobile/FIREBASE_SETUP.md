# Firebase push notifications — BODARE Pension House

Set up **Firebase Cloud Messaging (FCM)** for the Capacitor Android app. Guest login stays on PHP; Firebase is used only for push (Analytics optional later).

| Item | Value |
|------|-------|
| **Live site** | https://pensionhouse.bodarempc.com |
| **Firebase project** | `booking-system-b52f4` |
| **Android package name** | `com.bodarepensionhouse.app` |

The package name does **not** need to match the website domain. The live URL is configured separately in `mobile/capacitor.config.json` (`server.url`).

---

## PWA vs APK — what to test where

| Feature | PWA (browser / Add to Home Screen) | APK (Capacitor app) |
|---------|-------------------------------------|----------------------|
| Mobile UI, booking, login, cart | Yes | Yes |
| Firebase push notifications | **No** | **Yes** |
| Needs `google-services.json` | No | Yes |

Push runs only when `window.Capacitor` is present (`native-bridge.js` exits early in the browser). There is no Web Push / VAPID setup in the PWA today.

Use the **PWA** to test the website. Build and install the **APK** to test notifications.

---

## Overview

| Layer | What you configure |
|-------|-------------------|
| **Firebase Console** | Android app + Cloud Messaging |
| **Android APK** | `google-services.json` in `mobile/android/app/` |
| **PHP server** | `includes/firebase-config.php` + DB table + optional service account |
| **Live site** | Deploy PHP/JS; `BODARE_PUSH_ENABLED` follows `push_enabled` in config |

---

## Step 1 — Firebase Console

1. Open [Firebase Console](https://console.firebase.google.com/) → project **`booking-system-b52f4`**.
2. **Build → Android app → Register app**
   - Package name: **`com.bodarepensionhouse.app`** (must match exactly)
   - App nickname: `BODARE Pension House`
   - Debug signing certificate: optional for FCM testing
3. **Add Firebase SDK** screen:
   - If asked: **Android → Gradle → Groovy** (`build.gradle`, not Kotlin DSL)
   - **Do not** paste Firebase SDK Gradle snippets (BoM, Analytics, etc.) — Capacitor `@capacitor/push-notifications` already pulls in `firebase-messaging`, and the project already has the `google-services` Gradle plugin
   - **Download `google-services.json`** and continue through the wizard
4. Firebase Analytics on this step is **optional** for push.

---

## Step 2 — Add `google-services.json` locally

Copy the downloaded file to:

```
mobile/android/app/google-services.json
```

Do **not** commit this file (gitignored).

Verify `project_id` inside the JSON matches **`booking-system-b52f4`**.

---

## Step 3 — PHP server and database

### Config

```powershell
copy includes\firebase-config.example.php includes\firebase-config.php
```

Edit `includes/firebase-config.php`:

```php
'push_enabled' => true,
'project_id' => 'booking-system-b52f4',
```

Leave `push_enabled` as **`false`** until `google-services.json` is in the Android app and you have rebuilt the APK — otherwise the app can crash when the user allows notifications without a valid Firebase native config.

### Database (once)

Run on the Bodare MySQL database:

```
admin/sql/create_push_device_tokens_table.sql
```

Creates table `push_device_tokens` for FCM registration tokens from the app.

### Deploy to production

Upload to **https://pensionhouse.bodarempc.com**:

| File |
|------|
| `includes/firebase-config.php` |
| `includes/firebase.php` |
| `includes/site-head.php` |
| `native-bridge.js` |
| `api-config.js` |
| `booking-api.js` |
| `admin/application/controllers/api/Push.php` |
| `admin/application/models/Push_device_model.php` |
| `admin/application/libraries/Fcm_service.php` |
| `admin/application/config/routes.php` |

The APK loads the live site — server files must be deployed for token registration to succeed.

---

## Step 4 — Build the debug APK (Windows)

**Requirements:** Node.js, Android SDK (`ANDROID_HOME`), **JDK 21+** (Capacitor 7).

### PowerShell blocks `npm` (ExecutionPolicy error)

If you see:

```
npm.ps1 cannot be loaded because running scripts is disabled on this system
```

Use one of these:

**Option A — build script (recommended):**

```powershell
cd c:\xampp\htdocs\bodarepensionhouse
powershell -ExecutionPolicy Bypass -File mobile\build-android-debug.ps1
```

**Option B — `npm.cmd` instead of `npm`:**

```powershell
cd mobile
npm.cmd run android:build:debug
```

**Option C — Command Prompt (cmd):**

```cmd
cd c:\xampp\htdocs\bodarepensionhouse\mobile
npm run android:build:debug
```

**Option D — allow scripts for your user (one-time):**

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

Then `npm run android:build:debug` works in PowerShell.

### Output

APK path after a successful build:

```
mobile/dist/bodare-pension-house-debug.apk
```

Copy to the phone and install (enable “Install unknown apps” for your file manager if prompted).

Gradle applies the Google Services plugin automatically when `google-services.json` exists in `android/app/`.

---

## Step 5 — Test push

### A. App registers a token

1. Install the **new** APK (built **after** adding `google-services.json`).
2. Open the app → **Allow notifications** when prompted.
3. Check the database:

```sql
SELECT * FROM push_device_tokens ORDER BY id DESC LIMIT 5;
```

### B. Send a test from Firebase Console

1. Firebase → **Engage → Messaging → Create campaign**
2. Target app **`com.bodarepensionhouse.app`**, or paste an FCM token from `push_device_tokens`

### C. Notification tap behavior

Configured in `native-bridge.js`:

- `data.url` → opens that URL in the WebView
- `data.booking` → opens `customer-dashboard.php`

---

## Step 6 — Send from PHP (optional)

For booking alerts and other server-triggered push:

1. Firebase Console → **Project settings → Service accounts → Generate new private key**
2. Save JSON as:

```
admin/config/firebase-service-account.json
```

(gitignored — do not commit)

3. Ensure `service_account_json` in `firebase-config.php` points to that path.

4. Example:

```php
$this->load->library('fcm_service');
$this->fcm_service->send_to_user($user_id, 'Booking confirmed', 'Your stay is confirmed.');
// or
$this->fcm_service->send_to_token($fcm_token, 'Title', 'Body', ['url' => '/customer-dashboard.php']);
```

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| `npm.ps1 cannot be loaded` | Use `npm.cmd`, cmd, or `-ExecutionPolicy Bypass` (Step 4) |
| App **closes** when notifications are **on** | APK built without `google-services.json` — add file and rebuild |
| App works but **no token in DB** | Deploy server files; set `push_enabled => true`; run SQL migration |
| `registrationError` in device logs | Wrong package name or missing/invalid `google-services.json` |
| Server returns **503** on register | `firebase-config.php` missing on live site or `push_enabled` is false |
| Gradle `invalid source release: 21` | Install JDK 21+ (see `mobile/build-android-debug.ps1` / `mobile/.tools/jdk-21/`) |

---

## Security

- Never commit `google-services.json`, `includes/firebase-config.php`, or `admin/config/firebase-service-account.json`.
- FCM tokens live in `push_device_tokens`; linked to `user_id` when the guest is logged in.
- Do not store Firebase secrets in Company Knowledge or public repos.

---

## Quick checklist

- [ ] Firebase Android app registered (`com.bodarepensionhouse.app`, project `booking-system-b52f4`)
- [ ] `google-services.json` in `mobile/android/app/`
- [ ] APK rebuilt and installed on device
- [ ] SQL migration run (`push_device_tokens`)
- [ ] `firebase-config.php` on live site with `push_enabled => true`
- [ ] Push PHP/JS deployed to pensionhouse.bodarempc.com
- [ ] Token appears in DB after opening app
- [ ] Test notification sent from Firebase Console

---

## Related files (repo)

| Path | Role |
|------|------|
| `mobile/capacitor.config.json` | Live URL + app id |
| `native-bridge.js` | Capacitor push registration + token sync |
| `includes/firebase-config.example.php` | Config template |
| `admin/sql/create_push_device_tokens_table.sql` | DB schema |
| `admin/application/libraries/Fcm_service.php` | Server-side FCM send (HTTP v1) |
| `mobile/build-android-debug.ps1` | One-command Windows debug build |

Canonical knowledge copy: `Company-Knowledge/COMPANIES/Bodare-Pension-House/BPHKB/PLATFORM_KNOWLEDGE/13_OPERATIONS/PWA_AND_CAPACITOR.md`
