// audit_logs_viewer.js - Audit Logs Viewer Interactions
// CSP Compliant: External JavaScript file

/**
 * Display audit log details in modal
 * @param {Object} log - Log object with all audit details
 */
function showDetails(log) {
  document.getElementById('detailId').textContent = log.id || '-';
  document.getElementById('detailUser').textContent = (log.username || '-') + ' (' + (log.user_role || '-') + ')';
  document.getElementById('detailAction').textContent = log.action || '-';
  document.getElementById('detailTable').textContent = log.table_name || '-';
  document.getElementById('detailRecordId').textContent = log.record_id || '-';
  document.getElementById('detailIp').textContent = log.ip_address || '-';
  document.getElementById('detailTimestamp').textContent = log.created_at || '-';
  document.getElementById('detailStatus').textContent = log.status || '-';
  document.getElementById('detailError').textContent = log.error_message || 'None';
  
  // Parse and display old values
  try {
    const oldVals = log.old_values ? JSON.parse(log.old_values) : null;
    document.getElementById('detailOldValues').textContent = oldVals ? JSON.stringify(oldVals, null, 2) : '-';
  } catch (e) {
    document.getElementById('detailOldValues').textContent = log.old_values || '-';
  }
  
  // Parse and display new values
  try {
    const newVals = log.new_values ? JSON.parse(log.new_values) : null;
    document.getElementById('detailNewValues').textContent = newVals ? JSON.stringify(newVals, null, 2) : '-';
  } catch (e) {
    document.getElementById('detailNewValues').textContent = log.new_values || '-';
  }
  
  document.getElementById('detailsModal').classList.add('active');
}

/**
 * Hide audit log details modal
 */
function hideDetails() {
  document.getElementById('detailsModal').classList.remove('active');
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    hideDetails();
  }
});
