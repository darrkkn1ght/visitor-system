-- ============================================================================
-- VISITOR UNAVAILABLE FLOW MIGRATION
-- Adds columns to support message mode and availability snapshots
-- Run this migration BEFORE deploying code changes
-- ============================================================================

-- Add message mode columns to visitors table
ALTER TABLE `visitors`
ADD COLUMN `visitor_message` TEXT DEFAULT NULL AFTER `purpose`,
ADD COLUMN `preferred_return` DATETIME DEFAULT NULL AFTER `visitor_message`,
ADD COLUMN `admin_id` INT DEFAULT NULL AFTER `preferred_return`,
ADD COLUMN `availability_snapshot` ENUM('available','busy','unavailable','away') DEFAULT NULL AFTER `admin_id`,
ADD COLUMN `status_message_snapshot` VARCHAR(255) DEFAULT NULL AFTER `availability_snapshot`,
ADD COLUMN `queue_status` ENUM('pending','resolved','scheduled') DEFAULT 'pending' AFTER `status_message_snapshot`,
ADD COLUMN `admin_notes` TEXT DEFAULT NULL AFTER `queue_status`,
ADD COLUMN `resolved_at` DATETIME DEFAULT NULL AFTER `admin_notes`;

-- Add index for queue queries
ALTER TABLE `visitors`
ADD INDEX `idx_queue_status` (`queue_status`),
ADD INDEX `idx_admin_id` (`admin_id`);

-- Add foreign key for admin_id (optional, for referential integrity)
ALTER TABLE `visitors`
ADD CONSTRAINT `fk_visitor_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;
