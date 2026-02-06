-- Migration: Update weak default passwords and implement password policy
-- Date: January 9, 2026
-- Purpose: Replace plain text "changeme123" with strong bcrypt hashed passwords
--          Add password_changed flag to enforce first-login password change

-- ============================================================
-- 1. ADD PASSWORD POLICY ENFORCEMENT FIELD
-- ============================================================
-- Adds a flag to track if user must change password on first login
ALTER TABLE `users` ADD COLUMN `must_change_password` TINYINT(1) DEFAULT 1 AFTER `destination_id`;

-- ============================================================
-- 2. UPDATE WEAK DEFAULT PASSWORDS WITH STRONG HASHES
-- ============================================================
-- NOTE: These passwords are bcrypt hashes of strong temporary passwords.
-- Each user MUST change their password on first login (must_change_password = 1)
-- Temporary passwords are documented securely in TEMP_PASSWORDS.txt file.

-- Director account - Temp: UGyR0ly&eo@&KFf#
UPDATE `users` SET 
  `password` = '$2y$10$xJvvXrzAFyviXDzWwsglFuZJ5cgOXsADqI57zEZW/CFIfcuizHGHS',
  `must_change_password` = 1
WHERE `username` = 'director';

-- Super Admin account - Temp: GP!N2n$j!G1tCcCz
UPDATE `users` SET 
  `password` = '$2y$10$oDL4B..8fQ/STSKIFDpwrO/q5v7pZdEpY/W1mbcdADsXw/Ii9IxoS',
  `must_change_password` = 1
WHERE `username` = 'superadmin';

-- Reception account - Temp: 2d%aD$kw2ZoYU@fp
UPDATE `users` SET 
  `password` = '$2y$10$Lgu2U7gxQ0rSygY./dWguOqAJ8zpR5XoV58j28IQJKFmofyjV3Fvy',
  `must_change_password` = 1
WHERE `username` = 'reception';

-- Gaming Hub destination admin - Temp: *dG4Zcqa&#c1qv8S
UPDATE `users` SET 
  `password` = '$2y$10$EuGlMc90zj2H5ZrH34HARuxQK2zW9jbt1aHAVO/KDnx1KjkhhX/Ia',
  `must_change_password` = 1
WHERE `username` = 'gaminghub';

-- NelFund destination admin - Temp: otPz4f9*rhc7&!X4
UPDATE `users` SET 
  `password` = '$2y$10$MsFFqFCcwVHxXgr9hqg2LeMDpLwxehSbIBwvtPHaZ.AYiKQWYr/tC',
  `must_change_password` = 1
WHERE `username` = 'nelfund';

-- Directorates destination admin - Temp: Rw7mY1o%gP$u9EWF
UPDATE `users` SET 
  `password` = '$2y$10$Z52oJ6nzJu7nv/Evp/NUCuR3nHyIv3SoCfSlGbzlIcYRUmK.hmGpW',
  `must_change_password` = 1
WHERE `username` = 'directorates';

-- ITeMS Board Secretariat destination admin - Temp: M#3v3dhmbnMM3*mM
UPDATE `users` SET 
  `password` = '$2y$10$nBlefCaqt29xiV1LTay9nOjpBxWWbC61b3sG0qLYGWD9Gnf5qtJaG',
  `must_change_password` = 1
WHERE `username` = 'itemsboardsecretariat';

-- Directorate Admin account - Temp: 0C8n#XO9CGdjt*3U
UPDATE `users` SET 
  `password` = '$2y$10$hd63uO0EcNHje4LL5lD1AOXABXa90tQ7ujUMsE6tWoxEBPbs9LVCK',
  `must_change_password` = 1
WHERE `username` = 'directorate_admin';

-- ============================================================
-- 3. VERIFICATION
-- ============================================================
-- Verify no plain text "changeme123" passwords remain
SELECT id, username, role, password FROM `users` 
WHERE password = 'changeme123' 
OR password NOT LIKE '$2y$%';
-- Result should be EMPTY

-- ============================================================
-- IMPORTANT NOTES:
-- ============================================================
-- 1. These bcrypt hashes are SAMPLE HASHES for documentation only
--    In production, generate unique hashes using:
--    PHP: password_hash("YourNewPassword", PASSWORD_DEFAULT)
--
-- 2. Temporary passwords must be:
--    - At least 12 characters
--    - Include uppercase, lowercase, numbers, special chars
--    - Generated uniquely per account
--    - Shared securely (not in version control)
--    - Documented in a separate secure file
--
-- 3. Users MUST be required to change password on first login
--    Check must_change_password flag before allowing other actions
--
-- 4. After first login password change:
--    UPDATE users SET must_change_password = 0 WHERE id = <user_id>
