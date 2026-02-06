// events_calendar.js - Events Calendar Interactions
// CSP Compliant: External JavaScript file

// Initialize dropdown menu
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

// Event Modal Functions
function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

function showEventDetails(date) {
  const modal = document.getElementById('eventModal');
  const modalDate = document.getElementById('modalDate');
  const modalBody = document.getElementById('modalBody');
  
  // Format date for display
  const d = new Date(date);
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  modalDate.textContent = d.toLocaleDateString(undefined, options);
  
  const dayEvents = eventsData[date] || [];
  
  if (dayEvents.length === 0) {
    modalBody.innerHTML = '<p style="text-align:center; color:#888; margin-top:20px;">No events scheduled for this day.</p>';
  } else {
    let html = '';
    dayEvents.forEach(event => {
      html += '<div style="margin-bottom:16px; padding:12px; background:#f5f5f5; border-radius:6px;">';
      html += '<strong style="color:#1a73e8;">' + escapeHtml(event.title) + '</strong><br>';
      if (event.description) {
        html += '<small style="color:#666;">' + escapeHtml(event.description) + '</small><br>';
      }
      html += '<small style="color:#999;">Time: ' + escapeHtml(event.time || 'N/A') + '</small>';
      html += '</div>';
    });
    modalBody.innerHTML = html;
  }
  
  modal.style.display = 'block';
  document.body.classList.add('modal-open');
}

function closeModal() {
  const modal = document.getElementById('eventModal');
  modal.style.display = 'none';
  document.body.classList.remove('modal-open');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
  const modal = document.getElementById('eventModal');
  if (modal && event.target === modal) {
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
  }
});
