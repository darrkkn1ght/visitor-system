<?php
/**
 * filename_validation.php - Secure Filename Handling
 * 
 * Prevents null byte injection, directory traversal, and other filename-based attacks
 * Provides safe filename sanitization for downloads and exports
 */

/**
 * Validate filename for security
 * 
 * Checks for:
 * - Null bytes (\0)
 * - Directory traversal (../)
 * - Special characters that could cause issues
 * - Path separators
 * 
 * @param string $filename Filename to validate
 * @return bool True if filename is safe
 */
function validate_filename($filename) {
    // Check for null bytes
    if (strpos($filename, "\0") !== false) {
        error_log("Filename validation failed: null byte detected in '$filename'");
        return false;
    }
    
    // Check for directory traversal
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        error_log("Filename validation failed: directory traversal attempt in '$filename'");
        return false;
    }
    
    // Check for empty filename
    if (empty($filename) || strlen($filename) === 0) {
        error_log("Filename validation failed: empty filename");
        return false;
    }
    
    // Check filename length (reasonable limit)
    if (strlen($filename) > 255) {
        error_log("Filename validation failed: filename too long");
        return false;
    }
    
    // Check for control characters
    if (preg_match('/[\x00-\x1f]/', $filename)) {
        error_log("Filename validation failed: control characters in '$filename'");
        return false;
    }
    
    return true;
}

/**
 * Sanitize filename - remove unsafe characters
 * 
 * Converts filename to safe format by:
 * - Removing null bytes
 * - Removing directory traversal patterns
 * - Replacing special characters with underscores
 * - Allowing only alphanumeric, underscore, hyphen, and dot
 * 
 * @param string $filename Filename to sanitize
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove null bytes
    $filename = str_replace("\0", '', $filename);
    
    // Remove directory traversal patterns
    $filename = str_replace(['..', '/', '\\'], '_', $filename);
    
    // Remove leading/trailing dots and spaces
    $filename = trim($filename, '. ');
    
    // Replace any character that's not alphanumeric, underscore, hyphen, or dot with underscore
    // Pattern allows: a-z, A-Z, 0-9, _, -, .
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Remove multiple consecutive underscores/hyphens
    $filename = preg_replace('/[_-]{2,}/', '_', $filename);
    
    // If filename is empty after sanitization, use default
    if (empty($filename)) {
        $filename = 'file_' . time();
    }
    
    return $filename;
}

/**
 * Generate safe export filename with timestamp
 * 
 * Creates filename in format: export_YYYYMMDD_HHMMSS.ext
 * Prevents null byte injection and directory traversal
 * 
 * @param string $base_name Base name for file (e.g., 'visitors', 'events')
 * @param string $extension File extension (e.g., 'csv', 'pdf')
 * @param string $date_from Optional date filter start (format: YYYY-MM-DD)
 * @param string $date_to Optional date filter end (format: YYYY-MM-DD)
 * @return string Safe filename
 */
function generate_safe_export_filename($base_name, $extension, $date_from = null, $date_to = null) {
    // Sanitize base name
    $base_name = sanitize_filename($base_name);
    
    // Sanitize extension - only allow alphanumeric
    $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);
    if (empty($extension)) {
        $extension = 'csv';
    }
    
    // Build filename
    $timestamp = date('YmdHis');
    $filename = $base_name . '_' . $timestamp;
    
    // Add date range if provided
    if (!empty($date_from) && !empty($date_to)) {
        // Validate date format (YYYY-MM-DD)
        if (validate_date_format($date_from) && validate_date_format($date_to)) {
            // Use only numeric parts of date to avoid special chars
            $from_clean = preg_replace('/[^0-9]/', '', $date_from);
            $to_clean = preg_replace('/[^0-9]/', '', $date_to);
            $filename .= '_' . $from_clean . '_to_' . $to_clean;
        }
    }
    
    // Add extension
    $filename .= '.' . $extension;
    
    // Final validation
    if (!validate_filename($filename)) {
        // Fallback to safe default
        $filename = 'export_' . time() . '.' . $extension;
    }
    
    return $filename;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date Date string to validate
 * @return bool True if valid format
 */
function validate_date_format($date) {
    // Check format: YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    
    // Verify it's a valid date
    $parts = explode('-', $date);
    return checkdate($parts[1], $parts[2], $parts[0]);
}

/**
 * Validate CSV filename specifically
 * 
 * @param string $filename Filename to validate
 * @return bool True if safe
 */
function validate_csv_filename($filename) {
    // Must be valid filename
    if (!validate_filename($filename)) {
        return false;
    }
    
    // Must end with .csv
    if (!preg_match('/\.csv$/i', $filename)) {
        error_log("CSV filename validation failed: doesn't end with .csv");
        return false;
    }
    
    // Must be reasonable length (CSV files don't need long names)
    if (strlen($filename) > 100) {
        error_log("CSV filename validation failed: filename too long");
        return false;
    }
    
    return true;
}

/**
 * Safe header output for file download
 * 
 * Prevents null byte injection in headers
 * 
 * @param string $filename Filename for download
 * @param string $content_type MIME type (e.g., 'text/csv')
 * @return void Sets headers and exits
 */
function set_download_headers($filename, $content_type = 'application/octet-stream') {
    // Validate filename before using in headers
    if (!validate_filename($filename)) {
        error_log("Download header rejected: invalid filename '$filename'");
        http_response_code(400);
        die("Invalid filename");
    }
    
    // Sanitize content type
    $content_type = preg_replace('/[^a-zA-Z0-9\-\/]/', '', $content_type);
    if (empty($content_type)) {
        $content_type = 'application/octet-stream';
    }
    
    // Set headers with proper escaping
    header("Content-Type: $content_type");
    header("Content-Disposition: attachment; filename=\"" . str_replace('"', '', $filename) . "\"");
    header("Content-Length: " . strlen($content_type)); // This gets updated when content is ready
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

/**
 * Get safe filename from user input (e.g., from form field)
 * 
 * @param string $user_input Raw filename from user
 * @return string|null Validated and sanitized filename, or null if invalid
 */
function get_safe_filename($user_input) {
    if (empty($user_input)) {
        return null;
    }
    
    // Sanitize first
    $safe_name = sanitize_filename($user_input);
    
    // Validate
    if (validate_filename($safe_name)) {
        return $safe_name;
    }
    
    return null;
}

?>
