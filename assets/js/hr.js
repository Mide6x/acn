document.addEventListener('DOMContentLoaded', loadPendingRequests);

function loadPendingRequests() {
    fetch('parameter/parameter.php?action=get_pending_requests')
        .then(response => response.text())
        .then(data => {
            document.getElementById('pendingRequestsTable').innerHTML = data;
        });
}

function toggleStationDetails(jdrequestid) {
    const detailsRow = document.getElementById(`stations-${jdrequestid}`);
    detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
}

function approveStation(jdrequestid, station) {
    if (confirm('Are you sure you want to approve this station request?')) {
        updateStationStatus(jdrequestid, station, 'approved');
    }
}

function declineStation(jdrequestid, station) {
    document.getElementById('decline_jdrequestid').value = jdrequestid;
    document.getElementById('decline_station').value = station;
    document.getElementById('decline_reason').value = '';
    
    new bootstrap.Modal(document.getElementById('declineModal')).show();
}

function submitDecline() {
    const jdrequestid = document.getElementById('decline_jdrequestid').value;
    const station = document.getElementById('decline_station').value;
    const reason = document.getElementById('decline_reason').value;

    if (!reason.trim()) {
        alert('Please provide a reason for declining');
        return;
    }

    updateStationStatus(jdrequestid, station, 'declined', reason);
    bootstrap.Modal.getInstance(document.getElementById('declineModal')).hide();
}

function updateStationStatus(jdrequestid, station, status, reason = null) {
    const formData = new FormData();
    formData.append('action', 'update_station_status');
    formData.append('jdrequestid', jdrequestid);
    formData.append('station', station);
    formData.append('status', status);
    if (reason) formData.append('reason', reason);

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            loadPendingRequests();
        } else {
            alert('Error updating status: ' + data);
        }
    });
}