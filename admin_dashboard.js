// admin_dashboard.js - Admin Dashboard Filter Controls
// CSP Compliant: External JavaScript file
// Note: Menu functionality is handled by simple_menu.js

console.log("admin_dashboard.js loaded");

function initializeFilterControls() {
  const filterDestination = document.getElementById('filter_destination');
  const rowsPerPage = document.getElementById('rows_per_page');

  console.log("Filter controls:", { filterDestination, rowsPerPage });

  if (filterDestination) {
    console.log("Found filter destination element:", filterDestination);
    console.log("Current value:", filterDestination.value);
    filterDestination.addEventListener('change', function () {
      console.log("Destination filter changed to:", this.value);
      console.log("Form action:", this.form.action);
      console.log("Form method:", this.form.method);
      this.form.submit();
    });
  } else {
    console.log("Filter destination element not found (normal for receptionist/destination_admin)");
  }

  if (rowsPerPage) {
    console.log("Found rows per page element:", rowsPerPage);
    console.log("Current value:", rowsPerPage.value);
    rowsPerPage.addEventListener('change', function () {
      console.log("Rows per page changed to:", this.value);
      console.log("Form action:", this.form.action);
      console.log("Form method:", this.form.method);
      this.form.submit();
    });
  } else {
    console.log("Rows per page element not found (normal for receptionist/destination_admin)");
  }

  console.log("Filter controls initialized");
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", function () {
    initializeFilterControls();
    initializeNotifications();
    initializeMessageQueue();
  });
} else {
  // DOM is already loaded (deferred script)
  initializeFilterControls();
  initializeNotifications();
  initializeMessageQueue();
}

// Notification Logic Wrapper
function initializeNotifications() {
  const notifIcon = document.getElementById('notif-icon');
  const notifDropdown = document.getElementById('notif-dropdown');
  const notifBadge = document.getElementById('notif-badge');
  const notifList = document.getElementById('notif-list');
  const markReadBtn = document.getElementById('mark-all-read');

  // Toggle Dropdown
  if (notifIcon) {
    // Remove existing listeners to avoid duplicates if called multiple times (though simple check helps)
    const newIcon = notifIcon.cloneNode(true);
    notifIcon.parentNode.replaceChild(newIcon, notifIcon);

    newIcon.addEventListener('click', function (e) {
      e.stopPropagation();
      if (notifDropdown) {
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
      }
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    const icon = document.getElementById('notif-icon'); // Re-select in case of clone
    if (notifDropdown && notifDropdown.style.display === 'block' && icon && !icon.contains(e.target) && !notifDropdown.contains(e.target)) {
      notifDropdown.style.display = 'none';
    }
  });

  // Fetch Notifications
  function fetchNotifications() {
    if (!notifList) return;

    fetch('fetch_notifications.php?limit=50')
      .then(response => response.json())
      .then(data => {
        // Update Badge
        if (data.count > 0) {
          if (notifBadge) {
            notifBadge.textContent = data.count > 99 ? '99+' : data.count;
            notifBadge.style.display = 'block';
          }
        } else {
          if (notifBadge) notifBadge.style.display = 'none';
        }

        // Update List
        if (notifList) {
          notifList.innerHTML = '';
          if (data.notifications.length === 0) {
            notifList.innerHTML = '<div class="notif-item empty">No new notifications</div>';
          } else {
            data.notifications.forEach(notif => {
              const item = document.createElement('div');
              item.className = 'notif-item unread';
              item.innerHTML = `
                              ${notif.message}
                              <span class="time">${new Date(notif.created_at).toLocaleString()}</span>
                          `;
              notifList.appendChild(item);
            });
          }
        }
      })
      .catch(err => console.error('Error fetching notifications:', err));
  }

  // Mark All Read
  if (markReadBtn) {
    // Clone to remove old listeners
    const newBtn = markReadBtn.cloneNode(true);
    markReadBtn.parentNode.replaceChild(newBtn, markReadBtn);

    newBtn.addEventListener('click', function () {
      fetch('mark_notifications.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            fetchNotifications(); // Refresh list (should be empty now)
          }
        })
        .catch(err => console.error('Error marking read:', err));
    });
  }

  // Initial Fetch & Poll
  fetchNotifications();
  // Clear any existing intervals to prevent duplicates if function called twice
  if (window.notifInterval) clearInterval(window.notifInterval);
  window.notifInterval = setInterval(fetchNotifications, 30000); // Poll every 30 seconds
}

