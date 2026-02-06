<?php
/**
 * rate_limiting.php - Rate Limiting System
 * 
 * Prevents abuse of resource-intensive operations like data exports
 * Tracks attempts per user per hour using database
 * 
 * Features:
 * - Per-user, per-hour tracking
 * - Configurable limits per operation
 * - Admin override capability
 * - Detailed logging of attempts
 */

/**
 * Configuration for rate limits
 * Define limits per operation type
 */
$RATE_LIMITS = [
    'export_csv' => [
        'limit' => 5,           // Max 5 exports per hour
        'window' => 3600,       // Per hour (seconds)
        'name' => 'CSV Export'
    ],
    'export_data' => [
        'limit' => 5,
        'window' => 3600,
        'name' => 'Data Export'
    ],
    'backup_records' => [
        'limit' => 3,           // More restrictive for backup
        'window' => 3600,
        'name' => 'Backup Records'
    ],
];

/**
 * Initialize rate limiting table if it doesn't exist
 * 
 * @global mysqli $conn
 * @return void
 */
function init_rate_limiting() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS `rate_limit_log` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) NOT NULL,
        `username` VARCHAR(50) NOT NULL,
        `operation` VARCHAR(50) NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `data_volume` BIGINT(20) DEFAULT 0 COMMENT 'Number of records exported',
        `status` VARCHAR(20) DEFAULT 'SUCCESS' COMMENT 'SUCCESS or BLOCKED',
        `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_user_op_time` (`user_id`, `operation`, `timestamp`),
        INDEX `idx_operation_time` (`operation`, `timestamp`),
        INDEX `idx_timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        error_log("Rate limit table creation failed: " . $conn->error);
    }
}

/**
 * Check if user has exceeded rate limit for operation
 * 
 * @param string $operation Operation name (e.g., 'export_csv')
 * @param int $user_id User ID
 * @param string $username Username for logging
 * @param string $ip_address IP address of request
 * @return array ['allowed' => bool, 'message' => string, 'remaining' => int]
 */
function check_rate_limit($operation, $user_id, $username, $ip_address) {
    global $conn, $RATE_LIMITS;
    
    // Check if operation is configured
    if (!isset($RATE_LIMITS[$operation])) {
        error_log("Unknown operation in rate limiting: $operation");
        return ['allowed' => true, 'message' => 'Operation not rate limited', 'remaining' => -1];
    }
    
    $config = $RATE_LIMITS[$operation];
    $limit = $config['limit'];
    $window = $config['window'];
    $cutoff_time = time() - $window;
    
    // Count attempts in the time window
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as attempt_count FROM rate_limit_log 
         WHERE user_id = ? AND operation = ? AND timestamp > FROM_UNIXTIME(?) AND status = 'SUCCESS'"
    );
    
    if (!$stmt) {
        error_log("Rate limit query failed: " . $conn->error);
        return ['allowed' => true, 'message' => 'Rate limit check failed', 'remaining' => -1];
    }
    
    $stmt->bind_param("isi", $user_id, $operation, $cutoff_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $attempts = $row['attempt_count'] ?? 0;
    $remaining = max(0, $limit - $attempts);
    
    if ($attempts >= $limit) {
        // Rate limit exceeded
        log_rate_limit_attempt($user_id, $username, $operation, $ip_address, 0, 'BLOCKED');
        
        return [
            'allowed' => false,
            'message' => "Too many {$config['name']} requests. Limit: {$limit} per hour. Please try again later.",
            'remaining' => 0,
            'reset_time' => get_rate_limit_reset_time($user_id, $operation)
        ];
    }
    
    return [
        'allowed' => true,
        'message' => "Rate limit OK. {$remaining} remaining.",
        'remaining' => $remaining
    ];
}

/**
 * Log a rate limit attempt
 * 
 * @param int $user_id User ID
 * @param string $username Username
 * @param string $operation Operation name
 * @param string $ip_address IP address
 * @param int $data_volume Number of records exported (0 if blocked)
 * @param string $status SUCCESS or BLOCKED
 * @return bool
 */
function log_rate_limit_attempt($user_id, $username, $operation, $ip_address, $data_volume = 0, $status = 'SUCCESS') {
    global $conn;
    
    $stmt = $conn->prepare(
        "INSERT INTO rate_limit_log (user_id, username, operation, ip_address, data_volume, status) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    if (!$stmt) {
        error_log("Rate limit log insert failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("isssii", $user_id, $username, $operation, $ip_address, $data_volume, $status);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Get rate limit reset time for user/operation
 * 
 * @param int $user_id User ID
 * @param string $operation Operation name
 * @return int Unix timestamp when limit resets
 */
function get_rate_limit_reset_time($user_id, $operation) {
    global $conn, $RATE_LIMITS;
    
    if (!isset($RATE_LIMITS[$operation])) {
        return 0;
    }
    
    $window = $RATE_LIMITS[$operation]['window'];
    
    // Get oldest attempt in the window
    $stmt = $conn->prepare(
        "SELECT UNIX_TIMESTAMP(MIN(timestamp)) as oldest_time FROM rate_limit_log 
         WHERE user_id = ? AND operation = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND status = 'SUCCESS' 
         LIMIT 1"
    );
    
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("is", $user_id, $operation);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row && $row['oldest_time']) {
        return $row['oldest_time'] + $window;
    }
    
    return time() + $window;
}

/**
 * Check if user is admin (super_admin or director)
 * Admins can bypass rate limits
 * 
 * @return bool
 */
function is_rate_limit_admin() {
    $role = $_SESSION['role'] ?? null;
    return $role === 'super_admin' || $role === 'director';
}

/**
 * Enforce rate limit with HTTP 429 response if exceeded
 * 
 * @param string $operation Operation name
 * @param int $user_id User ID
 * @param string $username Username
 * @param string $ip_address IP address
 * @return array Rate limit check result
 */
function enforce_rate_limit($operation, $user_id, $username, $ip_address) {
    // Admins can bypass rate limits
    if (is_rate_limit_admin()) {
        return [
            'allowed' => true,
            'message' => 'Admin - no rate limit',
            'remaining' => -1,
            'admin_override' => true
        ];
    }
    
    $result = check_rate_limit($operation, $user_id, $username, $ip_address);
    
    if (!$result['allowed']) {
        // Return 429 Too Many Requests
        http_response_code(429);
        
        // Log security event
        if (function_exists('log_security_event')) {
            log_security_event('rate_limit_exceeded', [
                'operation' => $operation,
                'user' => $username,
                'ip' => $ip_address
            ], 'MEDIUM');
        }
        
        // Return JSON error if AJAX request, otherwise plain text
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $result['message'],
                'reset_time' => $result['reset_time'] ?? null
            ]);
        } else {
            header('Content-Type: text/plain');
            echo $result['message'];
        }
        
        exit;
    }
    
    return $result;
}

/**
 * Get rate limit statistics for user
 * 
 * @param int $user_id User ID
 * @return array Statistics by operation
 */
function get_user_rate_limit_stats($user_id) {
    global $conn, $RATE_LIMITS;
    
    $stats = [];
    
    foreach (array_keys($RATE_LIMITS) as $operation) {
        $config = $RATE_LIMITS[$operation];
        $cutoff_time = time() - $config['window'];
        
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as attempts, SUM(data_volume) as total_volume FROM rate_limit_log 
             WHERE user_id = ? AND operation = ? AND timestamp > FROM_UNIXTIME(?) AND status = 'SUCCESS'"
        );
        
        if ($stmt) {
            $stmt->bind_param("isi", $user_id, $operation, $cutoff_time);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            $attempts = $row['attempts'] ?? 0;
            $remaining = max(0, $config['limit'] - $attempts);
            
            $stats[$operation] = [
                'attempts' => $attempts,
                'limit' => $config['limit'],
                'remaining' => $remaining,
                'total_records' => $row['total_volume'] ?? 0,
                'reset_time' => get_rate_limit_reset_time($user_id, $operation)
            ];
        }
    }
    
    return $stats;
}

/**
 * Cleanup old rate limit logs (older than 24 hours)
 * Run this periodically to prevent table bloat
 * 
 * @return int Number of records deleted
 */
function cleanup_old_rate_limits() {
    global $conn;
    
    $cutoff_time = time() - (24 * 3600);
    
    $stmt = $conn->prepare("DELETE FROM rate_limit_log WHERE timestamp < FROM_UNIXTIME(?)");
    if (!$stmt) {
        error_log("Rate limit cleanup failed: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("i", $cutoff_time);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    error_log("Rate limit cleanup: deleted $affected old records");
    return $affected;
}

?>
