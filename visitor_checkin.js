// visitor_checkin.js - External JavaScript for visitor check-in page
// CSP Compliant: No inline scripts, all functionality in external file

// ============================================================================
// MAIN INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
  console.log('Visitor Check-in JS loaded');

  // ============================================================================
  // SUCCESS MESSAGE AUTO-FADE
  // ============================================================================
  const submissionMessage = document.getElementById('submission-message');
  if (submissionMessage) {
    setTimeout(function () {
      submissionMessage.classList.add('fade-out');
      setTimeout(function () {
        submissionMessage.style.display = 'none';
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('msg');
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }
      }, 700);
    }, 4000);
  }

  // ============================================================================
  // ADMIN MENU TOGGLE
  // ============================================================================
  const adminToggle = document.getElementById('adminToggle');
  const adminMenu = document.getElementById('adminMenu');

  console.log('Admin toggle element:', adminToggle);
  console.log('Admin menu element:', adminMenu);

  if (adminToggle && adminMenu) {
    console.log('Adding admin menu event listener');

    adminToggle.addEventListener('click', function (e) {
      console.log('Admin menu button clicked!');
      e.preventDefault();
      e.stopPropagation();
      adminMenu.classList.toggle('show');
      console.log('Menu show class:', adminMenu.classList.contains('show'));
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (e) {
      if (!adminToggle.contains(e.target) && !adminMenu.contains(e.target)) {
        adminMenu.classList.remove('show');
      }
    });
  } else {
    console.error('Admin menu elements not found!');
  }

  // ============================================================================
  // FACULTY AUTOCOMPLETE
  // ============================================================================
  const facultyInput = document.getElementById('faculty');
  const facultySuggestions = document.getElementById('facultySuggestions');

  // Hard-coded faculty list (fallback if get_faculties.php is unavailable)
  const fallbackFaculties = [
    'Faculty of Engineering',
    'Faculty of Veterinary Medicine',
    'Faculty of The Social Sciences',
    'Faculty of Technology',
    'Faculty of Renewable Natural Resources',
    'Faculty of Public Health',
    'Faculty of Pharmacy',
    'Faculty of Nursing',
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

  if (facultyInput && facultySuggestions) {
    /**
     * Render faculty suggestions in the dropdown
     * @param {Array} items - Array of faculty names to display
     */
    function renderSuggestions(items) {
      facultySuggestions.innerHTML = '';

      if (items.length === 0) {
        facultySuggestions.style.display = 'none';
        return;
      }

      items.forEach(faculty => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';

        // Handle both string and object formats
        const facultyName = typeof faculty === 'string' ? faculty : faculty.name;

        div.textContent = facultyName;
        div.addEventListener('click', function () {
          facultyInput.value = facultyName;
          facultySuggestions.innerHTML = '';
          facultySuggestions.style.display = 'none';
        });

        facultySuggestions.appendChild(div);
      });

      facultySuggestions.style.display = 'block';
    }

    // Input event - fetch or filter suggestions
    facultyInput.addEventListener('input', function () {
      const query = this.value.trim();

      if (query.length < 1) {
        facultySuggestions.style.display = 'none';
        return;
      }

      // Try to fetch from database first
      fetch('get_faculties.php?q=' + encodeURIComponent(query))
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
        })
        .then(data => {
          renderSuggestions(data);
        })
        .catch(err => {
          // Fallback to static list if fetch fails
          console.warn('Fallback to static faculty list:', err);
          const matches = fallbackFaculties.filter(f =>
            f.toLowerCase().includes(query.toLowerCase())
          );
          renderSuggestions(matches);
        });
    });

    // Focus event - show suggestions if input has value
    facultyInput.addEventListener('focus', function () {
      if (this.value.trim().length > 0) {
        this.dispatchEvent(new Event('input'));
      }
    });

    // Close suggestions when clicking outside autocomplete container
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.autocomplete-container')) {
        facultySuggestions.style.display = 'none';
      }
    });
  }

  // ============================================================================
  // DESTINATION STATUS + STEP VISIBILITY (FRESH FETCH)
  // ============================================================================
  const destinationSelect = document.getElementById('destination');
  const adminStatusBanner = document.getElementById('adminStatusBanner');
  const statusTitle = document.getElementById('statusTitle');
  const statusMessagePreview = document.getElementById('statusMessagePreview');
  const statusIconLarge = document.querySelector('.status-icon-large');
  const step2Details = document.getElementById('step-2-details');
  const stepMessageMode = document.getElementById('step-message-mode');
  const messageNotice = document.getElementById('messageNotice');

  // Hidden fields for availability snapshot
  const hiddenAdminId = document.getElementById('admin_id');
  const hiddenAvailability = document.getElementById('availability_snapshot');
  const hiddenStatusMessage = document.getElementById('status_message_snapshot');
  const hiddenSubmissionMode = document.getElementById('submission_mode');

  // Status configuration
  const statusConfig = {
    available: {
      icon: '✓',
      title: 'Available',
      class: 'status-available',
      showCheckIn: true,
      showMessageMode: false
    },
    busy: {
      icon: '⏱',
      title: 'Busy',
      class: 'status-busy',
      showCheckIn: true,
      showMessageMode: false // Show both options
    },
    unavailable: {
      icon: '✕',
      title: 'Unavailable',
      class: 'status-unavailable',
      showCheckIn: false,
      showMessageMode: true
    },
    away: {
      icon: '☾',
      title: 'Away',
      class: 'status-away',
      showCheckIn: false,
      showMessageMode: true
    }
  };

  /**
   * Populate hidden fields with availability data for submission
   */
  function storeAvailabilitySnapshot(data) {
    if (hiddenAdminId && data.source_user) {
      hiddenAdminId.value = data.source_user.id || '';
    }
    if (hiddenAvailability) {
      hiddenAvailability.value = data.status || '';
    }
    if (hiddenStatusMessage) {
      hiddenStatusMessage.value = data.message || '';
    }
  }

  /**
   * Update the status banner UI with given status data
   */
  function updateStatusBanner(status, message, sourceUser) {
    const config = statusConfig[status] || statusConfig.available;

    if (adminStatusBanner) {
      // Update icon
      if (statusIconLarge) {
        statusIconLarge.textContent = config.icon;
        statusIconLarge.className = `status-icon-large ${config.class}`;
      }

      // Update title
      if (statusTitle) {
        statusTitle.textContent = `This office is currently ${config.title}`;
      }

      // Update message
      if (statusMessagePreview) {
        if (message) {
          statusMessagePreview.textContent = message;
        } else if (status === 'available') {
          statusMessagePreview.textContent = 'You can proceed with check-in.';
        } else {
          statusMessagePreview.textContent = 'You may still leave a message for this office.';
        }
      }

      // Update banner class and show it
      adminStatusBanner.className = `status-banner ${config.class}`;
      adminStatusBanner.style.display = 'flex';
    }

    // Handle step visibility based on availability
    if (config.showMessageMode) {
      // Away or Unavailable: show message mode panel
      if (step2Details) step2Details.style.display = 'none';
      if (stepMessageMode) {
        stepMessageMode.style.display = 'block';
        // Update the notice text
        if (messageNotice) {
          if (status === 'away') {
            messageNotice.textContent = 'The admin is currently away. Please leave a message below.';
          } else {
            messageNotice.textContent = 'The admin is currently unavailable. Please leave a message below.';
          }
        }
      }
      // Set submission mode to message by default
      if (hiddenSubmissionMode) hiddenSubmissionMode.value = 'message';
    } else {
      // Available or Busy: show normal check-in form
      if (step2Details) step2Details.style.display = 'block';
      if (stepMessageMode) stepMessageMode.style.display = 'none';
      // Set submission mode to checkin
      if (hiddenSubmissionMode) hiddenSubmissionMode.value = 'checkin';
    }
  }

  /**
   * Fetch fresh availability from the server
   */
  async function fetchDestinationAvailability(destinationId) {
    try {
      const response = await fetch(`get_destination_availability.php?destination_id=${destinationId}`);
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      const data = await response.json();
      if (data.ok) {
        return data;
      } else {
        throw new Error(data.error || 'Unknown error');
      }
    } catch (error) {
      console.warn('Failed to fetch availability:', error);
      return null;
    }
  }

  if (destinationSelect) {
    destinationSelect.addEventListener('change', async function () {
      const destinationId = this.value;

      // Reset all states if no destination selected
      if (!destinationId) {
        if (adminStatusBanner) adminStatusBanner.style.display = 'none';
        if (step2Details) step2Details.style.display = 'none';
        if (stepMessageMode) stepMessageMode.style.display = 'none';
        return;
      }

      // Connect Realtime Client for this destination
      if (window.RealtimeClient) {
        RealtimeClient.connect({
          clientType: 'visitor',
          destinationId: destinationId
        });

        // Listen for availability updates
        RealtimeClient.on('availability_updated', (payload) => {
          if (parseInt(payload.destination_id) === parseInt(destinationId)) {
            console.log('Realtime status update:', payload);

            // Update UI immediately
            storeAvailabilitySnapshot({
              status: payload.status,
              message: payload.message,
              source_user: { id: payload.admin_id }
            });

            updateStatusBanner(
              payload.status,
              payload.message,
              { id: payload.admin_id, username: payload.admin_username }
            );

            // Update "Last updated" text
            const isoTime = payload.last_status_change || new Date().toISOString();
            updateVisitorLastUpdated(isoTime);
          }
        });
      }

      // Show loading state
      if (adminStatusBanner) {
        adminStatusBanner.className = 'status-banner status-loading';
        adminStatusBanner.style.display = 'flex';
        if (statusIconLarge) statusIconLarge.textContent = '⟳';
        if (statusTitle) statusTitle.textContent = 'Checking availability...';
        if (statusMessagePreview) statusMessagePreview.textContent = '';
      }
      if (step2Details) step2Details.style.display = 'none';
      if (stepMessageMode) stepMessageMode.style.display = 'none';

      // Fetch fresh availability from server
      const availability = await fetchDestinationAvailability(destinationId);

      if (availability) {
        // Store availability snapshot in hidden fields
        storeAvailabilitySnapshot(availability);
        // Update UI
        updateStatusBanner(
          availability.status,
          availability.message,
          availability.source_user
        );
      } else {
        // Error: fallback to data attributes (static) or default
        const selectedOption = this.options[this.selectedIndex];
        const fallbackStatus = selectedOption.dataset.status || 'available';
        const fallbackMessage = selectedOption.dataset.message || '';
        updateStatusBanner(fallbackStatus, fallbackMessage, null);
      }
    });

    // Trigger change event if destination was pre-selected (e.g., from URL param)
    if (destinationSelect.value) {
      destinationSelect.dispatchEvent(new Event('change'));
    }
  }
});

