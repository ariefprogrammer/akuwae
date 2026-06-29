const CACHE_NAME = 'akuwae-app-v1';

self.addEventListener('install', (event) => {
    console.log('[SW] Installed');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('[SW] Activated');
    event.waitUntil(clients.claim());
});

// Terima push notification
self.addEventListener('push', (event) => {
    let data = {};

    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'Akuwae', body: event.data?.text() || 'Ada notifikasi baru' };
    }

    const title = data.title || 'Akuwae';
    const options = {
        body: data.body || '',
        icon: data.icon || '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-192x192.png',
        data: { url: data.url || '/' },
        vibrate: [200, 100, 200],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// Saat notifikasi diklik, buka URL terkait
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});