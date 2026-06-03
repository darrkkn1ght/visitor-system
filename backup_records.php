<?php
// backup_records.php
?>
<!DOCTYPE html>
<html>
<head>
  <title>Backup and Restore Panel</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f6fa;
    }
    .container {
      padding: 20px;
      margin-top: 100px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    form label {
      display: block;
      margin: 10px 0 5px;
    }
    form input, form select {
      width: 90%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    form button {
      background-color: #1a73e8;
      color: white;
      padding: 10px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
    }
    form button:hover {
      background-color: #155ac6;
    }
    .dropdown {
      position: fixed;
      top: 15px;
      right: 20px;
      z-index: 1000;
    }
    .dropdown-button {
      padding: 10px 16px;
      font-size: 16px;
      background-color: #1a73e8;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: #ffffff;
      min-width: 180px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
      border-radius: 8px;
    }
    .dropdown-content a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }
    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
    .dropdown.show .dropdown-content {
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
    <a href="admin_dashboard.php"><span class="menu-icon">☰</span> Dashboard</a>
    <a href="events_calendar.php"><span class="menu-icon">◷</span> Events Calendar</a>
    <a href="backup_records.php"><span class="menu-icon">💾</span> Backup & Restore</a>
    <a href="logout.php" class="logout-btn"><span class="menu-icon">⎋</span> Logout</a>
  </div>
</div>

<div class="container">
  <h2 style="margin-bottom: 5px;">Backup and Restore Data</h2>
  <p style="text-align: center; color: #666; margin-bottom: 25px; font-size: 14px;">Securely backup and restore system records.</p>
  
  <div class="panel-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
    <h3 style="margin-top: 0; color: #333; font-size: 18px;">⬇ Export / Backup</h3>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Download a backup of visitor records within a selected date range.</p>
    <form action="export_data.php" method="POST">
      <label for="start_date">Start Date:</label>
      <input type="date" name="start_date" required>

      <label for="end_date">End Date:</label>
      <input type="date" name="end_date" required>

      <label for="format">Export Format:</label>
      <select name="format" required>
        <option value="csv">CSV (Spreadsheet)</option>
        <option value="json">JSON (System Format)</option>
      </select>

      <button type="submit">Download Backup</button>
    </form>
  </div>

  <div class="panel-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee; margin-top: 25px;">
    <h3 style="margin-top: 0; color: #333; font-size: 18px;">⬆ Restore from Backup</h3>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Restore visitor records from a previous backup file.</p>
    <form action="#" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); alert('Restore functionality access is limited to server environment administrators for security reasons.');">
      <label for="backup_file">Select Backup File (.csv or .json):</label>
      <input type="file" name="backup_file" accept=".csv, .json" required style="padding: 8px; background: white;">

      <button type="submit" style="background-color: #28a745; margin-top: 15px;">Restore Data</button>
    </form>
  </div>
</div>

<script src="simple_menu.js"></script>

</body>
</html>
