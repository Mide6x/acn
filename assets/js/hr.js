document.addEventListener('DOMContentLoaded', loadPendingRequests);

function loadPendingRequests() {
    console.log('Loading pending requests...');
    fetch('parameter/parameter.php?action=get_pending_requests')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            document.getElementById('pendingRequestsTable').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading pending requests:', error);
            document.getElementById('pendingRequestsTable').innerHTML = 
                '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
        });
}

function toggleStationDetails(jdrequestid, jdtitle) {
    // Show the modal first
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();

    // Fetch job title details
    fetch(`parameter/parameter.php?action=get_jobtitle_details&jdtitle=${encodeURIComponent(jdtitle)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data) {
                let detailsHtml = `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Job Title Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Job Title:</strong> ${data.jdtitle || 'N/A'}</p>
                                    <p><strong>Description:</strong> ${data.jddescription || 'N/A'}</p>
                                    <p><strong>Education Required:</strong> ${data.eduqualification || 'N/A'}</p>
                                    <p><strong>Professional Qualifications:</strong> ${data.proqualification || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Position:</strong> ${data.jdposition || 'N/A'}</p>
                                    <p><strong>Work Relations:</strong> ${data.workrelation || 'N/A'}</p>
                                    <p><strong>Age Requirements:</strong> ${data.agebracket || 'N/A'}</p>
                                    <p><strong>Additional Requirements:</strong> ${data.personspec || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('jobTitleDetails').innerHTML = detailsHtml;
            }
        })
        .catch(error => {
            console.error('Error fetching job details:', error);
            document.getElementById('jobTitleDetails').innerHTML = 
                '<div class="alert alert-danger">Error loading job details</div>';
        });

    // Fetch station requests
    refreshStationRequests(jdrequestid);
}

function refreshStationRequests(jdrequestid) {
    fetch(`parameter/parameter.php?action=get_station_requests&jdrequestid=${jdrequestid}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            document.getElementById('stationRequestsTable').innerHTML = data;
        })
        .catch(error => {
            console.error('Error fetching station requests:', error);
            document.getElementById('stationRequestsTable').innerHTML = 
                '<tr><td colspan="5" class="text-center text-danger">Error loading station requests</td></tr>';
        });
    }


function approveStation(jdrequestid, station) {
    if (confirm('Are you sure you want to approve this station request?')) {
        updateStationStatus(jdrequestid, station, 'approved');
    }
}

function showDeclineModal(jdrequestid, station) {
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
            // Refresh both tables
            refreshStationRequests(jdrequestid);
            loadPendingRequests();
        } else {
            alert('Error updating status: ' + data);
        }
    });
}