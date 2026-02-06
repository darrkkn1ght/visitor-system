<?php
/**
 * WebSocket Server for Real-Time Notifications
 * 
 * This script runs as a standalone WebSocket server to push notifications
 * to connected admin users in real-time.
 * 
 * Usage: php websocket_server.php
 * 
 * Requirements:
 * - PHP 7.4+ with sockets extension
 * - Run this script in the background: php websocket_server.php &
 * - Or use a process manager like supervisord
 */

require_once 'db.php';

// Configuration
define('WS_HOST', '0.0.0.0');
define('WS_PORT', 8080);
define('PING_INTERVAL', 30); // seconds

class WebSocketServer
{
    private $master;
    private $sockets = [];
    private $clients = [];
    private $db;

    public function __construct($host, $port)
    {
        global $conn;
        $this->db = $conn;

        // Create socket
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $host, $port);
        socket_listen($this->master, 20);

        $this->sockets[] = $this->master;

        echo "[" . date('Y-m-d H:i:s') . "] WebSocket server started on $host:$port\n";
    }

    public function run()
    {
        while (true) {
            $changed = $this->sockets;
            @socket_select($changed, $write, $except, 1);

            foreach ($changed as $socket) {
                if ($socket == $this->master) {
                    $this->handleNewConnection();
                } else {
                    $this->handleClientMessage($socket);
                }
            }

            // Check for new notifications to push
            $this->checkAndPushNotifications();

            // Clean up dead connections
            $this->cleanupConnections();
        }
    }

    private function handleNewConnection()
    {
        $client = socket_accept($this->master);
        if ($client === false) {
            return;
        }

        $this->sockets[] = $client;
        $this->clients[(int) $client] = [
            'socket' => $client,
            'handshake' => false,
            'user_id' => null,
            'session_id' => null,
            'last_ping' => time()
        ];

        echo "[" . date('Y-m-d H:i:s') . "] New connection\n";
    }

    private function handleClientMessage($socket)
    {
        $client_id = (int) $socket;
        $bytes = @socket_recv($socket, $buffer, 2048, 0);

        if ($bytes === false || $bytes == 0) {
            $this->disconnect($socket);
            return;
        }

        $client = &$this->clients[$client_id];

        if (!$client['handshake']) {
            $this->performHandshake($socket, $buffer);
        } else {
            $message = $this->unmask($buffer);
            $this->processMessage($socket, $message);
        }
    }

    private function performHandshake($socket, $received_header)
    {
        $headers = [];
        $lines = preg_split("/\r\n/", $received_header);

        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        if (!isset($headers['Sec-WebSocket-Key'])) {
            return false;
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: $secAccept\r\n\r\n";

        socket_write($socket, $response, strlen($response));

        $client_id = (int) $socket;
        $this->clients[$client_id]['handshake'] = true;

        echo "[" . date('Y-m-d H:i:s') . "] Handshake completed\n";
    }

    private function processMessage($socket, $message)
    {
        $client_id = (int) $socket;
        $data = json_decode($message, true);

        if (!$data) {
            return;
        }

        switch ($data['type'] ?? '') {
            case 'auth':
                $this->authenticateClient($socket, $data);
                break;

            case 'ping':
                $this->clients[$client_id]['last_ping'] = time();
                $this->send($socket, json_encode(['type' => 'pong']));
                break;

            case 'mark_read':
                $this->markNotificationRead($data['notification_id'] ?? 0);
                break;
        }
    }

    private function authenticateClient($socket, $data)
    {
        $session_id = $data['session_id'] ?? '';
        $user_id = $data['user_id'] ?? 0;

        // Verify session in database
        $stmt = $this->db->prepare("
            SELECT id, username, role 
            FROM users 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $this->send($socket, json_encode([
                'type' => 'auth_failed',
                'message' => 'Invalid user'
            ]));
            $this->disconnect($socket);
            return;
        }

        $client_id = (int) $socket;
        $this->clients[$client_id]['user_id'] = $user_id;
        $this->clients[$client_id]['session_id'] = $session_id;

        // Store in database
        $connection_id = uniqid('ws_', true);
        $ip = $this->getClientIP($socket);

        $stmt = $this->db->prepare("
            INSERT INTO websocket_sessions 
            (user_id, session_id, connection_id, ip_address) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            connection_id = VALUES(connection_id),
            last_ping = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("isss", $user_id, $session_id, $connection_id, $ip);
        $stmt->execute();
        $stmt->close();

        $this->send($socket, json_encode([
            'type' => 'auth_success',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]));

        echo "[" . date('Y-m-d H:i:s') . "] User {$user['username']} authenticated\n";
    }

    private function checkAndPushNotifications()
    {
        static $last_check = 0;

        // Check every 2 seconds
        if (time() - $last_check < 2) {
            return;
        }

        $last_check = time();

        // Get unread notifications for connected users
        $user_ids = [];
        foreach ($this->clients as $client) {
            if ($client['user_id']) {
                $user_ids[] = $client['user_id'];
            }
        }

        if (empty($user_ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        $stmt = $this->db->prepare("
            SELECT n.*, v.fullname as visitor_name, v.purpose
            FROM notifications n
            LEFT JOIN visitors v ON n.visitor_id = v.id
            WHERE n.user_id IN ($placeholders)
            AND n.is_read = 0
            AND n.created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY n.created_at DESC
        ");

        $types = str_repeat('i', count($user_ids));
        $stmt->bind_param($types, ...$user_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($notification = $result->fetch_assoc()) {
            $this->pushNotificationToUser($notification['user_id'], $notification);
        }

        $stmt->close();
    }

    private function pushNotificationToUser($user_id, $notification)
    {
        foreach ($this->clients as $client) {
            if ($client['user_id'] == $user_id && $client['handshake']) {
                $this->send($client['socket'], json_encode([
                    'type' => 'notification',
                    'data' => $notification
                ]));
            }
        }
    }

    private function markNotificationRead($notification_id)
    {
        if ($notification_id > 0) {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $notification_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function send($socket, $message)
    {
        $message = $this->mask($message);
        @socket_write($socket, $message, strlen($message));
    }

    private function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, $length);
        }

        return $header . $text;
    }

    private function unmask($text)
    {
        $length = ord($text[1]) & 127;

        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }

        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }

    private function disconnect($socket)
    {
        $client_id = (int) $socket;

        if (isset($this->clients[$client_id])) {
            $user_id = $this->clients[$client_id]['user_id'];
            $session_id = $this->clients[$client_id]['session_id'];

            // Remove from database
            if ($session_id) {
                $stmt = $this->db->prepare("DELETE FROM websocket_sessions WHERE session_id = ?");
                $stmt->bind_param("s", $session_id);
                $stmt->execute();
                $stmt->close();
            }

            unset($this->clients[$client_id]);
        }

        $index = array_search($socket, $this->sockets);
        if ($index !== false) {
            unset($this->sockets[$index]);
        }

        @socket_close($socket);
        echo "[" . date('Y-m-d H:i:s') . "] Connection closed\n";
    }

    private function cleanupConnections()
    {
        static $last_cleanup = 0;

        if (time() - $last_cleanup < 60) {
            return;
        }

        $last_cleanup = time();

        // Remove stale connections (no ping for 2 minutes)
        foreach ($this->clients as $client_id => $client) {
            if ($client['handshake'] && (time() - $client['last_ping']) > 120) {
                echo "[" . date('Y-m-d H:i:s') . "] Cleaning up stale connection\n";
                $this->disconnect($client['socket']);
            }
        }

        // Clean up database
        $stmt = $this->db->prepare("
            DELETE FROM websocket_sessions 
            WHERE last_ping < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute();
        $stmt->close();
    }

    private function getClientIP($socket)
    {
        @socket_getpeername($socket, $ip);
        return $ip ?? 'unknown';
    }
}

// Start server
try {
    $server = new WebSocketServer(WS_HOST, WS_PORT);
    $server->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
