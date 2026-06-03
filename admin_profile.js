/**
 * Admin Profile Dashboard JavaScript
 * Handles availability updates, WebSocket notifications, and UI interactions
 */

(function () {
    'use strict';

    // Global state
    let ws = null;
    let wsReconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;
    let currentStatus = window.PROFILE_DATA.currentStatus;

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initAvailabilityControls();
        initStatusMessage();
        initWebSocket();
        highlightCurrentStatus();
        updateCharCounter();
    }

    /**
     * Availability Status Controls
     */
    function initAvailabilityControls() {
        const statusButtons = document.querySelectorAll('.status-btn');

        statusButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const newStatus = this.dataset.status;
                updateAvailabilityStatus(newStatus);
            });
        });
    }

    function highlightCurrentStatus() {
        const statusButtons = document.querySelectorAll('.status-btn');
        statusButtons.forEach(btn => {
            if (btn.dataset.status === currentStatus) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    async function updateAvailabilityStatus(newStatus) {
        const statusMessage = document.getElementById('statusMessage').value.trim();
        const messageDiv = document.getElementById('statusUpdateMessage');

        // Show loading state
        messageDiv.textContent = 'Updating status...';
        messageDiv.className = 'status-update-message';
        messageDiv.style.display = 'block';

        try {
            const response = await fetch('update_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    status: newStatus,
                    message: statusMessage,
                    csrf_token: window.PROFILE_DATA.csrfToken
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                currentStatus = newStatus;
                highlightCurrentStatus();

                const badge = document.getElementById('currentStatusBadge');
                badge.className = `status-badge status-${newStatus}`;
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                // Show success message
                messageDiv.textContent = '✓ Status updated successfully!';
                messageDiv.className = 'status-update-message success';

                // Hide message after 3 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            } else {
                throw new Error(data.error || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            messageDiv.textContent = '✕ ' + error.message;
            messageDiv.className = 'status-update-message error';
        }
    }

    /**
     * Status Message Input
     */
    function initStatusMessage() {
        const input = document.getElementById('statusMessage');
        const btnUpdate = document.getElementById('btnUpdateStatusMsg');

        input.addEventListener('input', updateCharCounter);

        // Save on button click
        if (btnUpdate) {
            btnUpdate.addEventListener('click', function () {
                updateAvailabilityStatus(currentStatus);
            });
        }

        // Also allow Enter key to submit
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateAvailabilityStatus(currentStatus);
            }
        });
    }

    function updateCharCounter() {
        const input = document.getElementById('statusMessage');
        const counter = document.getElementById('charCount');
        if (input && counter) {
            counter.textContent = input.value.length;
        }
    }

    /**
     * WebSocket Connection for Real-time Notifications
     */
    function initWebSocket() {
        // Try to connect to WebSocket server
        connectWebSocket();

        // Fallback to polling if WebSocket fails
        setTimeout(() => {
            if (!ws || ws.readyState !== WebSocket.OPEN) {
                // console.log('WebSocket not available, falling back to polling');
                startPolling();
            }
        }, 5000);
    }

    function connectWebSocket() {
        try {
            // Use the same hostname as the current page (localhost or IP)
            const wsHost = window.location.hostname;
            ws = new WebSocket(`ws://${wsHost}:8080`);

            ws.onopen = function () {
                // console.log('WebSocket connected');
                wsReconnectAttempts = 0;

                // Authenticate
                ws.send(JSON.stringify({
                    type: 'auth',
                    user_id: window.PROFILE_DATA.userId,
                    session_id: getSessionId()
                }));

                // Start ping interval
                startPingInterval();
            };

            ws.onmessage = function (event) {
                try {
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };

            ws.onerror = function (error) {
                // Silently handle WebSocket errors - fallback to polling
                // console.error('WebSocket error:', error);
            };

            ws.onclose = function () {
                // Silently handle close - will reconnect or fall back to polling
                // console.log('WebSocket closed');

                // Attempt to reconnect
                if (wsReconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                    wsReconnectAttempts++;
                    setTimeout(() => {
                        // console.log(`Reconnecting... Attempt ${wsReconnectAttempts}`);
                        connectWebSocket();
                    }, 3000 * wsReconnectAttempts);
                } else {
                    console.log('WebSocket unavailable, using polling for notifications');
                    startPolling();
                }
            };
        } catch (error) {
            console.error('Error creating WebSocket:', error);
            startPolling();
        }
    }

    function handleWebSocketMessage(data) {
        switch (data.type) {
            case 'auth_success':
                console.log('Authenticated as:', data.user.username);
                break;

            case 'auth_failed':
                console.error('Authentication failed:', data.message);
                ws.close();
                break;

            case 'notification':
                handleNewNotification(data.data);
                break;

            case 'pong':
                // Keep-alive response
                break;
        }
    }

    function startPingInterval() {
        setInterval(() => {
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'ping' }));
            }
        }, 30000); // Ping every 30 seconds
    }

    function handleNewNotification(notification) {
        // Update unread count
        updateUnreadCount();

        // Add notification to list
        prependNotification(notification);

        // Show browser notification if permitted
        showBrowserNotification(notification);

        // Play sound (optional)
        playNotificationSound();
    }

    /**
     * Polling Fallback
     */
    let pollingInterval = null;

    function startPolling() {
        if (pollingInterval) return; // Already polling

        pollingInterval = setInterval(async () => {
            try {
                const response = await fetch('fetch_notifications.php?unread_only=true&limit=5');
                const data = await response.json();

                if (data.success) {
                    updateUnreadCount(data.unread_count);

                    // Check for new notifications
                    data.notifications.forEach(notif => {
                        if (!document.querySelector(`[data-notification-id="${notif.id}"]`)) {
                            prependNotification(notif);
                            showBrowserNotification(notif);
                        }
                    });
                }
            } catch (error) {
                console.error('Error polling notifications:', error);
            }
        }, 15000); // Poll every 15 seconds
    }

    /**
     * Notification UI Updates
     */
    function updateUnreadCount(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count === undefined) {
                // Fetch count
                fetch('fetch_notifications.php?unread_only=true&limit=1')
                    .then(r => r.json())
                    .then(data => {
                        badge.textContent = `${data.unread_count} unread`;
                    });
            } else {
                badge.textContent = `${count} unread`;
            }
        }
    }

    function prependNotification(notification) {
        const list = document.getElementById('notificationsList');
        if (!list) return;

        // Remove "no notifications" message if present
        const noNotif = list.querySelector('.no-notifications');
        if (noNotif) {
            noNotif.remove();
        }

        // Create notification element
        const notifEl = createNotificationElement(notification);

        // Prepend to list
        list.insertBefore(notifEl, list.firstChild);

        // Limit to 5 notifications
        const items = list.querySelectorAll('.notification-item');
        if (items.length > 5) {
            items[items.length - 1].remove();
        }

        // Animate in
        setTimeout(() => {
            notifEl.style.opacity = '1';
            notifEl.style.transform = 'translateX(0)';
        }, 10);
    }

    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${notification.is_read ? 'read' : 'unread'}`;
        div.dataset.notificationId = notification.id;
        div.onclick = function () { window.toggleNotification(this); }; // Add click handler

        div.style.opacity = '0';
        div.style.transform = 'translateX(-20px)';
        div.style.transition = 'all 0.3s ease';

        const icon = notification.type === 'visitor_arrival' ? '🔔' :
            notification.type === 'checkout' ? '👋' : 'ℹ️';

        // Truncate logic
        const message = notification.message || '';
        const preview = message.length > 50 ? message.substring(0, 47) + '...' : message;

        div.innerHTML = `
            <div class="notification-icon">${icon}</div>
            <div class="notification-content">
                <div class="notification-header">
                    <div class="notification-time">${notification.time_ago || 'Just now'}</div>
                </div>
                <div class="notification-preview">${escapeHtml(preview)}</div>
                <div class="notification-full">
                    ${escapeHtml(message)}
                    ${notification.visitor_name ? `<br><small><strong>Visitor:</strong> ${escapeHtml(notification.visitor_name)}</small>` : ''}
                </div>
            </div>
        `;

        return div;
    }

    /**
     * Notification Interactions
     */
    window.toggleNotification = function (element) {
        // Toggle expanded class
        element.classList.toggle('expanded');

        // If unread, mark as read
        if (element.classList.contains('unread')) {
            const id = element.dataset.notificationId;
            markNotificationAsRead(id, element);
        }
    };

    async function markNotificationAsRead(id, element) {
        try {
            const formData = new URLSearchParams();
            formData.append('notification_id', id);
            formData.append('csrf_token', window.PROFILE_DATA.csrfToken);

            const response = await fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update UI visually
                element.classList.remove('unread');
                element.classList.add('read');

                // Update unread count
                updateUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Previous Code...

    /**
     * Browser Notifications
     */
    function showBrowserNotification(notification) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            const n = new Notification('New Visitor', {
                body: notification.message,
                icon: 'ui_logo-removebg-preview.png',
                badge: 'ui_logo-removebg-preview.png'
            });

            // Focus window on click
            n.onclick = function () {
                window.focus();
                // Optionally reload or highlight notification
            };
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    showBrowserNotification(notification);
                }
            });
        }
    }

    function playNotificationSound() {
        // Optional: play a subtle notification sound
        // const audio = new Audio('notification.mp3');
        // audio.volume = 0.3;
        // audio.play().catch(() => {}); // Ignore errors
    }

    /**
     * Utility Functions
     */
    function getSessionId() {
        // Get PHP session ID from cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'PHPSESSID') {
                return value;
            }
        }
        return '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Request notification permission on load
    if ('Notification' in window && Notification.permission === 'default') {
        setTimeout(() => {
            Notification.requestPermission();
        }, 2000);
    }
})();
