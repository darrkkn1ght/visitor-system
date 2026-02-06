<?php
require_once __DIR__ . '/../security_headers.php';
require_once __DIR__ . '/../db.php';

// Turn off output buffering
if (ob_get_level())
    ob_end_clean();

echo "Starting Events Table Migration...\n";

// 1. Add new columns if they don't exist
$alter_statements = [
    "ADD COLUMN IF NOT EXISTS title VARCHAR(150)",
    "ADD COLUMN IF NOT EXISTS start_datetime DATETIME",
    "ADD COLUMN IF NOT EXISTS end_datetime DATETIME",
    "ADD COLUMN IF NOT EXISTS location VARCHAR(255)", // expanded size just in case
    "ADD COLUMN IF NOT EXISTS visibility ENUM('public','internal') DEFAULT 'internal'",
    "ADD COLUMN IF NOT EXISTS created_by INT",
    "ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($alter_statements as $sql) {
    // Check if column exists first to avoid syntax error in older MySQL/MariaDB which might not support IF NOT EXISTS in ALTER TABLE
    // Actually, MariaDB 10.0+ supports IF NOT EXISTS. Let's try direct execution, if fail, we catch.
    try {
        $conn->query("ALTER TABLE events $sql");
        echo "Executed: ALTER TABLE events $sql\n";
    } catch (Exception $e) {
        // Fallback: check schema manually if needed, but usually IF NOT EXISTS handles it or it throws "Duplicate column"
        echo "Notice: " . $e->getMessage() . "\n";
    }
}

// 2. Migrate Data
echo "Migrating data...\n";

// Update title
$conn->query("UPDATE events SET title = event_title WHERE (title IS NULL OR title = '') AND event_title IS NOT NULL");

// Update location
$conn->query("UPDATE events SET location = venue WHERE (location IS NULL OR location = '') AND venue IS NOT NULL");

// Update Times
// Morning: 09:00 - 12:00
$conn->query("UPDATE events SET 
    start_datetime = CONCAT(event_date, ' 09:00:00'), 
    end_datetime = CONCAT(event_date, ' 12:00:00') 
    WHERE time_slot = 'morning' AND start_datetime IS NULL");

// Afternoon: 12:00 - 17:00
$conn->query("UPDATE events SET 
    start_datetime = CONCAT(event_date, ' 12:00:00'), 
    end_datetime = CONCAT(event_date, ' 17:00:00') 
    WHERE time_slot = 'afternoon' AND start_datetime IS NULL");

// Full Day: 09:00 - 17:00
$conn->query("UPDATE events SET 
    start_datetime = CONCAT(event_date, ' 09:00:00'), 
    end_datetime = CONCAT(event_date, ' 17:00:00') 
    WHERE time_slot = 'fullday' AND start_datetime IS NULL");

echo "Migration completed.\n";
?>