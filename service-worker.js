/**
 * Service Worker for Browser Push Notifications
 * Allows notifications even when tab is not focused
 *
 * Installed automatically by notification_handler.js
 */

self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    self.skipWaiting(); // Activate immediately
});

self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    self.clients.claim(); // Take control of all clients immediately
});

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
    const { type, title, options } = event.data;

    if (type === 'SHOW_NOTIFICATION') {
        console.log('[Service Worker] Showing notification:', title);

        self.registration.showNotification(title, {
            icon: '/visitor-system/ui_logo-removebg-preview.png',
            badge: '/visitor-system/ui_logo-removebg-preview.png',
            ...options
        });
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked:', event.notification.title);

    event.notification.close(); // Close the notification

    // Focus the existing window or open a new one
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            // Look for an existing window with the target URL
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes('/admin_dashboard.php') && 'focus' in client) {
                    return client.focus(); // Focus existing window
                }
            }
            // If no window exists, open a new one
            if (clients.openWindow) {
                return clients.openWindow('/visitor-system/admin_dashboard.php');
            }
        })
    );
});

// Handle notification close
self.addEventListener('notificationclose', (event) => {
    console.log('[Service Worker] Notification closed:', event.notification.title);
});
