# BODARE Pension House — Capacitor app

Native shell that loads the live PHP website at https://pensionhouse.bodarempc.com. Bookings, login, cart, and PayMongo stay on the server.

## First-time setup

```bash
cd mobile
npm install
npx cap add android
npx cap add ios
npx cap sync
```

Open the native project:

```bash
npx cap open android
npx cap open ios
```

## Firebase push notifications

**Full guide:** [FIREBASE_SETUP.md](./FIREBASE_SETUP.md)

Quick checklist:

1. Firebase project **`booking-system-b52f4`** → Android app **`com.bodarepensionhouse.app`**
2. Download `google-services.json` → `android/app/` (gitignored)
3. Copy `includes/firebase-config.example.php` → `includes/firebase-config.php`, set `push_enabled => true`
4. Run `admin/sql/create_push_device_tokens_table.sql` on the database
5. Rebuild APK (see below)
6. Deploy PHP/JS to https://pensionhouse.bodarempc.com

Push is **APK only** — the PWA cannot test FCM with the current setup.

Optional: service account JSON in `admin/config/` for sending from PHP (`Fcm_service`).

## Build debug APK (Windows)

**Recommended** (handles JDK 21 + sync + copy to `dist/`):

```powershell
powershell -ExecutionPolicy Bypass -File mobile\build-android-debug.ps1
```

If PowerShell blocks `npm`:

```powershell
cd mobile
npm.cmd run android:build:debug
```

Output: `dist/bodare-pension-house-debug.apk`

Requirements: Node.js, Android SDK (`ANDROID_HOME`), **JDK 21+**.

## Notes

- Prebuilt debug APK may be at `dist/bodare-pension-house-debug.apk` (rebuild after Firebase changes).
- PHP sessions use cookies; keep `androidScheme: https` so cookies work against production.
- AdSense units are hidden when `html.is-capacitor` is set.
- Do not put API keys or guest PII in this folder.
