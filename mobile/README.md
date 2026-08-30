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

## Firebase (FCM + Analytics)

Keep guest login on PHP. Use Firebase only for Cloud Messaging and Analytics.

1. Create an Android/iOS app in Firebase for `com.bodarepensionhouse.app`.
2. Download `google-services.json` into `android/app/` (do not commit secrets).
3. Download `GoogleService-Info.plist` into the iOS app target (do not commit secrets).
4. Enable Cloud Messaging. Optional: add Firebase Analytics SDK in the native project.
5. The live site already loads `native-bridge.js`, which registers for push when running inside Capacitor.

## Notes

- Prebuilt debug APK: `dist/bodare-pension-house-debug.apk` (rebuild with `npm run android:build:debug`).
- PHP sessions use cookies; keep `androidScheme: https` so cookies work against production.
- AdSense units are hidden when `html.is-capacitor` is set.
- Do not put API keys or guest PII in this folder.
