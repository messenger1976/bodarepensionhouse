/**
 * Browser / installed-PWA push notifications (Firebase Cloud Messaging, web).
 * The Capacitor APK uses native push in native-bridge.js instead, so this file
 * does nothing inside the app.
 *
 * Config comes from window.BODARE_FIREBASE_WEB (includes/site-head.php); null = off.
 * Tokens are saved through api/push/register with platform "web", so the server
 * sends booking / invoice notifications to browsers exactly like it does to phones.
 */
(function () {
    const TOKEN_KEY = 'bodare_web_push_token';
    const SDK_BASE = 'https://www.gstatic.com/firebasejs/10.12.2/';

    const config = window.BODARE_FIREBASE_WEB;
    const inApp = !!window.Capacitor;
    const supported = 'serviceWorker' in navigator
        && 'PushManager' in window
        && 'Notification' in window
        && window.isSecureContext;

    function isIos() {
        return /iphone|ipad|ipod/i.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    }

    function isLoggedIn() {
        try {
            return !!localStorage.getItem('user');
        } catch (e) {
            return false;
        }
    }

    function storedToken() {
        try {
            return localStorage.getItem(TOKEN_KEY) || '';
        } catch (e) {
            return '';
        }
    }

    function storeToken(token) {
        try {
            if (token) {
                localStorage.setItem(TOKEN_KEY, token);
            } else {
                localStorage.removeItem(TOKEN_KEY);
            }
        } catch (e) {}
    }

    async function serviceWorkerRegistration() {
        const basePath = (typeof window.BODARE_BASE_PATH === 'string' && window.BODARE_BASE_PATH)
            ? window.BODARE_BASE_PATH
            : '/';
        const scope = basePath.endsWith('/') ? basePath : basePath + '/';
        // Same URL + scope as script.js, so this returns the existing registration.
        await navigator.serviceWorker.register(scope + 'sw.js', { scope });
        return navigator.serviceWorker.ready;
    }

    let messagingPromise = null;
    function loadMessaging() {
        if (!messagingPromise) {
            messagingPromise = (async () => {
                const appSdk = await import(SDK_BASE + 'firebase-app.js');
                const msgSdk = await import(SDK_BASE + 'firebase-messaging.js');
                const app = appSdk.initializeApp({
                    apiKey: config.apiKey,
                    appId: config.appId,
                    messagingSenderId: config.messagingSenderId,
                    projectId: config.projectId,
                }, 'bodare-web-push');
                return { sdk: msgSdk, messaging: msgSdk.getMessaging(app) };
            })();
            messagingPromise.catch(() => { messagingPromise = null; });
        }
        return messagingPromise;
    }

    function waitForApi(attempt = 0) {
        if (typeof API !== 'undefined' && API.push) {
            return Promise.resolve(true);
        }
        if (attempt >= 20) {
            return Promise.resolve(false);
        }
        return new Promise((resolve) => setTimeout(resolve, 250))
            .then(() => waitForApi(attempt + 1));
    }

    async function getAndSaveToken(fresh = false) {
        const registration = await serviceWorkerRegistration();
        if (fresh) {
            // Firebase returns its cached token while the push subscription is unchanged,
            // even if FCM has already invalidated it (server log: NotRegistered).
            // Dropping the subscription makes getToken() mint a new one.
            try {
                const subscription = await registration.pushManager.getSubscription();
                if (subscription) {
                    await subscription.unsubscribe();
                }
            } catch (e) {
                console.log('Web push unsubscribe failed', e);
            }
        }
        const { sdk, messaging } = await loadMessaging();
        const token = await sdk.getToken(messaging, {
            vapidKey: config.vapidKey,
            serviceWorkerRegistration: registration,
        });
        if (!token) {
            throw new Error('Firebase returned no token.');
        }

        if (!(await waitForApi())) {
            throw new Error('API not loaded.');
        }
        const response = await API.push.registerToken({
            token,
            platform: 'web',
            device_label: (navigator.userAgent || '').substring(0, 255),
        });
        if (!response || !response.success) {
            throw new Error((response && response.message) || 'Server did not save the token.');
        }

        storeToken(token);
        return token;
    }

    /** Ask permission (must run from a click) and register this browser. */
    async function enable() {
        if (!config || inApp || !supported) {
            return false;
        }
        const permission = Notification.permission === 'granted'
            ? 'granted'
            : await Notification.requestPermission();
        if (permission !== 'granted') {
            return false;
        }
        await getAndSaveToken(true);
        return true;
    }

    /** Stop notifications for this browser (server row deactivated + Firebase token deleted). */
    async function disable(deleteFirebaseToken = true) {
        const token = storedToken();
        storeToken('');
        if (!token) {
            return;
        }
        try {
            if (await waitForApi()) {
                await API.push.unregisterToken({ token });
            }
        } catch (e) {
            console.log('Web push unregister failed', e);
        }
        if (deleteFirebaseToken && config && supported) {
            try {
                const { sdk, messaging } = await loadMessaging();
                await sdk.deleteToken(messaging);
            } catch (e) {
                console.log('Web push deleteToken failed', e);
            }
        }
    }

    // Called by API.auth.logout() so a shared computer stops getting the previous
    // guest's booking notifications. Only the server row is deactivated and unlinked:
    // logout navigates away immediately, and a Firebase deleteToken() cut off midway
    // leaves the browser holding a dead token that it hands back on the next getToken().
    window.BODARE_webPushLogout = function () {
        return disable(false);
    };

    window.BODARE_enableWebPush = enable;

    function isEnabled() {
        return supported && Notification.permission === 'granted' && storedToken() !== '';
    }

    function renderPanel() {
        const panel = document.getElementById('web-push-panel');
        if (!panel) {
            return;
        }
        if (!config || inApp) {
            panel.style.display = 'none';
            return;
        }
        panel.style.display = '';

        const status = document.getElementById('web-push-status');
        const enableBtn = document.getElementById('web-push-enable');
        const disableBtn = document.getElementById('web-push-disable');

        let message = '';
        let showEnable = false;
        let showDisable = false;

        if (isIos() && !isStandalone()) {
            message = 'On iPhone and iPad, tap Share → Add to Home Screen, open BODARE from your home screen, then turn on notifications here.';
        } else if (!supported) {
            message = 'This browser does not support notifications.';
        } else if (Notification.permission === 'denied') {
            message = 'Notifications are blocked for this site. Allow them in your browser settings, then reload this page.';
        } else if (isEnabled()) {
            message = 'Notifications are on for this browser. You will be alerted when a booking is confirmed, an invoice is ready, and the day before check-in.';
            showDisable = true;
        } else {
            message = 'Get an alert on this device when your booking is confirmed, an invoice is ready, and the day before check-in.';
            showEnable = true;
        }

        if (status) status.textContent = message;
        if (enableBtn) enableBtn.style.display = showEnable ? '' : 'none';
        if (disableBtn) disableBtn.style.display = showDisable ? '' : 'none';
    }

    function notify(message, type) {
        if (typeof showToast === 'function') {
            showToast(message, type);
        }
    }

    function bindPanel() {
        const enableBtn = document.getElementById('web-push-enable');
        const disableBtn = document.getElementById('web-push-disable');

        if (enableBtn) {
            enableBtn.addEventListener('click', async () => {
                enableBtn.disabled = true;
                try {
                    const ok = await enable();
                    notify(ok ? 'Notifications turned on.' : 'Notifications were not allowed.', ok ? 'success' : 'error');
                } catch (e) {
                    console.log('Web push enable failed', e);
                    notify('Could not turn on notifications. Please try again.', 'error');
                } finally {
                    enableBtn.disabled = false;
                    renderPanel();
                }
            });
        }

        if (disableBtn) {
            disableBtn.addEventListener('click', async () => {
                disableBtn.disabled = true;
                try {
                    await disable();
                    notify('Notifications turned off for this browser.', 'success');
                } finally {
                    disableBtn.disabled = false;
                    renderPanel();
                }
            });
        }

        renderPanel();
    }

    window.addEventListener('DOMContentLoaded', bindPanel);

    // Tokens can rotate; refresh silently for a logged-in guest who already opted in.
    window.addEventListener('load', () => {
        if (!config || inApp || !supported || !isLoggedIn()) {
            return;
        }
        if (Notification.permission !== 'granted' || storedToken() === '') {
            return;
        }
        getAndSaveToken()
            .catch((e) => console.log('Web push token refresh failed', e))
            .finally(renderPanel);
    });
})();
