-- Migration: Create rate limiting table
-- Date: January 9, 2026
-- Purpose: Track export attempts per user per hour to prevent abuse
--          Enforce rate limits on resource-intensive operations

-- ============================================================
-- RATE LIMIT LOG TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `rate_limit_log` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique rate limit record ID',
    
    `user_id` INT(11) NOT NULL COMMENT 'User performing the operation',
    `username` VARCHAR(50) NOT NULL COMMENT 'Username for quick reference',
    `operation` VARCHAR(50) NOT NULL COMMENT 'Operation type: export_csv, export_data, backup_records',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'Source IP address',
    `data_volume` BIGINT(20) DEFAULT 0 COMMENT 'Number of records exported or processed',
    `status` VARCHAR(20) DEFAULT 'SUCCESS' COMMENT 'SUCCESS or BLOCKED (rate limit exceeded)',
    
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the operation occurred',
    
    INDEX `idx_user_op_time` (`user_id`, `operation`, `timestamp`),
    INDEX `idx_operation_time` (`operation`, `timestamp`),
    INDEX `idx_timestamp` (`timestamp`),
    INDEX `idx_user_time` (`user_id`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Rate limit tracking for export operations';

-- ============================================================
-- RATE LIMITS BY OPERATION (in rate_limiting.php)
-- ============================================================
-- CSV Export: 5 per hour
-- Data Export: 5 per hour
-- Backup Records: 3 per hour (more restrictive)

-- ============================================================
-- CLEANUP POLICY
-- ============================================================
-- Old records (>24 hours) should be deleted periodically
-- Example: Run cleanup_old_rate_limits() after each hour

-- ============================================================
-- VERIFICATION
-- ============================================================
SELECT 'rate_limit_log table created successfully' AS status;
SELECT COUNT(*) AS initial_record_count FROM `rate_limit_log`;
