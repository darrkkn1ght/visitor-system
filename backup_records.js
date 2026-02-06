// backup_records.js - Backup/Export Records Page Interactions
// CSP Compliant: External JavaScript file

document.addEventListener("DOMContentLoaded", function () {
  const button = document.querySelector('.dropdown-button');
  const dropdown = document.querySelector('.dropdown');

  if (button && dropdown) {
    button.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('.dropdown')) {
        dropdown.classList.remove('show');
      }
    });
  }
});
