-- ============================================================================
-- Personal Admin Dashboard - Database Migration
-- ============================================================================
-- Description: Adds admin profile features, availability status tracking,
--              enhanced notifications, and password reset functionality
-- Date: 2026-01-29
-- ============================================================================

-- Add availability and profile fields to users table
ALTER TABLE users 
ADD COLUMN availability_status ENUM('available', 'busy', 'unavailable', 'away') DEFAULT 'available' AFTER must_change_password,
ADD COLUMN status_message VARCHAR(255) DEFAULT NULL AFTER availability_status,
ADD COLUMN last_status_change TIMESTAMP NULL AFTER status_message,
ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER last_status_change,
ADD COLUMN phone_number VARCHAR(20) DEFAULT NULL AFTER profile_photo,
ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER phone_number,
ADD COLUMN notification_preferences JSON DEFAULT NULL AFTER email;

-- Create index for availability queries
ALTER TABLE users ADD INDEX idx_availability (availability_status, destination_id);

-- ============================================================================
-- Admin Availability Log Table
-- ============================================================================
-- Tracks all availability status changes for audit and analytics
CREATE TABLE IF NOT EXISTS admin_availability_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  old_status ENUM('available', 'busy', 'unavailable', 'away') DEFAULT NULL,
  new_status ENUM('available', 'busy', 'unavailable', 'away') NOT NULL,
  status_message VARCHAR(255) DEFAULT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_date (user_id, changed_at),
  INDEX idx_status (new_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- Enhanced Notifications Table
-- ============================================================================
-- Add new columns to existing notifications table
ALTER TABLE notifications
ADD COLUMN notification_type ENUM('visitor_arrival', 'system', 'alert', 'checkout') DEFAULT 'visitor_arrival' AFTER is_read,
ADD COLUMN visitor_id INT DEFAULT NULL AFTER notification_type,
ADD COLUMN action_url VARCHAR(255) DEFAULT NULL AFTER visitor_id,
ADD COLUMN priority ENUM('low', 'normal', 'high') DEFAULT 'normal' AFTER action_url,
ADD COLUMN read_at TIMESTAMP NULL AFTER priority;

-- Add foreign key for visitor_id
ALTER TABLE notifications
ADD CONSTRAINT fk_notification_visitor 
FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL;

-- Add indexes for better query performance
ALTER TABLE notifications 
ADD INDEX idx_user_read (user_id, is_read),
ADD INDEX idx_type (notification_type),
ADD INDEX idx_created (created_at DESC);

-- ============================================================================
-- Password Reset Tokens Table
-- ============================================================================
-- Stores secure tokens for password reset functionality
CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_expires (expires_at),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- WebSocket Sessions Table (for real-time notifications)
-- ============================================================================
-- Tracks active WebSocket connections for push notifications
CREATE TABLE IF NOT EXISTS websocket_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  session_id VARCHAR(64) NOT NULL UNIQUE,
  connection_id VARCHAR(128) DEFAULT NULL,
  connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_ping TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_session (session_id),
  INDEX idx_last_ping (last_ping)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- Update existing data with default values
-- ============================================================================
-- Set all existing users to 'available' status
UPDATE users 
SET availability_status = 'available', 
    last_status_change = CURRENT_TIMESTAMP 
WHERE availability_status IS NULL;

-- ============================================================================
-- Migration Complete
-- ============================================================================
-- Verify tables created successfully
SELECT 'Migration completed successfully!' AS status;
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'visitor_db' 
AND TABLE_NAME IN ('users', 'admin_availability_log', 'notifications', 'password_reset_tokens', 'websocket_sessions');
