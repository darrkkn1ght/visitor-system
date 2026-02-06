<?php
/**
 * Fix Missing must_change_password Column
 * This script adds the missing column and updates passwords with hashes
 */

include 'db.php';

echo "<h2>Database Schema Fix</h2>";

// Step 1: Add the missing column
echo "<h3>Step 1: Adding must_change_password column...</h3>";
$sql_add_column = "ALTER TABLE `users` ADD COLUMN `must_change_password` TINYINT(1) DEFAULT 1 AFTER `destination_id`";

try {
    if ($conn->query($sql_add_column) === TRUE) {
        echo "✅ Column added successfully<br>";
    } else {
        // Column might already exist, which is fine
        if (strpos($conn->error, 'Duplicate column') !== false) {
            echo "ℹ️ Column already exists (expected if migration ran before)<br>";
        } else {
            echo "❌ Error: " . $conn->error . "<br>";
        }
    }
} catch (Exception $e) {
    echo "⚠️ Exception: " . $e->getMessage() . "<br>";
}

// Step 2: Update passwords with bcrypt hashes
echo "<h3>Step 2: Updating user passwords with bcrypt hashes...</h3>";

$password_updates = [
    ['username' => 'director', 'hash' => '$2y$10$xJvvXrzAFyviXDzWwsglFuZJ5cgOXsADqI57zEZW/CFIfcuizHGHS'],
    ['username' => 'superadmin', 'hash' => '$2y$10$oDL4B..8fQ/STSKIFDpwrO/q5v7pZdEpY/W1mbcdADsXw/Ii9IxoS'],
    ['username' => 'reception', 'hash' => '$2y$10$Lgu2U7gxQ0rSygY./dWguOqAJ8zpR5XoV58j28IQJKFmofyjV3Fvy'],
    ['username' => 'gaminghub', 'hash' => '$2y$10$EuGlMc90zj2H5ZrH34HARuxQK2zW9jbt1aHAVO/KDnx1KjkhhX/Ia'],
    ['username' => 'nelfund', 'hash' => '$2y$10$MsFFqFCcwVHxXgr9hqg2LeMDpLwxehSbIBwvtPHaZ.AYiKQWYr/tC'],
    ['username' => 'directorates', 'hash' => '$2y$10$Z52oJ6nzJu7nv/Evp/NUCuR3nHyIv3SoCfSlGbzlIcYRUmK.hmGpW'],
    ['username' => 'itemsboardsecretariat', 'hash' => '$2y$10$nBlefCaqt29xiV1LTay9nOjpBxWWbC61b3sG0qLYGWD9Gnf5qtJaG'],
    ['username' => 'directorate_admin', 'hash' => '$2y$10$hd63uO0EcNHje4LL5lD1AOXABXa90tQ7ujUMsE6tWoxEBPbs9LVCK']
];

foreach ($password_updates as $update) {
    $username = $update['username'];
    $hash = $update['hash'];
    
    $stmt = $conn->prepare("UPDATE `users` SET `password` = ?, `must_change_password` = 1 WHERE `username` = ?");
    $stmt->bind_param("ss", $hash, $username);
    
    if ($stmt->execute()) {
        echo "✅ Updated: $username<br>";
    } else {
        echo "❌ Failed to update $username: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Step 3: Verify the setup
echo "<h3>Step 3: Verifying setup...</h3>";
$verify_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE must_change_password = 1");
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$verify_row = $verify_result->fetch_assoc();
$verify_stmt->close();

echo "✅ Users with must_change_password flag: " . $verify_row['count'] . "<br>";

echo "<h3>Summary</h3>";
echo "<p style='color: green; font-weight: bold;'>✅ Database schema has been fixed!</p>";
echo "<p>You can now:</p>";
echo "<ol>";
echo "<li>Try logging in with any of the 8 accounts using passwords from TEMP_PASSWORDS.txt</li>";
echo "<li>The system will redirect you to change_password.php on first login</li>";
echo "<li>After changing your password, you'll have access to the admin dashboard</li>";
echo "</ol>";

$conn->close();
?>
