const WebSocket = require('ws');
const http = require('http');

const PORT = 3005;

// Create HTTP server for internal PHP API calls
const server = http.createServer((req, res) => {
    // Only allow local POST requests
    const isLocal = req.socket.remoteAddress === '::1' || req.socket.remoteAddress === '127.0.0.1' || req.socket.remoteAddress === '::ffff:127.0.0.1';

    // CORS headers for local dev (if needed)
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHeader(204);
        res.end();
        return;
    }

    if (req.method === 'POST' && req.url === '/emit') {
        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });
        req.on('end', () => {
            try {
                const data = JSON.parse(body);
                broadcastEvent(data);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ ok: true }));
            } catch (e) {
                console.error('Invalid JSON received:', e);
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ ok: false, error: 'Invalid JSON' }));
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

// Create WebSocket server attached to HTTP server
const wss = new WebSocket.Server({ server, path: '/ws' });

// Store clients with their subscription data
// Map<WebSocket, { clientType: string, role: string, adminId: number, destinationId: number }>
const clients = new Map();

wss.on('connection', (ws) => {
    // console.log('Client connected');
    clients.set(ws, {});

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            // Handle handshake
            if (data.type === 'hello') {
                const metadata = {
                    clientType: data.client || 'unknown',
                    role: data.role || 'guest',
                    adminId: parseInt(data.admin_id) || 0,
                    destinationId: parseInt(data.destination_id) || 0,
                    connectedAt: new Date()
                };
                clients.set(ws, metadata);
                // console.log('Client identified:', metadata);
            }
        } catch (e) {
            console.error('WS Error:', e);
        }
    });

    ws.on('close', () => {
        clients.delete(ws);
    });

    ws.on('error', (e) => {
        console.error('WS Connection Error:', e);
        clients.delete(ws);
    });
});

/**
 * Broadcast event to relevant clients
 * @param {Object} eventData 
 * { 
 *   event: string, 
 *   admin_id: number|null, 
 *   destination_id: number|null, 
 *   payload: Object 
 * }
 */
function broadcastEvent(data) {
    const { event, admin_id, destination_id, payload } = data;
    const message = JSON.stringify({
        event: event,
        data: payload,
        ts: new Date().toISOString()
    });

    const targetAdminId = parseInt(admin_id) || 0;
    const targetDestId = parseInt(destination_id) || 0;

    console.log(`Broadcasting [${event}] to Admin:${targetAdminId}, Dest:${targetDestId}`);

    wss.clients.forEach(client => {
        if (client.readyState !== WebSocket.OPEN) return;

        const meta = clients.get(client);
        if (!meta) return;

        let shouldSend = false;

        // Logic:
        // 1. If admin_id is provided, send to that specific admin
        if (targetAdminId > 0 && meta.adminId === targetAdminId) {
            shouldSend = true;
        }

        // 2. If destination_id is provided
        if (targetDestId > 0) {
            // Send to visitors on this destination page
            if (meta.clientType === 'visitor' && meta.destinationId === targetDestId) {
                shouldSend = true;
            }
            // Send to other admins of this destination (e.g. receptionists)
            if (meta.clientType === 'admin' && meta.destinationId === targetDestId) {
                shouldSend = true;
            }
        }

        // 3. Directors/Super Admins might want to see everything? 
        // For now, let's keep it specific to avoid noise, unless explicitly handled.

        // Special case: availability updates should go to ALL visitors of that destination
        if (event === 'availability_updated') {
            if (meta.clientType === 'visitor' && meta.destinationId === targetDestId) {
                shouldSend = true;
            }
            // Also update the admin themselves (e.g. other tabs)
            if (meta.adminId === targetAdminId) {
                shouldSend = true;
            }
        }

        if (shouldSend) {
            client.send(message);
        }
    });
}

server.listen(PORT, () => {
    console.log(`Realtime server running on port ${PORT}`);
});