// --- SIDEBAR AVAILABILITY LOGIC ---
document.addEventListener('DOMContentLoaded', () => {

  // Status Dropdown Handler
  const statusDropdown = document.getElementById('statusDropdown');
  const statusMessageInput = document.getElementById('statusMessage');
  const statusUpdateMsg = document.getElementById('statusUpdateMessage');

  if (statusDropdown) {
    statusDropdown.addEventListener('change', function () {
      const newStatus = this.value;
      const message = statusMessageInput ? statusMessageInput.value : '';

      // Update dropdown styling immediately
      this.className = `status-select status-${newStatus}`;

      updateStatus(newStatus, message);
    });
  }

  // Status Message Input Handler (Debounced auto-save)
  let timeoutId;
  if (statusMessageInput) {
    statusMessageInput.addEventListener('input', function () {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => {
        const status = statusDropdown.value;
        const message = this.value;
        updateStatus(status, message, true); // true = silent update
      }, 1000);
    });
  }

  // Helper: Update Status AJAX
  async function updateStatus(status, message, silent = false) {
    try {
      if (!silent && statusUpdateMsg) {
        statusUpdateMsg.textContent = 'Updating...';
        statusUpdateMsg.className = 'status-update-message';
      }

      const formData = new URLSearchParams();
      formData.append('status', status);
      formData.append('message', message);
      formData.append('csrf_token', window.PROFILE_DATA.csrfToken);

      const response = await fetch('update_availability.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        if (!silent && statusUpdateMsg) {
          statusUpdateMsg.textContent = 'Updated!';
          statusUpdateMsg.className = 'status-update-message success';
          setTimeout(() => statusUpdateMsg.textContent = '', 2000);
        }
      } else {
        throw new Error(data.message || 'Update failed');
      }
    } catch (error) {
      console.error(error);
      if (!silent && statusUpdateMsg) {
        statusUpdateMsg.textContent = 'Error updating status';
        statusUpdateMsg.className = 'status-update-message error';
      }
    }
  }

  // --- SIDEBAR NOTIFICATIONS LOGIC ---
  window.toggleSidebarNotification = async function (element) {
    if (element.classList.contains('unread')) {
      const id = element.dataset.notificationId;

      // Optimistic UI update
      element.classList.remove('unread');
      element.classList.add('read');

      try {
        const formData = new URLSearchParams();
        formData.append('notification_id', id);
        formData.append('csrf_token', window.PROFILE_DATA.csrfToken);
        await fetch('mark_notification_read.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData
        });
      } catch (e) { console.error(e); }
    }
  };
});

