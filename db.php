<?php
/********************************************/
/* 2. db.php - Database connection file     */
/********************************************/

// Load .env file if it exists
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        // Skip comments and empty lines
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove surrounding quotes if present
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            // Only set if not already set by actual environment
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Load credentials from environment variables with safe fallbacks
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: 3306;
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'visitor_db';

// Establish database connection with port
//$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    // Log the detailed error server-side for debugging
    error_log("Database connection failed: " . $conn->connect_error);
    
    // Display generic error to user (do NOT expose database details)
    die("Database connection error. Please try again later.");
}