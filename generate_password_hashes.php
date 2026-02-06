<?php
/**
 * generate_password_hashes.php
 * 
 * Generates bcrypt hashes for temporary passwords
 * Run this once to generate hashes, then use them in the migration
 * 
 * Usage: PHP CLI
 * php generate_password_hashes.php
 */

// Temporary passwords generated
$temp_passwords = [
    'director' => 'UGyR0ly&eo@&KFf#',
    'superadmin' => 'GP!N2n$j!G1tCcCz',
    'reception' => '2d%aD$kw2ZoYU@fp',
    'gaminghub' => '*dG4Zcqa&#c1qv8S',
    'nelfund' => 'otPz4f9*rhc7&!X4',
    'directorates' => 'Rw7mY1o%gP$u9EWF',
    'itemsboardsecretariat' => 'M#3v3dhmbnMM3*mM',
    'directorate_admin' => '0C8n#XO9CGdjt*3U'
];

echo "=== TEMPORARY PASSWORD HASHES ===\n";
echo "Copy these hashes into the migration SQL file\n\n";

foreach ($temp_passwords as $user => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "User: $user\n";
    echo "Temp Password: $password\n";
    echo "Bcrypt Hash: $hash\n";
    echo "SQL: UPDATE users SET password = '$hash' WHERE username = '$user';\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\n=== PASSWORD DOCUMENTATION ===\n";
echo "Save these temporary passwords securely (e.g., email to users):\n\n";

foreach ($temp_passwords as $user => $password) {
    echo "$user: $password\n";
}

echo "\nUsers MUST change these temporary passwords on first login.\n";
?>
