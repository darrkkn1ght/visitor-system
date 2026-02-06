/**
 * events_calendar.js
 * Frontend logic for the interactive events calendar.
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- State ---
  let currentMonth = new Date().getMonth(); // 0-11
  let currentYear = new Date().getFullYear();
  let selectedDate = null;
  let eventsData = []; // Flat array of events from API
  let filteredEvents = []; // Events for selected day

  // --- DOM Elements ---
  const gridEl = document.getElementById('calendarGrid');
  const monthDisplayEl = document.getElementById('currentMonthYear');
  const sidebarListEl = document.getElementById('sidebarEventList');
  const selectedDateTitle = document.getElementById('selectedDateDisplay');
  const selectedDayName = document.getElementById('selectedDayName');
  const eventCountBadge = document.getElementById('eventCountBadge');

  // Modal
  const modal = document.getElementById('eventModal');
  const modalTitle = document.getElementById('modalTitle');
  const form = document.getElementById('eventForm');
  const btnCreate = document.getElementById('btnCreateEvent');
  const btnDelete = document.getElementById('btnDeleteEvent');

  // --- Initialization ---
  init();

  function init() {
    fetchEvents();
    setupEventListeners();
    selectDate(new Date()); // Select today by default
  }

  function setupEventListeners() {
    document.getElementById('btnPrevMonth').addEventListener('click', () => changeMonth(-1));
    document.getElementById('btnNextMonth').addEventListener('click', () => changeMonth(1));
    document.getElementById('btnToday').addEventListener('click', () => {
      currentMonth = new Date().getMonth();
      currentYear = new Date().getFullYear();
      fetchEvents();
      selectDate(new Date());
    });

    if (btnCreate) {
      btnCreate.addEventListener('click', () => openModal());
    }

    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('btnCancel').addEventListener('click', closeModal);

    // Close modal on outside click
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });

    // Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    form.addEventListener('submit', handleFormSubmit);
    btnDelete.addEventListener('click', handleDelete);
  }

  // --- Core Logic ---

  async function fetchEvents() {
    const start = new Date(currentYear, currentMonth - 1, 1).toISOString().split('T')[0];
    const end = new Date(currentYear, currentMonth + 2, 0).toISOString().split('T')[0];

    try {
      monthDisplayEl.innerText = 'Loading...';
      // Use date range fetch
      const res = await fetch(`get_events.php?start=${start}&end=${end}`);
      const data = await res.json();

      if (data.success) {
        eventsData = data.events;
        renderCalendar();
        // Re-select date to update sidebar if still in view
        if (selectedDate) updateSidebar();
      } else {
        console.error("Failed to load events", data.message, data.error);
        monthDisplayEl.innerText = 'Error loading events';
      }
    } catch (error) {
      console.error("Network error", error);
      monthDisplayEl.innerText = 'Network Error';
    }
  }

  function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    } else if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    fetchEvents(); // Re-fetch for new range
  }

  function selectDate(dateObj) {
    selectedDate = dateObj;
    updateSidebar();
    renderCalendar(); // To update 'selected' class
  }

  function renderCalendar() {
    gridEl.innerHTML = '';
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    monthDisplayEl.innerText = `${monthNames[currentMonth]} ${currentYear}`;

    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDayIndex = firstDay.getDay(); // 0 (Sun) - 6 (Sat)

    // Previous month padding
    const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();
    for (let i = startDayIndex - 1; i >= 0; i--) {
      const dayNum = prevMonthLastDay - i;
      const cell = createDayCell(dayNum, true);
      gridEl.appendChild(cell);
    }

    // Current month
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
      const cellDate = new Date(currentYear, currentMonth, i);
      const isToday = isSameDay(cellDate, today);
      const isSelected = selectedDate && isSameDay(cellDate, selectedDate);

      const cell = createDayCell(i, false, isToday, isSelected);

      // Add dots
      const dayEvents = getEventsForDay(cellDate);
      if (dayEvents.length > 0) {
        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'events-dots';
        // Show max 5 dots
        dayEvents.slice(0, 5).forEach(ev => {
          const dot = document.createElement('div');
          // Schema doesn't have visibility, default to dot. 
          // Or we could infer from time_slot if we wanted different colors.
          dot.className = `dot`;
          dotsContainer.appendChild(dot);
        });
        if (dayEvents.length > 5) {
          const moreDot = document.createElement('div');
          moreDot.className = 'dot more';
          dotsContainer.appendChild(moreDot);
        }
        cell.appendChild(dotsContainer);
      }

      cell.addEventListener('click', () => selectDate(cellDate));
      gridEl.appendChild(cell);
    }

    // Next month padding (finish grid)
    const totalCells = startDayIndex + daysInMonth;
    const nextMonthPadding = 42 - totalCells; // 6 rows * 7 cols = 42
    for (let i = 1; i <= nextMonthPadding; i++) {
      const cell = createDayCell(i, true);
      gridEl.appendChild(cell);
    }
  }

  function createDayCell(num, isOtherMonth, isToday = false, isSelected = false) {
    const el = document.createElement('div');
    el.className = `day-cell ${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''} ${isSelected ? 'selected' : ''}`;
    el.innerHTML = `<div class="day-num">${num}</div>`;
    return el;
  }

  function updateSidebar() {
    if (!selectedDate) return;

    const dateStr = selectedDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    selectedDateTitle.innerText = selectedDate.getDate();
    selectedDayName.innerText = dateStr;

    filteredEvents = getEventsForDay(selectedDate);
    eventCountBadge.innerText = filteredEvents.length;
    sidebarListEl.innerHTML = '';

    if (filteredEvents.length === 0) {
      sidebarListEl.innerHTML = '<div class="empty-state">No events scheduled.</div>';
      return;
    }

    filteredEvents.forEach(ev => {
      const el = document.createElement('div');
      el.className = 'event-item';

      // Format time based on slot
      const timeStr = formatSlot(ev.time_slot);

      el.innerHTML = `
                <span class="event-time">${timeStr}</span>
                <div class="event-title">${escapeHtml(ev.title)}</div>
                 ${ev.venue ? `<div class="event-loc">📍 ${escapeHtml(ev.venue)}</div>` : ''}
                ${ev.organizer ? `<div class="event-loc" style="font-size:0.75rem">👤 ${escapeHtml(ev.organizer)}</div>` : ''}
            `;
      el.addEventListener('click', () => openModal(ev));
      sidebarListEl.appendChild(el);
    });
  }

  function getEventsForDay(dateObj) {
    // Matches DB date format YYYY-MM-DD
    const yMD = formatDateISO(dateObj);
    return eventsData.filter(ev => {
      return ev.date === yMD;
    }).sort((a, b) => {
      // Sort by time slot priority
      const order = { 'morning': 1, 'afternoon': 2, 'fullday': 0 };
      return (order[a.time_slot] || 99) - (order[b.time_slot] || 99);
    });
  }

  // --- Modal Logic ---

  function openModal(event = null) {
    modal.classList.add('active');
    const isEdit = !!event;

    modalTitle.innerText = isEdit ? 'Edit Event' : 'Add New Event';
    btnDelete.style.display = isEdit ? 'block' : 'none';

    // Populate or Clear
    document.getElementById('eventId').value = isEdit ? event.id : '';
    document.getElementById('inpTitle').value = isEdit ? event.title : '';
    document.getElementById('inpLocation').value = isEdit ? event.venue : ''; // venue -> location input
    document.getElementById('inpDesc').value = isEdit ? event.description : '';

    // Visibility is not in schema, default internal if needed or hide input
    // Using existing input ID 'inpVisibility' - leaving it as is, or hiding if schema implies NO visibility column.
    // The user prompted schema doesn't have visibility column. 
    // We will ignore it or let it default.

    if (isEdit) {
      // We need to map Date + Slot to Start/End inputs if we keep the datetime-local inputs
      // OR we should change the form to use Date + Slot select.
      // Since User said "Update JS only if necessary" but also provided a schema with time_slot,
      // we really SHOULD have updated the form structure to match schema (Date + Slot).
      // But strict requirement: "Update get_events.php... Update events_calendar.js only if necessary... List files changed".
      // If I don't change the form HTML, I have to fake the start/end inputs.
      // But form submission goes to create_event.php which I likely haven't fixed yet?
      // Wait, I am ONLY tasked to fix get_events.php and navigation.
      // "Task: Fix get_events.php so it correctly queries this schema... requirements... return JSON."
      // "Deliverable: Update get_events.php, update events_calendar.js only if necessary".

      // I will fill the start/end inputs with dummy times based on slot so formatting doesn't break
      const date = event.date;
      let sTime = '09:00', eTime = '17:00';
      if (event.time_slot === 'morning') { sTime = '09:00'; eTime = '12:00'; }
      if (event.time_slot === 'afternoon') { sTime = '12:00'; eTime = '17:00'; }

      const startStr = `${date}T${sTime}`;
      const endStr = `${date}T${eTime}`;

      if (document.getElementById('inpStart')) document.getElementById('inpStart').value = startStr;
      if (document.getElementById('inpEnd')) document.getElementById('inpEnd').value = endStr;

    } else {
      // Default
      if (selectedDate) {
        const base = formatDateISO(selectedDate);
        if (document.getElementById('inpStart')) document.getElementById('inpStart').value = `${base}T09:00`;
        if (document.getElementById('inpEnd')) document.getElementById('inpEnd').value = `${base}T17:00`;
      }
    }
  }

  function closeModal() {
    modal.classList.remove('active');
  }

  async function handleFormSubmit(e) {
    e.preventDefault();
    // NOTE: CREATE/UPDATE logic might still be broken if the PHP endpoints expect new columns.
    // But the user task is specifically about "Month navigation... Get events".
    // I will leave create/update logic as is unless instructed, or try to be safe.
    // The prompt says "Fix get_events.php". It didn't explicitly ask to fix create_event.php, but implied the schema is rigid.
    // I'll stick to get_events.php fix.

    const formData = new FormData(form);
    const isEdit = !!formData.get('id');
    const url = isEdit ? 'update_event.php' : 'create_event.php';

    try {
      const res = await fetch(url, {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        closeModal();
        fetchEvents(); // Refresh
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      alert('Network Error');
    }
  }

  async function handleDelete() {
    if (!confirm('Are you sure you want to delete this event?')) return;

    const id = document.getElementById('eventId').value;
    const token = document.querySelector('input[name="csrf_token"]').value;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', token);

    try {
      const res = await fetch('delete_event.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        closeModal();
        fetchEvents();
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      alert('Network Error');
    }
  }

  // --- Helpers ---
  function isSameDay(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
      d1.getMonth() === d2.getMonth() &&
      d1.getDate() === d2.getDate();
  }

  function formatDateISO(d) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function formatSlot(slot) {
    if (slot === 'morning') return 'Morning (9am - 12pm)';
    if (slot === 'afternoon') return 'Afternoon (12pm - 5pm)';
    return 'Full Day';
  }

  function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
  }
});
