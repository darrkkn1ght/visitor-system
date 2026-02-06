-- Migration: Create login_attempts table for brute-force protection
-- Date: January 9, 2026
-- Purpose: Track failed login attempts and implement lockout mechanism

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 address',
  `success` TINYINT(1) DEFAULT 0 COMMENT '1 for successful login, 0 for failed',
  `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the attempt was made',
  INDEX `idx_username_time` (`username`, `attempt_time`),
  INDEX `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks login attempts for brute-force protection';

-- Optional: Clean up old attempts (older than 30 days)
-- This can be run periodically via cron job
-- DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
