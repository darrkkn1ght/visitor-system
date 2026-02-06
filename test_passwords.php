<?php
/**
 * TEST PASSWORD VERIFICATION
 * This script tests if the temporary passwords work correctly
 */

include 'db.php';

// Test credentials
$test_users = [
    ['username' => 'director', 'password' => 'UGyR0ly&eo@&KFf#'],
    ['username' => 'superadmin', 'password' => 'GP!N2n$j!G1tCcCz'],
    ['username' => 'reception', 'password' => '2d%aD$kw2ZoYU@fp'],
    ['username' => 'gaminghub', 'password' => '*dG4Zcqa&#c1qv8S'],
    ['username' => 'nelfund', 'password' => 'otPz4f9*rhc7&!X4'],
    ['username' => 'directorates', 'password' => 'Rw7mY1o%gP$u9EWF'],
    ['username' => 'itemsboardsecretariat', 'password' => 'M#3v3dhmbnMM3*mM'],
    ['username' => 'directorate_admin', 'password' => '0C8n#XO9CGdjt*3U']
];

echo "<h2>Password Verification Test Results</h2>";
echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background-color: #f0f0f0;'>
    <th style='padding: 10px;'>Username</th>
    <th style='padding: 10px;'>User Exists</th>
    <th style='padding: 10px;'>Password Hash in DB</th>
    <th style='padding: 10px;'>Password Verification</th>
    <th style='padding: 10px;'>Result</th>
</tr>";

foreach ($test_users as $test) {
    $username = $test['username'];
    $password = $test['password'];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_exists = "✅ YES (ID: " . $user['id'] . ")";
        $hash_stored = substr($user['password'], 0, 20) . "...";
        
        // Verify password
        $password_valid = password_verify($password, $user['password']);
        $verification = $password_valid ? "✅ PASS" : "❌ FAIL";
        $status = $password_valid ? "<span style='color: green; font-weight: bold;'>✅ WORKING</span>" : "<span style='color: red; font-weight: bold;'>❌ NOT WORKING</span>";
    } else {
        $user_exists = "❌ NO";
        $hash_stored = "N/A";
        $verification = "N/A";
        $status = "<span style='color: orange; font-weight: bold;'>⚠️ USER NOT FOUND</span>";
    }
    
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$username</strong></td>";
    echo "<td style='padding: 10px;'>$user_exists</td>";
    echo "<td style='padding: 10px; font-family: monospace; font-size: 12px;'>$hash_stored</td>";
    echo "<td style='padding: 10px;'>$verification</td>";
    echo "<td style='padding: 10px;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Summary
echo "<h3>Summary</h3>";
$all_pass = $result->num_rows > 0 ? "All passwords should be working if setup is complete." : "Some users not found or passwords don't match.";
echo "<p>$all_pass</p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If any users show ❌ USER NOT FOUND: Run the migration scripts to create users with temp passwords</li>";
echo "<li>If passwords show ❌ NOT WORKING: The hash in the database doesn't match the temp password</li>";
echo "<li>Try logging in at <a href='admin_login.php'>admin_login.php</a> with one of the tested credentials</li>";
echo "</ol>";

$conn->close();
?>
