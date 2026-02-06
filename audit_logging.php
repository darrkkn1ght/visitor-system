<?php
/**
 * audit_logging.php - Audit Logging System
 * 
 * Provides functions to log all admin actions, user changes, and security events
 * Audit logs are immutable once written for compliance and forensic analysis
 * 
 * Features:
 * - Track admin actions (create, update, delete)
 * - Log failed login attempts with IP
 * - Log user role changes and permission modifications
 * - JSON storage of before/after values
 * - IP address and user agent tracking
 * - Status and error message recording
 */

/**
 * Log an audit event to the database
 * 
 * @param string $action Action type (LOGIN, CREATE, UPDATE, DELETE, ROLE_CHANGE, etc)
 * @param string $table_name Database table affected
 * @param int $record_id ID of affected record
 * @param array $old_values Previous values (before change)
 * @param array $new_values New values (after change)
 * @param string $status SUCCESS or FAILURE
 * @param string $error_message Error message if failed
 * @return bool True on success, false on failure
 */
function log_audit_event($action, $table_name = null, $record_id = null, $old_values = [], $new_values = [], $status = 'SUCCESS', $error_message = null) {
    global $conn;
    
    // Get current user info from session
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'SYSTEM';
    $user_role = $_SESSION['role'] ?? 'UNKNOWN';
    
    // Get IP address (handle proxy scenarios)
    $ip_address = get_client_ip();
    
    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    // Get or create session ID
    $session_id = session_id();
    
    // Generate change summary
    $change_summary = generate_change_summary($action, $old_values, $new_values);
    
    // Convert arrays to JSON
    $old_values_json = !empty($old_values) ? json_encode($old_values) : null;
    $new_values_json = !empty($new_values) ? json_encode($new_values) : null;
    
    // Prepare statement
    $stmt = $conn->prepare("
        INSERT INTO audit_log (
            user_id, username, user_role, action, table_name, record_id,
            old_values, new_values, change_summary, ip_address, user_agent,
            session_id, status, error_message
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        error_log("Audit logging prepare error: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param(
        "issssissssssss", // Corrected type definition string
        $user_id, $username, $user_role, $action, $table_name, $record_id,
        $old_values_json, $new_values_json, $change_summary, $ip_address,
        $user_agent, $session_id, $status, $error_message
    );
    
    if (!$stmt->execute()) {
        error_log("Audit logging execution error: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $stmt->close();
    return true;
}

/**
 * Log a login attempt
 * 
 * @param string $username Username attempting to login
 * @param bool $success Whether login was successful
 * @param string $error_message Error message if login failed
 * @return bool True on success
 */
function log_login_attempt($username, $success = true, $error_message = null) {
    global $conn;
    
    $ip_address = get_client_ip();
    $action = $success ? 'LOGIN' : 'LOGIN_FAILED';
    $status = $success ? 'SUCCESS' : 'FAILURE';
    
    // Log to login_attempts table (for rate limiting)
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time, success) VALUES (?, ?, NOW(), ?)");
    if ($stmt) {
        $success_flag = $success ? 1 : 0;
        $stmt->bind_param("ssi", $username, $ip_address, $success_flag);
        $stmt->execute();
        $stmt->close();
    }
    
    // Log to audit_log table (for compliance)
    return log_audit_event(
        $action,
        'users',
        null,
        [],
        ['username' => $username],
        $status,
        $error_message
    );
}

/**
 * Log a user creation
 * 
 * @param int $user_id New user ID
 * @param array $user_data User data that was created
 * @return bool
 */
function log_user_creation($user_id, $user_data) {
    // Don't log password in audit trail
    $sanitized_data = $user_data;
    unset($sanitized_data['password']);
    
    return log_audit_event(
        'CREATE',
        'users',
        $user_id,
        [],
        $sanitized_data
    );
}

/**
 * Log a user modification
 * 
 * @param int $user_id User ID being modified
 * @param array $old_values Previous user data
 * @param array $new_values New user data
 * @return bool
 */
function log_user_modification($user_id, $old_values, $new_values) {
    // Don't log passwords
    unset($old_values['password']);
    unset($new_values['password']);
    
    // Track which fields changed
    $changes = [];
    foreach ($new_values as $key => $new_val) {
        if (isset($old_values[$key]) && $old_values[$key] !== $new_val) {
            $changes[$key] = [
                'old' => $old_values[$key],
                'new' => $new_val
            ];
        }
    }
    
    // Determine action (role change vs general update)
    $action = isset($changes['role']) ? 'ROLE_CHANGE' : 'UPDATE';
    
    return log_audit_event(
        $action,
        'users',
        $user_id,
        $old_values,
        $new_values
    );
}

/**
 * Log a user deletion
 * 
 * @param int $user_id User ID being deleted
 * @param array $user_data User data before deletion
 * @return bool
 */
function log_user_deletion($user_id, $user_data) {
    unset($user_data['password']);
    
    return log_audit_event(
        'DELETE',
        'users',
        $user_id,
        $user_data,
        []
    );
}

/**
 * Log a visitor creation/check-in
 * 
 * @param int $visitor_id Visitor ID
 * @param array $visitor_data Visitor data
 * @return bool
 */
function log_visitor_checkin($visitor_id, $visitor_data) {
    return log_audit_event(
        'VISITOR_CHECKIN',
        'visitors',
        $visitor_id,
        [],
        $visitor_data
    );
}

/**
 * Log a visitor checkout
 * 
 * @param int $visitor_id Visitor ID
 * @param string $time_out Checkout time
 * @return bool
 */
function log_visitor_checkout($visitor_id, $time_out) {
    return log_audit_event(
        'VISITOR_CHECKOUT',
        'visitors',
        $visitor_id,
        [],
        ['time_out' => $time_out]
    );
}

/**
 * Log a data export
 * 
 * @param string $export_type Type of export (csv, excel, pdf, etc)
 * @param int $record_count Number of records exported
 * @param array $filters Applied filters
 * @return bool
 */
function log_data_export($export_type, $record_count, $filters = []) {
    return log_audit_event(
        'EXPORT',
        'export',
        null,
        [],
        [
            'export_type' => $export_type,
            'record_count' => $record_count,
            'filters' => $filters
        ]
    );
}

/**
 * Log a database action (create, update, delete)
 * 
 * @param string $action Action type
 * @param string $table_name Table name
 * @param int $record_id Record ID
 * @param array $old_values Old values (for updates/deletes)
 * @param array $new_values New values (for creates/updates)
 * @return bool
 */
function log_database_action($action, $table_name, $record_id, $old_values = [], $new_values = []) {
    // Don't log sensitive data (passwords)
    if (!empty($old_values) && isset($old_values['password'])) {
        unset($old_values['password']);
    }
    if (!empty($new_values) && isset($new_values['password'])) {
        unset($new_values['password']);
    }
    
    return log_audit_event(
        $action,
        $table_name,
        $record_id,
        $old_values,
        $new_values
    );
}

/**
 * Log a security event (suspicious activity, policy violation, etc)
 * 
 * @param string $event_type Type of security event
 * @param array $details Event details
 * @param string $severity CRITICAL, HIGH, MEDIUM, LOW
 * @return bool
 */
function log_security_event($event_type, $details = [], $severity = 'MEDIUM') {
    return log_audit_event(
        'SECURITY_' . strtoupper($event_type),
        null,
        null,
        [],
        array_merge($details, ['severity' => $severity])
    );
}

/**
 * Get client IP address (handles proxy scenarios)
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    // Check for shared internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IP passed from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    // Check for remote address
    else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    
    // Sanitize IP address
    $ip = trim($ip);
    $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'INVALID_IP';
    
    return $ip;
}

/**
 * Generate human-readable change summary
 * 
 * @param string $action Action type
 * @param array $old_values Previous values
 * @param array $new_values New values
 * @return string Summary text
 */
function generate_change_summary($action, $old_values, $new_values) {
    switch ($action) {
        case 'CREATE':
            return "Created new record";
        case 'DELETE':
            return "Deleted record";
        case 'UPDATE':
            $changes = [];
            foreach ($new_values as $key => $val) {
                if (isset($old_values[$key]) && $old_values[$key] !== $val) {
                    $changes[] = "$key changed";
                }
            }
            return count($changes) > 0 ? "Updated: " . implode(", ", $changes) : "No changes";
        case 'ROLE_CHANGE':
            $old_role = $old_values['role'] ?? 'UNKNOWN';
            $new_role = $new_values['role'] ?? 'UNKNOWN';
            return "Role changed from '$old_role' to '$new_role'";
        case 'EXPORT':
            return "Exported " . ($new_values['record_count'] ?? 0) . " records as " . ($new_values['export_type'] ?? 'file');
        case 'LOGIN':
            return "User login successful";
        case 'LOGIN_FAILED':
            return "Login attempt failed";
        case 'VISITOR_CHECKIN':
            return "Visitor checked in";
        case 'VISITOR_CHECKOUT':
            return "Visitor checked out";
        default:
            return ucfirst(strtolower($action));
    }
}

/**
 * Retrieve audit logs with filtering
 * 
 * @param array $filters Filters: user_id, action, table_name, status, date_from, date_to
 * @param int $limit Number of records to return
 * @param int $offset Pagination offset
 * @return array Audit log records
 */
function get_audit_logs($filters = [], $limit = 100, $offset = 0) {
    global $conn;
    
    $where_parts = [];
    $params = [];
    $types = "";
    
    // Apply filters
    if (!empty($filters['user_id'])) {
        $where_parts[] = "user_id = ?";
        $params[] = $filters['user_id'];
        $types .= "i";
    }
    
    if (!empty($filters['action'])) {
        $where_parts[] = "action = ?";
        $params[] = $filters['action'];
        $types .= "s";
    }
    
    if (!empty($filters['table_name'])) {
        $where_parts[] = "table_name = ?";
        $params[] = $filters['table_name'];
        $types .= "s";
    }
    
    if (!empty($filters['status'])) {
        $where_parts[] = "status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    if (!empty($filters['date_from'])) {
        $where_parts[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $types .= "s";
    }
    
    if (!empty($filters['date_to'])) {
        $where_parts[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $types .= "s";
    }
    
    $where_clause = count($where_parts) > 0 ? "WHERE " . implode(" AND ", $where_parts) : "";
    
    $query = "SELECT * FROM audit_log $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Audit log query error: " . $conn->error);
        return [];
    }
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Bind parameters dynamically
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    $stmt->close();
    return $logs;
}

/**
 * Count audit log records with filters
 * 
 * @param array $filters Filters
 * @return int Total count
 */
function count_audit_logs($filters = []) {
    global $conn;
    
    $where_parts = [];
    $params = [];
    $types = "";
    
    // Apply same filters as get_audit_logs
    if (!empty($filters['user_id'])) {
        $where_parts[] = "user_id = ?";
        $params[] = $filters['user_id'];
        $types .= "i";
    }
    
    if (!empty($filters['action'])) {
        $where_parts[] = "action = ?";
        $params[] = $filters['action'];
        $types .= "s";
    }
    
    if (!empty($filters['table_name'])) {
        $where_parts[] = "table_name = ?";
        $params[] = $filters['table_name'];
        $types .= "s";
    }
    
    if (!empty($filters['status'])) {
        $where_parts[] = "status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    if (!empty($filters['date_from'])) {
        $where_parts[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $types .= "s";
    }
    
    if (!empty($filters['date_to'])) {
        $where_parts[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $types .= "s";
    }
    
    $where_clause = count($where_parts) > 0 ? "WHERE " . implode(" AND ", $where_parts) : "";
    $query = "SELECT COUNT(*) as total FROM audit_log $where_clause";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        return 0;
    }
    
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total'] ?? 0;
}

?>
