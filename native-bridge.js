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

    async function setupPush() {
        const PushNotifications = plugin('PushNotifications');
        if (!PushNotifications) return;

        try {
            let perm = await PushNotifications.checkPermissions();
            if (perm.receive !== 'granted') {
                perm = await PushNotifications.requestPermissions();
            }
            if (perm.receive !== 'granted') {
                return;
            }

            await PushNotifications.register();

            PushNotifications.addListener('registration', (token) => {
                console.log('FCM token registered');
                window.BODARE_PUSH_TOKEN = token && token.value ? token.value : '';
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
        } catch (error) {
            console.log('Push notifications skipped', error);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        setupStatusBar();
        setupBackButton();
        setupPush();
    });
})();
