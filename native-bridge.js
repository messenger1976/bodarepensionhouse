(function () {
    if (!window.Capacitor) {
        return;
    }

    document.documentElement.classList.add('is-capacitor');

    function plugin(name) {
        return (window.Capacitor.Plugins && window.Capacitor.Plugins[name]) || null;
    }

    async function setupStatusBar() {
        const StatusBar = plugin('StatusBar');
        if (!StatusBar) return;
        try {
            await StatusBar.setBackgroundColor({ color: '#065f46' });
            await StatusBar.setStyle({ style: 'DARK' });
        } catch (error) {
            console.log('StatusBar not available', error);
        }
    }

    async function setupBackButton() {
        const App = plugin('App');
        if (!App || !App.addListener) return;
        App.addListener('backButton', ({ canGoBack }) => {
            if (canGoBack || window.history.length > 1) {
                window.history.back();
            } else if (App.exitApp) {
                App.exitApp();
            }
        });
    }

    async function syncPushTokenToServer(tokenValue, attempt = 0) {
        if (!tokenValue) {
            return false;
        }

        if (typeof API === 'undefined' || !API.push) {
            if (attempt < 10) {
                await new Promise((resolve) => setTimeout(resolve, 400));
                return syncPushTokenToServer(tokenValue, attempt + 1);
            }
            console.log('Push token sync skipped: API.push not loaded');
            return false;
        }

        try {
            const platform = window.Capacitor.getPlatform
                ? window.Capacitor.getPlatform()
                : 'android';
            const response = await API.push.registerToken({
                token: tokenValue,
                platform,
                device_label: navigator.userAgent ? navigator.userAgent.substring(0, 255) : '',
            });
            console.log('Push token synced', response && response.success);
            return !!(response && response.success);
        } catch (error) {
            console.log('Push token sync failed', error);
            return false;
        }
    }

    window.BODARE_syncPushToken = async function () {
        if (window.BODARE_PUSH_TOKEN) {
            await syncPushTokenToServer(window.BODARE_PUSH_TOKEN);
        }
    };

    async function setupPush() {
        if (window.BODARE_PUSH_ENABLED !== true) {
            return;
        }

        const PushNotifications = plugin('PushNotifications');
        if (!PushNotifications) return;

        try {
            PushNotifications.addListener('registration', (token) => {
                const tokenValue = token && token.value ? token.value : '';
                window.BODARE_PUSH_TOKEN = tokenValue;
                console.log('FCM token registered');
                syncPushTokenToServer(tokenValue);
            });

            PushNotifications.addListener('registrationError', (error) => {
                console.log('Push registration error', error);
            });

            PushNotifications.addListener('pushNotificationReceived', (notification) => {
                console.log('Push received', notification && notification.title);
            });

            PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
                const data = (action && action.notification && action.notification.data) || {};
                if (data.url) {
                    window.location.href = data.url;
                } else if (data.booking) {
                    window.location.href = 'customer-dashboard.php';
                }
            });

            let perm = await PushNotifications.checkPermissions();
            if (perm.receive !== 'granted') {
                perm = await PushNotifications.requestPermissions();
            }
            if (perm.receive !== 'granted') {
                return;
            }

            await PushNotifications.register();
        } catch (error) {
            console.log('Push notifications skipped', error);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        setupStatusBar();
        setupBackButton();
        setupPush();
    });

    window.addEventListener('load', () => {
        if (window.BODARE_PUSH_TOKEN) {
            syncPushTokenToServer(window.BODARE_PUSH_TOKEN);
        }
    });
})();
