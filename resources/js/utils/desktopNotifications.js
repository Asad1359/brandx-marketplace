/**
 * Request permission for desktop notifications.
 */
export async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.warn('This browser does not support desktop notifications.');
        return false;
    }

    if (Notification.permission === 'granted') {
        return true;
    }

    if (Notification.permission === 'denied') {
        return false;
    }

    const permission = await Notification.requestPermission();
    return permission === 'granted';
}

/**
 * Show a desktop notification.
 */
export function showNotification(title, body, onClick) {
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'granted') return;

    try {
        const notification = new Notification(title, {
            body,
            icon: '/favicon.ico',
            tag: 'brandx-chat',
            renotify: true,
        });

        notification.onclick = (event) => {
            event.preventDefault();
            window.focus();
            notification.close();
            onClick?.();
        };

        setTimeout(() => notification.close(), 6000);
    } catch (e) {
        console.warn('Could not show notification:', e);
    }
}

/**
 * Check if desktop notifications are enabled.
 */
export function isDesktopNotificationsEnabled() {
    return (
        'Notification' in window &&
        Notification.permission === 'granted' &&
        localStorage.getItem('chat_desktop_notifications') !== 'false'
    );
}

export function setDesktopNotificationsEnabled(enabled) {
    localStorage.setItem('chat_desktop_notifications', String(enabled));
}