// ============================================================================
// GLOBAL FUNCTIONS
// ============================================================================

/**
 * Confirm alternate check-in action
 * @returns {boolean} User confirmation result
 */
function confirmAlternate() {
  return confirm('You will leave a message for the department head. Continue?');
}

/**
 * Validate and sanitize phone number input (numbers only)
 * @param {HTMLElement} input - The phone input element
 */
function validatePhone(input) {
  input.value = input.value.replace(/[^0-9]/g, '');
}

/**
 * Set the submission mode for the form
 * @param {string} mode - Either 'checkin' or 'message'
 */
function setSubmissionMode(mode) {
  const hiddenMode = document.getElementById('submission_mode');
  if (hiddenMode) {
    hiddenMode.value = mode;
  }
  return true; // Allow form submission
}

/**
 * Switch from message mode to check-in mode (Proceed Anyway)
 */
function showProceedAnyway() {
  const step2Details = document.getElementById('step-2-details');
  const stepMessageMode = document.getElementById('step-message-mode');
  const proceedHint = document.getElementById('proceedHint');
  const hiddenMode = document.getElementById('submission_mode');

  // Hide message mode, show check-in form
  if (stepMessageMode) stepMessageMode.style.display = 'none';
  if (step2Details) step2Details.style.display = 'block';
  if (proceedHint) proceedHint.style.display = 'block';

  // Set mode to checkin (visitor is proceeding anyway)
  if (hiddenMode) hiddenMode.value = 'checkin';
}

/**
 * Update the "Last Updated" text in the status banner
 */
function updateVisitorLastUpdated(isoString) {
  const bannerText = document.querySelector('.status-text-content');
  if (!bannerText) return;

  let timeLabel = document.getElementById('visitorStatusTime');
  if (!timeLabel) {
    timeLabel = document.createElement('span');
    timeLabel.id = 'visitorStatusTime';
    timeLabel.className = 'status-last-updated';
    bannerText.appendChild(timeLabel);
  }

  // Parse time
  const date = new Date(isoString);
  if (isNaN(date.getTime())) return;

  const now = new Date();
  const diffMin = Math.floor((now - date) / 60000);

  if (diffMin < 1) {
    timeLabel.textContent = 'Updated just now';
  } else {
    timeLabel.textContent = `Updated ${diffMin}m ago`;
  }
}

