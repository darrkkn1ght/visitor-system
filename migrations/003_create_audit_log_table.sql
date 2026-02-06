-- Migration: Create audit logging system
-- Date: January 9, 2026
-- Purpose: Immutable audit log for all admin actions, user changes, and security events
--          Track login attempts, admin operations, and permission modifications

-- ============================================================
-- AUDIT LOG TABLE - IMMUTABLE RECORD
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique audit record ID',
    
    -- User & Session Information
    `user_id` INT(11) UNSIGNED COMMENT 'Admin user who performed the action (NULL for system)',
    `username` VARCHAR(50) COMMENT 'Username of the person who performed the action',
    `user_role` VARCHAR(50) COMMENT 'Role of user at time of action (director, superadmin, etc)',
    
    -- Action Information
    `action` VARCHAR(50) NOT NULL COMMENT 'Type of action: LOGIN, LOGIN_FAILED, CREATE, UPDATE, DELETE, ROLE_CHANGE, EXPORT, etc',
    `table_name` VARCHAR(100) COMMENT 'Database table affected (users, visitors, events, destinations)',
    `record_id` INT(11) UNSIGNED COMMENT 'ID of record affected',
    
    -- Change Details
    `old_values` LONGTEXT COMMENT 'JSON object with previous values before change',
    `new_values` LONGTEXT COMMENT 'JSON object with new values after change',
    `change_summary` TEXT COMMENT 'Human-readable summary of changes made',
    
    -- Security Information
    `ip_address` VARCHAR(45) COMMENT 'Source IP address of the request (IPv4 or IPv6)',
    `user_agent` VARCHAR(255) COMMENT 'User agent string for tracking device/browser',
    `session_id` VARCHAR(100) COMMENT 'Session ID for correlating multiple actions',
    
    -- Event Details
    `status` VARCHAR(20) DEFAULT 'SUCCESS' COMMENT 'SUCCESS, FAILURE, or other status',
    `error_message` TEXT COMMENT 'Error message if action failed',
    `details` LONGTEXT COMMENT 'Additional details in JSON format',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of action',
    `created_date` DATE GENERATED ALWAYS AS (DATE(created_at)) STORED COMMENT 'Date for efficient querying',
    
    -- Indexes for efficient querying
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_table_name` (`table_name`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_created_date` (`created_date`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_user_id_date` (`user_id`, `created_date`),
    INDEX `idx_action_table_date` (`action`, `table_name`, `created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Immutable audit log for compliance and security monitoring';

-- ============================================================
-- FAILED LOGIN ATTEMPTS TABLE (for brute-force tracking)
-- ============================================================
-- Already created in migration 001, but ensuring it exists
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `success` TINYINT(1) DEFAULT 0,
    INDEX `idx_username_time` (`username`, `attempt_time`),
    INDEX `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOG RETENTION POLICY
-- ============================================================
-- Note: In production, implement this as a scheduled job
-- Keep logs for minimum 1 year for compliance
-- Recommendation: Run monthly to archive logs older than 1 year

-- EXAMPLE: Archive old logs (run manually or as scheduled job)
-- INSERT INTO audit_log_archive SELECT * FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
-- DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- ============================================================
-- AUDIT LOG VIEWS (for convenient querying)
-- ============================================================

-- View for recent failed logins
CREATE OR REPLACE VIEW `vw_failed_logins` AS
SELECT 
    id,
    username,
    ip_address,
    attempt_time,
    DATE_FORMAT(attempt_time, '%Y-%m-%d %H:%i:%s') AS attempt_datetime,
    COUNT(*) OVER (PARTITION BY username, DATE(attempt_time) ORDER BY attempt_time) AS attempts_today
FROM `login_attempts`
ORDER BY attempt_time DESC;

-- View for admin actions (excluding read-only actions)
CREATE OR REPLACE VIEW `vw_admin_actions` AS
SELECT 
    id,
    user_id,
    username,
    action,
    table_name,
    record_id,
    change_summary,
    ip_address,
    status,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS action_datetime,
    DATEDIFF(NOW(), created_at) AS days_ago
FROM `audit_log`
WHERE action IN ('CREATE', 'UPDATE', 'DELETE', 'ROLE_CHANGE', 'EXPORT', 'IMPORT')
ORDER BY created_at DESC;

-- View for login events
CREATE OR REPLACE VIEW `vw_login_events` AS
SELECT 
    id,
    username,
    action,
    status,
    ip_address,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS login_datetime,
    error_message
FROM `audit_log`
WHERE action IN ('LOGIN', 'LOGIN_FAILED')
ORDER BY created_at DESC;

-- View for user modifications
CREATE OR REPLACE VIEW `vw_user_changes` AS
SELECT 
    id,
    user_id,
    username,
    action,
    record_id,
    old_values,
    new_values,
    change_summary,
    ip_address,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS modified_at
FROM `audit_log`
WHERE table_name = 'users'
ORDER BY created_at DESC;

-- ============================================================
-- VERIFICATION
-- ============================================================
-- Verify table creation
SELECT 'audit_log table created successfully' AS status;
SELECT COUNT(*) AS initial_record_count FROM `audit_log`;
