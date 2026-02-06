// create_event.js - Create Event Page Interactions
// CSP Compliant: External JavaScript file

document.addEventListener("DOMContentLoaded", function () {
  const dropdown = document.querySelector(".dropdown");
  const button = document.querySelector(".dropdown-button");

  if (button && dropdown) {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdown.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
      if (!dropdown.contains(event.target)) {
        dropdown.classList.remove("show");
      }
    });
  }
});
