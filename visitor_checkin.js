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
  // FORM SUBMISSION HANDLER - FETCH + INLINE ERRORS
  // ============================================================================
  const checkinForm = document.getElementById('checkinForm');
  if (checkinForm) {
    // Track which submit button was clicked (FormData doesn't include it)
    let clickedSubmitBtn = null;
    checkinForm.querySelectorAll('button[type="submit"]').forEach(btn => {
      btn.addEventListener('click', function () {
        clickedSubmitBtn = this;
      });
    });

    checkinForm.addEventListener('submit', async function (e) {
      e.preventDefault(); // Always prevent — we handle submission via fetch

      clearFormErrors(checkinForm);

      const destination = document.getElementById('destination').value.trim();
      const submissionMode = document.getElementById('submission_mode');
      const isMessageMode = submissionMode && submissionMode.value === 'message';

      // Determine which inputs to validate based on active mode
      let fullname, faculty, phone, purpose;

      if (isMessageMode) {
        const msgFullname = document.querySelector('.msg-fullname');
        const msgFaculty = document.querySelector('.msg-faculty');
        const msgPhone = document.querySelector('.msg-phone');
        const msgMessage = document.getElementById('visitor_message');

        fullname = msgFullname ? msgFullname.value.trim() : '';
        faculty = msgFaculty ? msgFaculty.value.trim() : '';
        phone = msgPhone ? msgPhone.value.trim() : '';
        purpose = msgMessage ? msgMessage.value.trim() : '';
      } else {
        const fullnameInput = document.querySelector('#step-2-details input[name="fullname"]');
        const facultyInput = document.querySelector('#step-2-details input[name="faculty"]');
        const phoneInput = document.querySelector('#step-2-details input[name="phone_number"]');
        const purposeTextarea = document.querySelector('#step-2-details textarea[name="purpose"]');

        fullname = fullnameInput ? fullnameInput.value.trim() : '';
        faculty = facultyInput ? facultyInput.value.trim() : '';
        phone = phoneInput ? phoneInput.value.trim() : '';
        purpose = purposeTextarea ? purposeTextarea.value.trim() : '';
      }

      const visitorTypeChecked = document.querySelector('input[name="visitor_type"]:checked');

      // --- Client-side validation (inline errors, no alert) ---
      const errors = {};

      if (!destination) errors.destination = 'Select a destination first';
      if (!fullname) errors.fullname = 'Full name is required';
      if (!faculty) errors.faculty = 'Faculty/Organization is required';
      if (!phone) errors.phone = 'Phone number is required';
      else if (!/^[0-9]{7,15}$/.test(phone)) errors.phone = 'Phone must be 7-15 digits (numbers only)';
      if (!purpose) errors.purpose = isMessageMode ? 'Message is required' : 'Purpose of visit is required';
      if (!visitorTypeChecked) errors.visitor_type = 'Please select a visitor type';

      if (Object.keys(errors).length > 0) {
        displayFieldErrors(errors, isMessageMode);
        return;
      }

      // --- Validation passed — submit via fetch ---
      console.log('✓ Form validation passed - submitting via fetch...');

      // Disable inputs in the INACTIVE panel to prevent empty duplicates
      // (FormData collects all inputs; PHP takes the last value which would be empty)
      const inactivePanel = isMessageMode
        ? document.getElementById('step-2-details')
        : document.getElementById('step-message-mode');
      const disabledInputs = [];
      if (inactivePanel) {
        inactivePanel.querySelectorAll('input, textarea, select').forEach(el => {
          if (!el.disabled) {
            el.disabled = true;
            disabledInputs.push(el);
          }
        });
      }

      const formData = new FormData(checkinForm);

      // Re-enable the inputs we just disabled
      disabledInputs.forEach(el => { el.disabled = false; });

      // Append clicked submit button's name/value (FormData misses this)
      if (clickedSubmitBtn && clickedSubmitBtn.name) {
        formData.append(clickedSubmitBtn.name, clickedSubmitBtn.value);
      }

      // Disable submit buttons while request is in flight
      const submitButtons = checkinForm.querySelectorAll('button[type="submit"]');
      submitButtons.forEach(btn => {
        btn.disabled = true;
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Submitting...';
      });

      try {
        const response = await fetch('submit.php', {
          method: 'POST',
          body: formData
        });

        // Success: submit.php redirected (302 → confirmation.php or index.php)
        if (response.redirected) {
          window.location.href = response.url;
          return;
        }

        // Try to parse JSON response (validation error or DB error)
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
          const data = await response.json();

          if (data.success === false && data.errors) {
            displayFieldErrors(data.errors, isMessageMode);
          } else {
            showFormBanner(checkinForm, 'Something went wrong. Please try again.', 'error');
          }
        } else {
          // Non-JSON, non-redirect — could be the redirected page HTML
          if (response.ok) {
            window.location.href = response.url;
          } else {
            showFormBanner(checkinForm, 'Something went wrong. Please try again.', 'error');
          }
        }
      } catch (err) {
        console.error('Form submission error:', err);
        showFormBanner(checkinForm, 'Network error. Please check your connection and try again.', 'error');
      } finally {
        // Re-enable submit buttons
        submitButtons.forEach(btn => {
          btn.disabled = false;
          if (btn.dataset.originalText) {
            btn.textContent = btn.dataset.originalText;
            delete btn.dataset.originalText;
          }
        });
      }
    });
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
      showCheckIn: false,
      showMessageMode: true // Busy = message only, no check-in
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
      // Away, Unavailable, or Busy: show message mode panel
      if (step2Details) step2Details.style.display = 'none';
      if (stepMessageMode) {
        stepMessageMode.style.display = 'block';
        // Update the notice text
        if (messageNotice) {
          if (status === 'busy') {
            messageNotice.textContent = 'This office is currently busy. Please leave a message below.';
          } else if (status === 'away') {
            messageNotice.textContent = 'The admin is currently away. Please leave a message below.';
          } else {
            messageNotice.textContent = 'The admin is currently unavailable. Please leave a message below.';
          }
        }
      }

      // Hide "Proceed Anyway" button when busy or unavailable
      const btnProceedAnyway = document.getElementById('btnProceedAnyway');
      if (btnProceedAnyway) {
        if (status === 'busy' || status === 'unavailable') {
          btnProceedAnyway.style.display = 'none';
        } else {
          btnProceedAnyway.style.display = '';
        }
      }

      // Set submission mode to message by default
      if (hiddenSubmissionMode) hiddenSubmissionMode.value = 'message';
    } else {
      // Available: show normal check-in form
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

// ============================================================================
// INLINE ERROR DISPLAY HELPERS
// ============================================================================

/**
 * Map server error keys to DOM input selectors based on mode
 * @param {string} fieldKey - Server error key (e.g. 'fullname', 'phone')
 * @param {boolean} isMessageMode - Whether the form is in message mode
 * @returns {HTMLElement|null}
 */
function getFieldElement(fieldKey, isMessageMode) {
  const fieldMap = {
    fullname: isMessageMode
      ? '.msg-fullname'
      : '#step-2-details input[name="fullname"]',
    faculty: isMessageMode
      ? '.msg-faculty'
      : '#step-2-details input[name="faculty"]',
    phone: isMessageMode
      ? '.msg-phone'
      : '#step-2-details input[name="phone_number"]',
    purpose: isMessageMode
      ? '#visitor_message'
      : '#step-2-details textarea[name="purpose"]',
    visitor_type: '.visitor-type-group',
    destination: '#destination'
  };

  const selector = fieldMap[fieldKey];
  return selector ? document.querySelector(selector) : null;
}

/**
 * Clear all existing inline error states from the form
 * @param {HTMLFormElement} form
 */
function clearFormErrors(form) {
  form.querySelectorAll('.field-error-msg').forEach(el => el.remove());
  form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
  const banner = form.querySelector('.form-error-banner');
  if (banner) banner.remove();
}

/**
 * Display inline errors for specific fields
 * @param {Object} errors - { fieldKey: "Error message", ... }
 * @param {boolean} isMessageMode
 */
function displayFieldErrors(errors, isMessageMode) {
  let firstErrorEl = null;

  for (const [key, message] of Object.entries(errors)) {
    const el = getFieldElement(key, isMessageMode);
    if (el) {
      // Add red border
      el.classList.add('input-error');

      // Create error message element below the input
      const errMsg = document.createElement('div');
      errMsg.className = 'field-error-msg';
      errMsg.textContent = message;

      // Insert after the input (or inside for radio groups)
      if (key === 'visitor_type') {
        el.appendChild(errMsg);
      } else {
        el.parentNode.insertBefore(errMsg, el.nextSibling);
      }

      if (!firstErrorEl) firstErrorEl = el;
    }
  }

  // Scroll to first error
  if (firstErrorEl) {
    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (firstErrorEl.focus) firstErrorEl.focus();
  }
}

/**
 * Show a generic error/success banner at the top of the form
 * @param {HTMLFormElement} form
 * @param {string} message
 * @param {string} type - 'error' or 'success'
 */
function showFormBanner(form, message, type) {
  // Remove existing banner
  const existing = form.querySelector('.form-error-banner');
  if (existing) existing.remove();

  const banner = document.createElement('div');
  banner.className = `form-error-banner form-banner-${type}`;
  banner.textContent = message;

  // Insert at the top of the form, after the h2
  const h2 = form.querySelector('h2');
  if (h2) {
    h2.parentNode.insertBefore(banner, h2.nextSibling);
  } else {
    form.prepend(banner);
  }

  banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

