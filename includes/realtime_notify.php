<?php
// includes/realtime_notify.php

/**
 * Send a realtime notification to the Node.js server
 * 
 * @param string $event Event name (e.g. 'availability_updated', 'new_message')
 * @param int $adminId Target admin ID (0 if not specific)
 * @param int $destinationId Target destination ID (0 if not specific)
 * @param array $payload Additional data to send
 * @return bool True if successful (or ignored), False on error (but doesn't throw)
 */
function notify_realtime($event, $adminId, $destinationId, $payload = [])
{
    $url = 'http://127.0.0.1:3001/emit';

    $data = [
        'event' => $event,
        'admin_id' => $adminId,
        'destination_id' => $destinationId,
        'payload' => $payload
    ];

    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);

    // Fast timeout to not block PHP execution
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 200);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($result === false) {
        // Silently fail or log to error log
        // error_log("Realtime notify failed: $error");
        return false;
    }

    return ($httpCode === 200);
}
