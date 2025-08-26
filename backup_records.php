<?php
// backup_records.php
?>
<!DOCTYPE html>
<html>
<head>
  <title>Export Visitor Records</title>
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

<div class="dropdown">
  <button class="dropdown-button">☰ Menu</button>
  <div class="dropdown-content">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="logout.php" style="color: red;">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Export Visitor Records</h2>
  <form action="export_data.php" method="POST">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" required>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" required>

    <label for="format">Export Format:</label>
    <select name="format" required>
      <option value="csv">CSV</option>
      <option value="json">JSON</option>
    </select>

    <button type="submit">Export Data</button>
  </form>
</div>

<script>
  document.querySelector('.dropdown-button').addEventListener('click', function (e) {
    e.stopPropagation();
    document.querySelector('.dropdown').classList.toggle('show');
  });

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.dropdown')) {
      document.querySelector('.dropdown').classList.remove('show');
    }
  });
</script>

</body>
</html>
