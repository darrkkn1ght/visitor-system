<?php
// index.php - Visitor input + log view
include 'db.php';

// Fetch destinations dynamically
$destinations = [];
$sql = "SELECT id, name FROM destinations ORDER BY name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Visitor Check-In</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- START Autocomplete Styles -->
  <style>
    .autocomplete-container {
      position: relative;
      width: 100%;
    }

    .suggestion-box {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 1000;
      background: #fff;
      border: 1px solid #ccc;
      border-top: none;
      max-height: 160px;
      overflow-y: auto;
      display: none;
      border-radius: 0 0 6px 6px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .suggestion-item {
      padding: 10px;
      cursor: pointer;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }

    .suggestion-item:hover {
      background: #f0f0f0;
    }
  </style>
  <!-- END Autocomplete Styles -->

</head>

<body>

  <div style="position: absolute; top: 10px; right: 20px;">
    <button onclick="toggleAdminMenu()"
      style="background: none; border: none; cursor: pointer; font-size: 16px; color: white; font-weight: bold;">☰</button>
    <div id="adminMenu"
      style="display: none; position: absolute; right: 0; top: 30px; background-color: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25); min-width: 150px;">
      <a href="admin_login.php"
        style="display: block; color: #1a73e8; text-decoration: none; margin-bottom: 5px; font-weight: bold;">Admin
        Login</a>

    </div>
  </div>
  <script>
    function toggleAdminMenu() {
      const menu = document.getElementById('adminMenu');
      menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
    }

    document.addEventListener('click', function (e) {
      const menu = document.getElementById('adminMenu');
      const button = e.target.closest('button');
      if (!menu.contains(e.target) && (!button || button.textContent.trim() !== '☰')) {
        menu.style.display = 'none';
      }
    });
  </script>



  <!-- <div style="position: absolute; top: 10px; left: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div> -->




  <div class="container">
    <?php if (isset($_GET['msg']) && $_GET['msg']): ?>
      <div id="submission-message"
        style="background:#e6f4ea;color:#1b5e20;padding:14px 18px;margin-bottom:18px;border-radius:8px;font-weight:600;font-size:15px;text-align:center;">
        <?= htmlspecialchars($_GET['msg']) ?>
      </div>
      <script>
        setTimeout(function() {
          var msg = document.getElementById('submission-message');
          if (msg) {
            msg.classList.add('fade-out');
            setTimeout(function() {
              msg.style.display = 'none';
              // Optionally, remove the msg param from the URL without reloading:
              if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('msg');
                window.history.replaceState({}, document.title, url.pathname + url.search);
              }
            }, 700); // Match the transition duration
          }
        }, 4000); // 4 seconds before fade starts
      </script>
    <?php endif; ?>

    <form class="registration-form" action="submit.php" method="POST">
      <h2>VISITOR CHECK-IN</h2>
      <div class="user details">


        <div class="form-row">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" placeholder="Enter Your Full Name" required>
          </div>
          <div class="form-group">
            
          </div>
        </div>




        <!-- Row 2 -->
        <div class="form-row">
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone_number" placeholder="Enter Your Phone Number" required pattern="[0-9]{7,15}"
              title="Enter a valid phone number (numbers only, 7–15 digits)" oninput="validatePhone(this)">
          </div>
          <div class="form-group">
          <label>Destination</label>
            <select name="destination" id="destination" required>
              <option value="">Select Destination</option>
              <?php foreach ($destinations as $dest): ?>
                <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['name']) ?></option>
              <?php endforeach; ?>
            </select>

          </div>
        </div>


        <!-- Row 3 -->
        <div class="form-row">
          <!-- START Autocomplete Faculty -->
          <div class="form-group autocomplete-container">
            <label>Faculty/Organization</label>
            <input type="text" id="faculty" name="faculty" placeholder="Faculty / Organization" autocomplete="off"
              required>
            <div id="facultySuggestions" class="suggestion-box"></div>
          </div>
          <!-- END Autocomplete Faculty -->


          <div class="form-group">
            <label for="purpose">Purpose of Visit / Leave a Message</label>
            <textarea name="purpose" id="purpose" rows="4" required></textarea>
          </div>
        </div>


        <!-- Row 4 -->
        <!-- Visitor Type -->
        <div class="form-group visitor-type-group">
          <label><strong>Visitor Type</strong></label>
          <div class="visitor-type-options">
            <label><input type="radio" name="visitor_type" value="staff" required> Staff </label>
            <label><input type="radio" name="visitor_type" value="student" required> Student </label>
            <label><input type="radio" name="visitor_type" value="guest" required> Guest </label>
          </div>
        </div>


        <button type="submit" class="btn" name="checkin_type" value="normal">Check In</button>

        <!-- Divider -->
        <hr style="margin: 20px 0; border: 1px solid #ccc;">

        <!-- Guidance text -->
        <p style="color: #444; font-size: 14px; margin-bottom: 8px;">
          <strong>Leave a Message:</strong> Only use this if the department head is unavailable at the time of your
          visit.
        </p>

        <!-- Alternate Check-In button -->
        <button type="submit" class="alternate-btn" name="checkin_type" value="alternate"
          onclick="return confirmAlternate()">
          Leave a Message
        </button>
    </form>
  </div>
  </div>




  <script>
    function confirmAlternate() {
      return confirm("Are you sure you want to proceed with Alternate Check-In?");
    }


  </script>

  <!-- START Autocomplete Script -->
  <script>
    (function () {
      const facultyInput = document.getElementById('faculty');
      const suggestionBox = document.getElementById('facultySuggestions');

      // You can extend this list
      const facultyList = [
        'Faculty of Engineering',
        'Faculty of Veterinary Medicine',
        'Faculty of The Social Sciences',
        'Faculty of Technology',
        'Faculty of Renewable Natural Resources',
        'Faculty of Public Health',
        'Faculty of Pharmacy',
        'Faculty ofNursing',
        'Faculty of Multidisciplinary Studies',
        'Faculty of Law',
        'Faculty of Environmental Design and Management',
        'Faculty of Education',
        'Faculty of Economics and Management Sciences',
        'Faculty of Dentistry',
        'Faculty of Computing',
        'Faculty of Clinical Sciences',
        'Faculty of Basic Medical Sciences',
        'Faculty of Basic Clinical Sciences',
        'Faculty of Arts',
        'Faculty of Agriculture',
      ];

      function render(list) {
        suggestionBox.innerHTML = '';
        list.forEach(item => {
          const div = document.createElement('div');
          div.className = 'suggestion-item';
          div.textContent = item;
          div.addEventListener('click', () => {
            facultyInput.value = item;
            suggestionBox.style.display = 'none';
          });
          suggestionBox.appendChild(div);
        });
        suggestionBox.style.display = list.length ? 'block' : 'none';
      }

      facultyInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        if (!q) { suggestionBox.style.display = 'none'; return; }
        const matches = facultyList.filter(v => v.toLowerCase().includes(q));
        render(matches);
      });

      // Show all on focus if field has some text; optional: show top items
      facultyInput.addEventListener('focus', function () {
        const q = this.value.toLowerCase().trim();
        const matches = q ? facultyList.filter(v => v.toLowerCase().includes(q)) : facultyList.slice(0, 0);
        render(matches);
      });

      document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-container')) {
          suggestionBox.style.display = 'none';
        }
      });
    })();


    // Phone number validation
    function validatePhone(input) {
      input.value = input.value.replace(/[^0-9]/g, '');
    }

  </script>
  <!-- END Autocomplete Script -->






</body>

</html>