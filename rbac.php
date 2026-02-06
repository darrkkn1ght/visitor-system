<?php
/**
 * rbac.php - Role-Based Access Control (RBAC) System
 * 
 * Centralized permission checking for all roles
 * Defines role-permission matrix and provides functions for access control
 * 
 * Roles:
 * - super_admin: Full system access
 * - director: View/manage all visitors and events
 * - destination_admin: Manage specific destination only
 * - receptionist: Check-in visitors for their destination
 */

/**
 * Define permission matrix
 * Maps roles to allowed actions on resources
 */
$PERMISSIONS = [
    'super_admin' => [
        'users' => ['create', 'read', 'update', 'delete', 'role_change'],
        'visitors' => ['create', 'read', 'update', 'delete', 'checkin', 'checkout'],
        'events' => ['create', 'read', 'update', 'delete'],
        'destinations' => ['create', 'read', 'update', 'delete'],
        'reports' => ['view', 'export'],
        'audit' => ['view'],
        'admin' => ['access'],
        'settings' => ['manage'],
    ],
    'director' => [
        'users' => ['read'],
        'visitors' => ['create', 'read', 'update', 'checkin', 'checkout'],
        'events' => ['create', 'read', 'update'],
        'destinations' => ['read'],
        'reports' => ['view', 'export'],
        'audit' => ['view'],
        'admin' => ['access'],
        'settings' => [],
    ],
    'destination_admin' => [
        'users' => ['read'], // Can only see own destination users
        'visitors' => ['create', 'read', 'update', 'checkin', 'checkout'], // Own destination only
        'events' => ['create', 'read', 'update'], // Own destination only
        'destinations' => ['read'], // Own destination only
        'reports' => ['view', 'export'], // Own destination only
        'audit' => [], // Cannot view audit logs
        'admin' => [],
        'settings' => [],
    ],
    'receptionist' => [
        'users' => [],
        'visitors' => ['create', 'read', 'checkin', 'checkout'], // Own destination only
        'events' => ['read'],
        'destinations' => ['read'], // Own destination only
        'reports' => [],
        'audit' => [],
        'admin' => [],
        'settings' => [],
    ],
];

/**
 * Check if user has permission for an action on a resource
 * 
 * @param string $resource Resource type (users, visitors, events, etc)
 * @param string $action Action to perform (create, read, update, delete, etc)
 * @param int $destination_id Optional destination ID for destination-specific checks
 * @return bool True if user has permission
 */
function has_permission($resource, $action, $destination_id = null) {
    global $PERMISSIONS;
    
    // Check if user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    $role = $_SESSION['role'] ?? null;
    $user_destination_id = $_SESSION['destination_id'] ?? null;
    
    // Check if role exists in permissions matrix
    if (!isset($PERMISSIONS[$role])) {
        error_log("Unknown role in RBAC check: $role");
        return false;
    }
    
    // Check if resource exists
    if (!isset($PERMISSIONS[$role][$resource])) {
        error_log("Unknown resource in RBAC check: $resource");
        return false;
    }
    
    // Check if action is allowed for this role/resource
    if (!in_array($action, $PERMISSIONS[$role][$resource])) {
        return false;
    }
    
    // For destination-specific resources, check destination access
    // (unless user is super_admin or director)
    if ($destination_id !== null && $role !== 'super_admin' && $role !== 'director') {
        if ($user_destination_id !== $destination_id) {
            return false;
        }
    }
    
    return true;
}

/**
 * Assert that user has permission, or die
 * Use this at the start of functions that need permission checking
 * 
 * @param string $resource Resource type
 * @param string $action Action to perform
 * @param int $destination_id Optional destination ID
 * @return void Exits if permission denied
 */
function require_permission($resource, $action, $destination_id = null) {
    if (!has_permission($resource, $action, $destination_id)) {
        error_log("Permission denied: User {$_SESSION['username']} attempted $action on $resource");
        
        // Log security event
        if (function_exists('log_security_event')) {
            log_security_event(
                'unauthorized_access',
                [
                    'resource' => $resource,
                    'action' => $action,
                    'user' => $_SESSION['username'] ?? 'UNKNOWN',
                    'destination_id' => $destination_id
                ],
                'MEDIUM'
            );
        }
        
        die("Access denied. You do not have permission to perform this action.");
    }
}

/**
 * Check if user can manage users (create, update, delete)
 * Only super_admin can do this
 * 
 * @return bool
 */
function can_manage_users() {
    return has_permission('users', 'create');
}

/**
 * Check if user can manage events
 * 
 * @param int $destination_id Optional destination ID
 * @return bool
 */
function can_manage_events($destination_id = null) {
    return has_permission('events', 'create', $destination_id);
}

/**
 * Check if user can manage destinations
 * Only super_admin can
 * 
 * @return bool
 */
function can_manage_destinations() {
    return has_permission('destinations', 'create');
}

/**
 * Check if user can view audit logs
 * Only super_admin and director can
 * 
 * @return bool
 */
function can_view_audit_logs() {
    return has_permission('audit', 'view');
}

/**
 * Check if user can export reports
 * 
 * @param int $destination_id Optional destination ID
 * @return bool
 */
function can_export_reports($destination_id = null) {
    return has_permission('reports', 'export', $destination_id);
}

/**
 * Check if user can access admin panel
 * 
 * @return bool
 */
function can_access_admin() {
    return has_permission('admin', 'access');
}

/**
 * Get list of allowed actions for a resource
 * 
 * @param string $resource Resource type
 * @return array List of allowed actions
 */
function get_allowed_actions($resource) {
    global $PERMISSIONS;
    
    $role = $_SESSION['role'] ?? null;
    
    if (!isset($PERMISSIONS[$role][$resource])) {
        return [];
    }
    
    return $PERMISSIONS[$role][$resource];
}

/**
 * Get current user's role
 * 
 * @return string|null
 */
function get_user_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user is super_admin
 * 
 * @return bool
 */
function is_super_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

/**
 * Check if user is director
 * 
 * @return bool
 */
function is_director() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'director';
}

/**
 * Check if user is destination_admin
 * 
 * @return bool
 */
function is_destination_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'destination_admin';
}

/**
 * Check if user is receptionist
 * 
 * @return bool
 */
function is_receptionist() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'receptionist';
}

/**
 * Get permission matrix (for debugging/display)
 * 
 * @return array Full permission matrix
 */
function get_permission_matrix() {
    global $PERMISSIONS;
    return $PERMISSIONS;
}

/**
 * Log permission check (for audit trail)
 * 
 * @param string $resource Resource
 * @param string $action Action
 * @param bool $allowed Whether action was allowed
 * @return void
 */
function log_permission_check($resource, $action, $allowed) {
    if (!$allowed && function_exists('log_security_event')) {
        log_security_event(
            'permission_denied',
            [
                'resource' => $resource,
                'action' => $action,
                'user' => $_SESSION['username'] ?? 'UNKNOWN',
                'role' => $_SESSION['role'] ?? 'UNKNOWN'
            ],
            'LOW'
        );
    }
}

?>
