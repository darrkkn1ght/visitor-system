/**
 * Realtime Notification Client
 * Connects to Node.js WebSocket server and dispatches events
 */

window.RealtimeClient = (function () {
    let ws = null;
    let config = {};
    let reconnectAttempts = 0;
    const MAX_RECONNECT_DELAY = 10000;
    const eventHandlers = {};

    function connect(initialConfig) {
        if (ws) {
            console.log('RealtimeClient: Closing existing connection to reconnect...');
            // Prevent auto-reconnect from the old close
            ws.onclose = null;
            ws.close();
        }

        config = { ...config, ...initialConfig };
        console.log('RealtimeClient: Connecting...', config);

        try {
            // Dynamic hostname for LAN access
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const host = window.location.hostname;
            ws = new WebSocket(`${protocol}//${host}:3005/ws`);

            ws.onopen = function () {
                console.log('RealtimeClient: Connected');
                reconnectAttempts = 0;

                // Send handshake
                ws.send(JSON.stringify({
                    type: 'hello',
                    client: config.clientType, // 'admin' or 'visitor'
                    role: config.role,
                    admin_id: config.adminId,
                    destination_id: config.destinationId
                }));
            };

            ws.onmessage = function (event) {
                try {
                    const message = JSON.parse(event.data);
                    dispatch(message.event, message.data);
                } catch (e) {
                    console.error('RealtimeClient key error:', e);
                }
            };

            ws.onclose = function () {
                console.log('RealtimeClient: Disconnected');
                ws = null;
                scheduleReconnect();
            };

            ws.onerror = function (err) {
                console.error('RealtimeClient: Error', err);
                ws.close();
            };

        } catch (e) {
            console.error('RealtimeClient connection failed:', e);
            scheduleReconnect();
        }
    }

    function scheduleReconnect() {
        const delay = Math.min(1000 * Math.pow(1.5, reconnectAttempts), MAX_RECONNECT_DELAY);
        reconnectAttempts++;
        console.log(`RealtimeClient: Reconnecting in ${delay}ms...`);
        setTimeout(() => connect(config), delay);
    }

    function on(eventType, handler) {
        if (!eventHandlers[eventType]) {
            eventHandlers[eventType] = [];
        }
        eventHandlers[eventType].push(handler);
    }

    function dispatch(eventType, payload) {
        console.log(`RealtimeClient: Received [${eventType}]`, payload);
        if (eventHandlers[eventType]) {
            eventHandlers[eventType].forEach(h => h(payload));
        }
    }

    return {
        connect,
        on,
        isConnected: () => ws && ws.readyState === WebSocket.OPEN
    };
})();