// ============================================================================
// MESSAGE QUEUE LOGIC
// ============================================================================
function initializeMessageQueue() {
  const queueElement = document.getElementById('sidebarMessageQueue');
  const queueCountBadge = document.getElementById('queueCount');

  if (!queueElement) return;

  // Render a single queue item
  function createQueueItem(msg) {
    const div = document.createElement('div');
    div.className = 'queue-item';
    div.dataset.id = msg.id;

    // Formatting date
    const date = new Date(msg.submitted_at);
    const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Check if urgent (has preferred return time)
    const isUrgent = msg.preferred_return ? '<span class="queue-tag urgent">Callback</span>' : '';
    const statusNote = msg.admin_notes ? '<div class="queue-has-notes">📝 Has notes</div>' : '';

    // Status badge class
    let statusClass = 'status-available';
    if (msg.availability_snapshot === 'busy') statusClass = 'status-busy';
    if (msg.availability_snapshot === 'unavailable') statusClass = 'status-unavailable';
    if (msg.availability_snapshot === 'away') statusClass = 'status-away';

    div.innerHTML = `
      <div class="queue-header">
        <span class="queue-name">${escapeHtml(msg.fullname)}</span>
        <span class="queue-time">${timeStr}</span>
      </div>
      <div class="queue-details">
        <div class="queue-purpose">${escapeHtml(msg.message || msg.purpose)}</div>
        <div class="queue-meta">
          ${isUrgent}
          <span class="queue-tag ${statusClass}">${msg.availability_snapshot || 'unknown'}</span>
        </div>
        ${statusNote}
      </div>
      <div class="queue-actions">
        <button class="btn-xs btn-resolve" onclick="resolveMessage(${msg.id}, 'resolve')">✓ Resolve</button>
        <button class="btn-xs btn-note" onclick="promptAddNote(${msg.id})">💬 Note</button>
      </div>
    `;
    return div;
  }

  // Fetch queue from server
  function fetchQueue() {
    fetch('get_message_queue.php')
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          renderQueue(data.messages);
          if (queueCountBadge) {
            queueCountBadge.textContent = data.count;
            queueCountBadge.style.display = data.count > 0 ? 'inline-block' : 'none';
          }
        }
      })
      .catch(err => console.error('Failed to fetch queue:', err));
  }

  // Render list
  function renderQueue(messages) {
    queueElement.innerHTML = '';
    if (messages.length === 0) {
      queueElement.innerHTML = '<div class="queue-empty">No pending messages</div>';
      return;
    }

    messages.forEach(msg => {
      queueElement.appendChild(createQueueItem(msg));
    });
  }

  // Helper to escape HTML to prevent XSS
  function escapeHtml(text) {
    if (!text) return '';
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Initial load and interval
  fetchQueue();
  setInterval(fetchQueue, 30000); // Refresh every 30s

  // Expose for realtime updates
  window.refreshMessageQueue = fetchQueue;
}

// Global actions for queue items
window.resolveMessage = function (id, action) {
  if (!confirm('Mark this message as resolved?')) return;
  performQueueAction(id, action);
};

window.promptAddNote = function (id) {
  const note = prompt("Enter a note for this message:");
  if (note && note.trim()) {
    performQueueAction(id, 'add_note', note);
  }
};

