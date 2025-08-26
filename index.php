<?php
// index.php - Visitor input + log view
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
    .autocomplete-container { position: relative; width: 100%; }
    .suggestion-box {
      position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
      background: #fff; border: 1px solid #ccc; border-top: none;
      max-height: 160px; overflow-y: auto; display: none; border-radius: 0 0 6px 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .suggestion-item { padding: 10px; cursor: pointer; font-size: 14px; font-family: 'Poppins', sans-serif; }
    .suggestion-item:hover { background: #f0f0f0; }
  </style>
  <!-- END Autocomplete Styles -->

</head>
<body>
 
  <div style="position: absolute; top: 10px; right: 20px;">
    <button onclick="toggleAdminMenu()" style="background: none; border: none; cursor: pointer; font-size: 16px; color: white; font-weight: bold;">☰</button>
    <div id="adminMenu" style="display: none; position: absolute; right: 0; top: 30px; background-color: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25); min-width: 150px;">
      <a href="admin_login.php" style="display: block; color: #1a73e8; text-decoration: none; margin-bottom: 5px; font-weight: bold;">Admin Login</a>
      
    </div>
  </div>
<script>
  function toggleAdminMenu() {
    const menu = document.getElementById('adminMenu');
    menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
  }

  document.addEventListener('click', function(e) {
    const menu = document.getElementById('adminMenu');
    const button = e.target.closest('button');
    if (!menu.contains(e.target) && (!button || button.textContent.trim() !== '☰')) {
      menu.style.display = 'none';
    }
  });
</script>



  <div style="position: absolute; top: 10px; left: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div>




  <div class="container">
    <form class="registration-form" action="submit.php" method="POST">
    <h2>VISITOR CHECK-IN</h2>
    <div class="user details">

    
  <div class="form-row">
  <div class="form-group">
    <label>Full Name</label>
        <input type="text" name="fullname" placeholder="Enter your name full Name" required>
     </div>
        <div class="form-group">
           <label>Destination</label>
        <select name="destination" id="destination" required>
          <option value="">Select Destination</option>
          <option value="Director's Office">Director's Office</option>
          <option value="ITeMS Board Secretariat">ITeMS Board Secretariat</option>
          <option value="Nelfund Support">Nelfund Support</option>
          <option value="Workshop/Seminar">Workshop/Seminar</option>
          <option value="Other">Other (Specify Below)</option>
        </select>
      </div>
      </div>


       
  
<!-- Row 2 -->
<div class="form-row">
<div class="form-group">
   <label>Phone Number</label>
<input type="tel" name="phone_number" placeholder="Enter Your Phone Number"
       required pattern="[0-9]{7,15}"
       title="Enter a valid phone number (numbers only, 7–15 digits)"
       oninput="validatePhone(this)">
  </div>  
<div class="form-group">
   <label>If Other</label>
        <input type="text" id="other_destination" name="other_destination" placeholder="If 'Other', please specify">
      </div>
      </div>
  

      <!-- Row 3 -->
<div class="form-row">
  <div class="form-group">
   <!-- START Autocomplete Faculty -->
<div class="form-group autocomplete-container">
  <label>Faculty/Organization</label>
  <input type="text" id="faculty" name="faculty" placeholder="Faculty / Organization" autocomplete="off" required>
  <div id="facultySuggestions" class="suggestion-box"></div>
</div>
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
    <label>Visitor Type</label>
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
    <strong>Alternate Check-In:</strong> Only use this if the department head is unavailable at the time of your visit.
</p>

<!-- Alternate Check-In button -->
<button type="submit" class="alternate-btn" name="checkin_type" value="alternate">
    Alternate Check-In
</button>
    </form>
  </div>
</div>
  



<script>
function confirmAlternate() {
    if (confirm("Are you sure you want to proceed with Alternate Check-In?")) {
        // Placeholder for future alternate check-in logic
    }
}
</script>

 <!-- START Autocomplete Script -->
  <script>
    (function() {
      const facultyInput = document.getElementById('faculty');
      const suggestionBox = document.getElementById('facultySuggestions');

      // You can extend this list
      const facultyList = [
        'Faculty of Engineering',
        'Faculty of Science',
        'Faculty of Arts',
        'Faculty of Education',
        'Faculty of Law',
        'Faculty of Social Sciences',
        'Information Technology Services',
        'Human Resources',
        'Finance Department'
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

      facultyInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        if (!q) { suggestionBox.style.display = 'none'; return; }
        const matches = facultyList.filter(v => v.toLowerCase().includes(q));
        render(matches);
      });

      // Show all on focus if field has some text; optional: show top items
      facultyInput.addEventListener('focus', function() {
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

    document.getElementById('destination').addEventListener('change', function () {
  const otherInput = document.getElementById('other_destination');
  
  if (this.value === 'Other') {
    otherInput.setAttribute('required', 'required'); // make required
  } else {
    otherInput.removeAttribute('required'); // remove required
  }
});

  // Phone number validation
function validatePhone(input) {
  input.value = input.value.replace(/[^0-9]/g, '');
}

  </script>
  <!-- END Autocomplete Script -->

  
  

  

</body>
</html>
