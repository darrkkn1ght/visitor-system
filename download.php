<?php
require_once 'security_headers.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

include 'db.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$sql = "SELECT * FROM visitors ORDER BY DATE(time_in) DESC, time_in DESC LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dropdown {
      position: absolute;
      top: 15px;
      right: 20px;
      display: inline-block;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
    }
    .dropdown-content a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }
    .dropdown-content a:hover {background-color: #f1f1f1}
    .dropdown:hover .dropdown-content {
      display: block;
    }
  </style>
</head>
<body>
  <!-- New Simple Menu -->
<div class="simple-menu-container">
  <button class="menu-toggle" id="menuToggle">☰ Menu</button>
  <div class="menu-panel" id="menuPanel">
    <div class="menu-header">
      <strong>Navigation</strong>
      <button class="menu-close" id="menuClose">×</button>
    </div>
    <a href="admin_dashboard.php"><span class="menu-icon">Home</span> Dashboard</a>
    <a href="backup_records.php"><span class="menu-icon">Download</span> Daily Backups</a>
    <a href="reset.php"><span class="menu-icon">Refresh</span> Reset & Backup Today's Data</a>
    <a href="logout.php" class="logout-btn"><span class="menu-icon">Exit</span> Logout</a>
  </div>
</div>

  <div class="container">
    <h2>All Visitor Records</h2>
    <form method="get" style="margin-bottom: 10px;">
      Show
      <input type="number" name="limit" value="<?= $limit ?>" min="10" step="10" style="width: 60px;">
      entries
      <button type="submit">Apply</button>
    </form>

    <table>
      <tr>
        <th>Name</th>
        <th>Person to See</th>
        <th>Purpose</th>
        <th>Time In</th>
        <th>Time Out</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['person_to_see']) ?></td>
          <td><?= htmlspecialchars($row['purpose']) ?></td>
          <td><?= $row['time_in'] ?></td>
          <td><?= $row['time_out'] ?: '---' ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
  
  <script src="simple_menu.js"></script>
</body>
</html>
