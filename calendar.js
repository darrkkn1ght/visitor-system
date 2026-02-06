// Calendar JavaScript - External file to comply with CSP
document.addEventListener('DOMContentLoaded', function() {
    // Get events data from window object (passed from PHP)
    const eventsData = window.eventsData || {};
    
    // Escape HTML special characters to prevent XSS
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

    // Modal functions
    window.showEventDetails = function(date) {
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
                // Escape all user-controlled data
                const escapedTitle = escapeHtml(event.event_title);
                const escapedOrganizer = escapeHtml(event.organizer_name);
                const escapedVenue = escapeHtml(event.venue || 'TBD');
                const escapedSlot = escapeHtml(event.time_slot);
                
                html += `
                <div class="event-detail-item">
                    <div class="event-detail-title">${escapedTitle}</div>
                    <div class="event-detail-info">
                        <strong>Organizer:</strong> ${escapedOrganizer}
                    </div>
                    <div class="event-detail-info">
                        <strong>Venue:</strong> ${escapedVenue}
                    </div>
                    <div class="event-detail-info event-detail-slot">
                        <strong>Slot:</strong> <span class="badge ${escapedSlot}">${escapedSlot}</span>
                    </div>
                </div>`;
            });
            modalBody.innerHTML = html;
        }
        
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    };

    window.closeModal = function() {
        const modal = document.getElementById('eventModal');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    };

    window.openCreateEventModal = function() {
        const createModal = document.getElementById('createEventModal');
        createModal.style.display = 'block';
        document.body.classList.add('modal-open');
    };

    window.closeCreateEventModal = function() {
        const createModal = document.getElementById('createEventModal');
        createModal.style.display = 'none';
        document.body.classList.remove('modal-open');
        // Reset form
        document.getElementById('createEventForm').reset();
        document.getElementById('createEventMessage').innerHTML = '';
        // Hide custom message group
        document.getElementById('customMessageGroup').style.display = 'none';
    };

    window.toggleCustomMessage = function(value) {
        const customGroup = document.getElementById('customMessageGroup');
        customGroup.style.display = value === 'custom' ? 'block' : 'none';
    };

    // Handle form submission via AJAX
    window.createEvent = function(event) {
        event.preventDefault();
        const formData = new FormData(document.getElementById('createEventForm'));
        const messageDiv = document.getElementById('createEventMessage');
        
        fetch('create_event_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                // Refresh calendar after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            }
        })
        .catch(error => {
            messageDiv.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
        });
    };

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const eventModal = document.getElementById('eventModal');
        const createModal = document.getElementById('createEventModal');
        
        if (event.target == eventModal) {
            window.closeModal();
        }
        if (event.target == createModal) {
            window.closeCreateEventModal();
        }
    });

    // Add event listeners for modal close buttons
    const modalCloseButtons = document.querySelectorAll('.close');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Add event listener for create event button
    const createEventBtn = document.getElementById('createEventBtn');
    if (createEventBtn) {
        createEventBtn.addEventListener('click', openCreateEventModal);
    }

    // Add event listener for cancel button
    const cancelCreateBtn = document.getElementById('cancelCreateBtn');
    if (cancelCreateBtn) {
        cancelCreateBtn.addEventListener('click', closeCreateEventModal);
    }

    // Add event listener for create event form
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        createEventForm.addEventListener('submit', createEvent);
    }

    // Add event listener for success message select
    const successMessageSelect = document.getElementById('successMessageSelect');
    if (successMessageSelect) {
        successMessageSelect.addEventListener('change', function() {
            toggleCustomMessage(this.value);
        });
    }

    // Add event listeners for calendar days
    const calendarDays = document.querySelectorAll('.day[data-date]');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const date = this.getAttribute('data-date');
            showEventDetails(date);
        });
    });
});
