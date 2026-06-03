/**
 * Notification Handler for Visitor System
 * Integrates with WebSocket real-time client and Service Worker
 * Sends browser notifications when visitors check in or messages arrive
 *
 * Usage: Include in admin_dashboard.php
 * <script src="notification_handler.js" defer></script>
 */

window.NotificationHandler = (function () {
    let notificationsEnabled = false;
    let serviceWorkerReady = false;

    /**
     * Initialize: Register Service Worker and Request Permissions
     */
    function init() {
        console.log('[NotificationHandler] Initializing...');

        // Step 1: Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/visitor-system/service-worker.js')
                .then((registration) => {
                    console.log('[NotificationHandler] Service Worker registered:', registration);
                    serviceWorkerReady = true;
                })
                .catch((error) => {
                    console.warn('[NotificationHandler] Service Worker registration failed:', error);
                });
        } else {
            console.warn('[NotificationHandler] Service Workers not supported in this browser');
            return;
        }

        // Step 2: Request Notification Permissions (only if not already asked)
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                console.log('[NotificationHandler] Notifications already granted');
                notificationsEnabled = true;
                setupWebSocketListeners();
            } else if (Notification.permission !== 'denied') {
                // Only ask if user hasn't already denied
                console.log('[NotificationHandler] Requesting notification permission...');
                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        console.log('[NotificationHandler] Notifications granted by user');
                        notificationsEnabled = true;
                        setupWebSocketListeners();
                    } else {
                        console.log('[NotificationHandler] Notifications denied by user');
                    }
                });
            } else {
                console.log('[NotificationHandler] Notifications previously denied by user');
            }
        } else {
            console.warn('[NotificationHandler] Notifications not supported in this browser');
        }
    }

    /**
     * Setup WebSocket Event Listeners
     * Listen for visitor arrivals and messages
     */
    function setupWebSocketListeners() {
        console.log('[NotificationHandler] Setting up WebSocket listeners...');

        // Ensure RealtimeClient is available and connected
        if (!window.RealtimeClient) {
            console.warn('[NotificationHandler] RealtimeClient not found. Retrying in 1s...');
            setTimeout(setupWebSocketListeners, 1000);
            return;
        }

        // Listen for visitor arrivals
        window.RealtimeClient.on('visitor_arrival', (data) => {
            console.log('[NotificationHandler] Visitor arrival event:', data);
            showNotification(
                '👤 New Visitor Arrived',
                `${data.fullname} is checking in to ${data.destination_name || 'your destination'}.`,
                {
                    body: `Visitor Type: ${data.visitor_type || 'Guest'}\nPhone: ${data.phone || 'N/A'}`,
                    tag: `visitor-${data.id}`, // Deduplicate notifications for same visitor
                    requireInteraction: false, // User can dismiss
                    data: { visitorId: data.id, destinationId: data.destination_id }
                }
            );
        });

        // Listen for messages (visitor unavailable / away)
        window.RealtimeClient.on('new_message', (data) => {
            console.log('[NotificationHandler] New message event:', data);
            showNotification(
                '💬 New Message from Visitor',
                `${data.fullname} sent a message.`,
                {
                    body: data.purpose || data.message || 'No message content',
                    tag: `message-${data.id}`,
                    requireInteraction: true, // Require user interaction for important messages
                    data: { visitorId: data.id, destinationId: data.destination_id }
                }
            );
        });

        // Listen for availability updates (optional)
        window.RealtimeClient.on('availability_updated', (data) => {
            console.log('[NotificationHandler] Availability update event:', data);
            // Only notify if admin availability changed and it's relevant to this user
            // This prevents notification spam
        });

        console.log('[NotificationHandler] WebSocket listeners established');
    }

    /**
     * Show a browser notification
     * Uses the Service Worker to display the notification
     */
    function showNotification(title, body = '', options = {}) {
        if (!notificationsEnabled) {
            console.warn('[NotificationHandler] Notifications not enabled. Skipping notification.');
            return;
        }

        if (!serviceWorkerReady) {
            console.warn('[NotificationHandler] Service Worker not ready yet. Queuing notification.');
            setTimeout(() => showNotification(title, body, options), 500);
            return;
        }

        const notificationOptions = {
            body: body,
            tag: 'visitor-system', // Group notifications
            requireInteraction: false, // Allow auto-dismiss
            ...options
        };

        console.log('[NotificationHandler] Sending notification to Service Worker:', title, notificationOptions);

        // Send message to Service Worker to display notification
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'SHOW_NOTIFICATION',
                title: title,
                options: notificationOptions
            });
        } else {
            // Fallback: Use browser's Notification API directly if Service Worker missing
            try {
                new Notification(title, notificationOptions);
            } catch (e) {
                console.error('[NotificationHandler] Failed to show notification:', e);
            }
        }
    }

    /**
     * Check if notifications are supported and enabled
     */
    function isEnabled() {
        return notificationsEnabled && ('Notification' in window);
    }

    /**
     * Manually request permission (for testing)
     */
    function requestPermission() {
        if ('Notification' in window && Notification.permission !== 'granted') {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    notificationsEnabled = true;
                    console.log('[NotificationHandler] Notifications now enabled');
                    setupWebSocketListeners();
                }
            });
        }
    }

    /**
     * Disable notifications
     */
    function disable() {
        notificationsEnabled = false;
        console.log('[NotificationHandler] Notifications disabled');
    }

    /**
     * Enable notifications (if permission already granted)
     */
    function enable() {
        if ('Notification' in window && Notification.permission === 'granted') {
            notificationsEnabled = true;
            setupWebSocketListeners();
            console.log('[NotificationHandler] Notifications enabled');
        }
    }

    // Public API
    return {
        init: init,
        show: showNotification,
        isEnabled: isEnabled,
        requestPermission: requestPermission,
        enable: enable,
        disable: disable
    };
})();

// Auto-initialize when document is ready
document.addEventListener('DOMContentLoaded', function () {
    console.log('[NotificationHandler] DOM loaded, initializing...');
    window.NotificationHandler.init();
});
