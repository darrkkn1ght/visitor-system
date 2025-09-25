<?php
// confirmation.php
include 'db.php';

if (!isset($_GET['token'])) {
    header("Location: index.php");
    exit();
}

$token = $_GET['token'];

// fetch visitor by token
$sql = "SELECT v.fullname, d.name AS destination, v.keycard_id, v.is_alternate, k.card_number AS keycard, v.expires_at
FROM visitors v
JOIN destinations d ON v.destination = d.id
LEFT JOIN keycards k ON v.keycard_id = k.id
WHERE v.token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // not found
    echo "<link rel='stylesheet' href='style.css'>";
    echo "<div style='max-width:600px;margin:40px auto;padding:20px;background:#fff;border-radius:8px;text-align:center;box-shadow:0 6px 18px rgba(0,0,0,0.08)'>Record not found.<br><a href='index.php' style='display:inline-block;margin-top:12px;padding:10px 16px;background:#1a73e8;color:#fff;border-radius:6px;text-decoration:none;'>Back</a></div>";
    exit();
}

$visitor = $result->fetch_assoc();
if (strtotime($visitor['expires_at']) < time()) {
    echo "<div class='err'>This confirmation link has expired.</div>";
    exit();
}
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
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Check-In Confirmation</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --accent-green: #4caf50;   /* lighter green for keycard */
      --accent-dark: #1b5e20;    /* darker green for glyph/text */
      --card-bg: #ffffff;
      --page-bg: #f6f8fb;
    }

    html,body{height:100%;margin:0;}
    body{
      font-family: 'Poppins', sans-serif;
      background: var(--page-bg);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:20px;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .confirmation-card{
      width:100%;
      max-width:720px;
      background:var(--card-bg);
      border-radius:12px;
      box-shadow:0 10px 30px rgba(10,30,60,0.08);
      padding:48px 48px;
      text-align:center;
    }

    .check-circle{
      width:96px;
      height:96px;
      border-radius:50%;
      margin:0 auto 18px;
      display:grid;
      place-items:center;
      background: linear-gradient(180deg, rgba(76,175,80,0.12), rgba(76,175,80,0.08));
      border: 2px solid rgba(76,175,80,0.18);
    }

    .check-svg {
      width:46px;
      height:46px;
      color: var(--accent-dark);
      font-weight:700;
    }

    h1 {
      margin:6px 0 12px;
      font-size:20px;
      font-weight:700;
      color:#263238;
    }

    .subtitle {
      font-size:15px;
      color:#4b5563;
      margin-bottom:18px;
    }

    .visitor-info {
      margin-top:8px;
      margin-bottom:18px;
      font-size:16px;
      color:#1f2937;
      letter-spacing:0.6px;
      text-transform:uppercase;            /* MAKE IT ALL CAPITALIZED */
      font-weight:600;
    }

    .visitor-info .label {
      display:block;
      font-size:12px;
      color:#6b7280;
      text-transform:none;
      font-weight:500;
      margin-bottom:6px;
    }

    .keycard-pill {
      display:inline-block;
      padding:12px 26px;
      border-radius:10px;
      background: var(--accent-green);
      color: #fff;
      font-weight:700;
      font-size:18px;
      letter-spacing:2px;
      text-transform:uppercase;            /* MAKE IT ALL CAPITALIZED */
      margin-top:10px;
    }

    .no-keycard {
      display:inline-block;
      padding:10px 18px;
      border-radius:8px;
      background:#e6f4ea;
      color:var(--accent-dark);
      font-weight:600;
      font-size:15px;
      text-transform:uppercase;
    }

    .note {
      margin-top:18px;
      color:#54606a;
      font-size:14px;
    }

    .back-btn {
      display:inline-block;
      margin-top:24px;
      padding:12px 22px;
      background:#1a73e8;
      color:#fff;
      border-radius:8px;
      text-decoration:none;
      font-weight:600;
    }
    .back-btn:active{transform:translateY(1px);}

    /* responsive tweaks */
    @media (max-width:480px){
      .confirmation-card{padding:28px 18px;}
      .check-circle{width:78px;height:78px;}
      .check-svg{width:36px;height:36px;}
      .keycard-pill{font-size:16px;padding:10px 20px;}
      .visitor-info{font-size:14px;}
      .back-btn{width:100%;display:block;}
    }
  </style>
</head>
<body>
  <main class="confirmation-card" role="main" aria-labelledby="confirmTitle">
    <div class="check-circle" aria-hidden="true">
      <!-- SVG check -->
      <svg class="check-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#ffffff" opacity="0.0"/>
        <path d="M20 6L9 17l-5-5" stroke="#1b5e20" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <h1 id="confirmTitle">CHECK-IN SUCCESSFUL</h1>

    <div class="subtitle">Thank you for checking in</div>

    <div class="visitor-info" aria-live="polite">
      <span class="label">Name</span>
      <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="visitor-info" aria-live="polite">
      <span class="label">Destination</span>
      <?php echo htmlspecialchars($destination, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <?php if (!empty($keycard) && !$is_alternate): ?>
      <div>
        <div class="label" style="font-size:12px;color:#6b7280;margin-bottom:8px;text-transform:none;">Your Keycard</div>
        <span class="keycard-pill"><?php echo htmlspecialchars($keycard, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="note">Please present this keycard to the receptionist to collect your card.</div>
    <?php else: ?>
      <div style="margin-top:12px;">
        <span class="no-keycard">NO KEYCARD ASSIGNED</span>
      </div>
      <div class="note">This was an alternate check-in; no keycard is required.</div>
    <?php endif; ?>

    <a href="index.php" class="back-btn" aria-label="Back to home">Back to Home</a>
  </main>
</body>
</html>
