<?php
// confirmation.php
require_once 'security_headers.php';
include 'db.php';

if (!isset($_GET['token'])) {
    header("Location: index.php");
    exit();
}

$token = $_GET['token'];

// ============================================================
// TOKEN VALIDATION - Prevent IDOR attacks
// ============================================================
// 1. Validate token format: must be exactly 64 hex characters (from bin2hex(random_bytes(32)))
// This prevents enumeration attacks with predictable patterns
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    // Invalid token format - log and reject
    error_log("Invalid token format from IP: " . $_SERVER['REMOTE_ADDR'] . ", token: " . substr($token, 0, 10) . "...");
    header("Location: index.php");
    exit();
}

// Fetch visitor by token with expiration check
$sql = "SELECT v.fullname, d.name AS destination, v.keycard_id, v.is_alternate, k.card_number AS keycard, v.expires_at
FROM visitors v
JOIN destinations d ON v.destination = d.id
LEFT JOIN keycards k ON v.keycard_id = k.id
WHERE v.token = ? AND v.expires_at > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Token not found OR expired - use same generic message (prevent enumeration)
    error_log("Invalid or expired token access from IP: " . $_SERVER['REMOTE_ADDR']);
    header("Location: index.php");
    exit();
}

$visitor = $result->fetch_assoc();
$stmt->close();
$conn->close();

// convenience variables
$fullname =$visitor['fullname'];
$destination = $visitor['destination'];
$keycard = $visitor['keycard'];           // may be NULL/empty for alternate check-in
$is_alternate = (int)$visitor['is_alternate'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Check-In Confirmation</title>
  <link rel="stylesheet" href="confirmation.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <main class="confirmation-card" role="main" aria-labelledby="confirmTitle">
    <!-- Success Checkmark -->
    <div class="check-circle" aria-hidden="true">
      <svg class="check-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="none" stroke="#10B981" stroke-width="1.5" opacity="0.3"/>
        <path d="M20 6L9 17l-5-5" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <!-- Main Title -->
    <h1 id="confirmTitle">CHECK-IN SUCCESSFUL</h1>

    <!-- Subtitle -->
    <div class="subtitle">Thank you for checking in</div>

    <!-- Visitor Name -->
    <div class="visitor-info" aria-live="polite">
      <span class="label">Name</span>
      <div class="visitor-info-value"><?= htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') ?></div>
    </div>

    <!-- Destination -->
    <div class="visitor-info" aria-live="polite">
      <span class="label">Destination</span>
      <div class="visitor-info-value"><?= htmlspecialchars($destination, ENT_QUOTES, 'UTF-8') ?></div>
    </div>

    <!-- Keycard Section -->
    <?php if (!empty($keycard) && !$is_alternate): ?>
      <div class="keycard-section">
        <span class="keycard-label">Your Keycard Number</span>
        <div class="keycard-pill"><?= htmlspecialchars($keycard, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="note">📋 Please present this keycard to the receptionist to collect your access card.</div>
      </div>
    <?php else: ?>
      <div class="no-keycard-section">
        <span class="no-keycard">NO KEYCARD ASSIGNED</span>
        <div class="note">This was an alternate check-in. No keycard is required. Please see the receptionist for assistance.</div>
      </div>
    <?php endif; ?>

    <!-- Back Button -->
    <a href="index.php" class="back-btn" aria-label="Back to home">Back to Home</a>
  </main>
</body>
</html>
