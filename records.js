// records.js - Records Page Interactions
// CSP Compliant: External JavaScript file

/**
 * Check out a visitor via AJAX
 * @param {number} id - Visitor ID
 * @param {HTMLElement} btn - Button element to update
 */
function checkOutVisitor(id, btn) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "timeout.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function () {
    if (xhr.status === 200 && xhr.responseText.trim() === "success") {
      btn.textContent = "Checked Out";
      btn.disabled = true;
    } else {
      alert("Error checking out. Please try again.");
    }
  };
  xhr.onerror = function() {
    alert("Error: Could not connect to server.");
  };
  xhr.send("id=" + encodeURIComponent(id));
}
