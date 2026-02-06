// destinations.js - Destinations Page Interactions
// CSP Compliant: External JavaScript file

document.addEventListener("DOMContentLoaded", function () {
  // Auto-fade success/error messages
  setTimeout(function () {
    document.querySelectorAll('.messages .ok, .messages .err').forEach(function (el) {
      el.classList.add('fade-out');
      setTimeout(function () {
        el.style.display = 'none';
      }, 700); // Match the CSS transition duration
    });
  }, 4000); // 4 seconds before fade starts

  // Auto-fade confirmation popups
  setTimeout(function () {
    document.querySelectorAll('.confirmation-popup').forEach(function (el) {
      el.classList.add('fade-out');
      setTimeout(function () {
        el.style.display = 'none';
      }, 700);
    });
  }, 4000);

  // Ensure only one keycard details panel opens at a time
  var detailsElements = document.querySelectorAll('tbody details');
  detailsElements.forEach(function (detailElement) {
    detailElement.addEventListener('toggle', function () {
      if (this.open) {
        // Close all other details elements
        detailsElements.forEach(function (otherDetail) {
          if (otherDetail !== detailElement && otherDetail.open) {
            otherDetail.open = false;
          }
        });
      }
    });
  });

  // Close details panel when clicking outside (on the backdrop)
  document.addEventListener('click', function (event) {
    // Check if clicked on the backdrop pseudo-element area
    detailsElements.forEach(function (detailElement) {
      if (detailElement.open) {
        // Dropdown behavior: Close if clicking anywhere outside the details component
        if (!detailElement.contains(event.target)) {
          detailElement.open = false;
        }
      }
    });
  });
});