function performQueueAction(id, action, notes = null) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  if (!csrfToken) {
    console.error("CSRF token not found");
    alert("Security token missing. Please refresh the page.");
    return;
  }

  const formData = new FormData();
  formData.append('visitor_id', id);
  formData.append('action', action);
  if (notes) formData.append('notes', notes);
  formData.append('csrf_token', csrfToken);

  fetch('resolve_message.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        // Remove item from UI immediately or refresh
        const item = document.querySelector(`.queue-item[data-id="${id}"]`);
        if (item && action === 'resolve') {
          item.remove();
          // Update count
          const badge = document.getElementById('queueCount');
          if (badge) {
            const current = parseInt(badge.textContent) || 0;
            badge.textContent = Math.max(0, current - 1);
            if (current - 1 <= 0) badge.style.display = 'none';
          }
        } else if (action === 'add_note') {
          alert("Note added successfully");
          // Could refresh note indicator here if needed
        }
      } else {
        alert("Error: " + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error("Request failed", err);
      alert("Network error occurred");
    });
}
// ============================================================================
// REALTIME NOTIFICATIONS
// ============================================================================
function initializeRealtime() {
  if (!window.RealtimeClient || !window.PROFILE_DATA) return;

  const { userId, destinationId, role } = window.PROFILE_DATA;

  RealtimeClient.connect({
    clientType: 'admin',
    adminId: userId,
    destinationId: destinationId,
    role: role
  });

  // Handle New Messages
  RealtimeClient.on('new_message', (payload) => {
    // 1. Update Badge
    updateQueueBadge(1); // increment by 1

    // 2. Update List if visible
    const queueElement = document.getElementById('sidebarMessageQueue');
    if (queueElement) {
      // Remove "empty" message if present
      const emptyMsg = queueElement.querySelector('.queue-empty');
      if (emptyMsg) emptyMsg.remove();

      // Add new item to top
      // Ideally we reuse createQueueItem from initializeMessageQueue scope
      // But it is scoped inside. We can trigger a fetch, or make createQueueItem global.
      // For simplicity/robustness, let's trigger a fetch
      // fetchQueue(); // But fetchQueue is not global.
      // Let's rely on the interval OR simple reload. 
      // Better: Make fetchQueue globally accessible or trigger click.

      // Simple fallback: just refresh the queue
      // We can expose a global refresher
      if (window.refreshMessageQueue) {
        window.refreshMessageQueue();
      }
    }
  });

  // Handle Message Actions (Resolved from other tab)
  RealtimeClient.on('message_action', (payload) => {
    if (payload.action === 'resolve') {
      // Remove from UI
      const item = document.querySelector(`.queue-item[data-id="${payload.visitor_id}"]`);
      if (item) {
        item.style.opacity = '0';
        setTimeout(() => item.remove(), 300);
        updateQueueBadge(-1);
      } else {
        // If not found, maybe just refresh to be safe
        if (window.refreshMessageQueue) window.refreshMessageQueue();
      }
    } else {
      // Note added or scheduled
      if (window.refreshMessageQueue) window.refreshMessageQueue();
    }
  });

  // Handle Availability Updates (from other tab)
  RealtimeClient.on('availability_updated', (payload) => {
    // If it's my own status
    if (parseInt(payload.admin_id) === userId) {
      const dropdown = document.getElementById('statusDropdown');
      const msgInput = document.getElementById('statusMessage');

      if (dropdown && dropdown.value !== payload.status) {
        dropdown.value = payload.status;
        dropdown.className = `status-select status-${payload.status}`;
      }
      if (msgInput && msgInput.value !== payload.message) {
        msgInput.value = payload.message;
      }

      // Update timestamp
      updateLastStatusTime(payload.last_status_change);
    }
  });
}

function updateQueueBadge(delta) {
  const badge = document.getElementById('queueCount');
  if (badge) {
    let current = parseInt(badge.textContent) || 0;
    let newVal = current + delta;
    if (newVal < 0) newVal = 0; // Prevent negative
    badge.textContent = newVal;
    badge.style.display = newVal > 0 ? 'inline-block' : 'none';
  }
}

// "Last Updated" Helper
function updateLastStatusTime(isoString) {
  // Logic to update UI text... 
  // We need an element for this.
  // Let's add it via JS if not exists
  const container = document.querySelector('.sidebar-availability');
  if (!container) return;

  let timeLabel = document.getElementById('lastStatusUpdate');
  if (!timeLabel) {
    timeLabel = document.createElement('div');
    timeLabel.id = 'lastStatusUpdate';
    timeLabel.className = 'last-update-text';
    container.appendChild(timeLabel);
  }

  // Calculate time ago
  const date = new Date(isoString);
  const now = new Date();
  const diffMin = Math.floor((now - date) / 60000);

  if (diffMin < 1) {
    timeLabel.textContent = 'Updated just now';
  } else {
    timeLabel.textContent = `Updated ${diffMin}m ago`;
  }
}

// Hook into DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  initializeRealtime();

  // Poll "Last Updated" text every minute
  setInterval(() => {
    // We need the last timestamp stored somewhere. 
    // We can store it on the element data-time
  }, 60000);
});

