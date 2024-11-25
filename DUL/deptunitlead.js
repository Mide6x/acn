// Move these functions outside document.ready
window.approveDeptUnitLeadRequest = function(jdrequestid) {
    if (confirm('Are you sure you want to approve this request?')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'deptunitlead_approve',
                jdrequestid: jdrequestid
            },
            success: function(response) {
                if (response.includes('success')) {
                    alert('Request approved successfully');
                    $('#requestDetailsModal').modal('hide');
                    loadStaffRequests();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error approving request: ' + error);
            }
        });
    }
};

window.showDeclineModal = function(jdrequestid) {
    document.getElementById('decline_jdrequestid').value = jdrequestid;
    document.getElementById('decline_reason').value = '';
    new bootstrap.Modal(document.getElementById('declineModal')).show();
};

window.declineDeptUnitLeadRequest = function() {
    const jdrequestid = document.getElementById('decline_jdrequestid').value;
    const reason = document.getElementById('decline_reason').value.trim();

    if (!reason) {
        alert('Please provide a reason for declining');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'deptunitlead_decline',
            jdrequestid: jdrequestid,
            reason: reason
        },
        success: function(response) {
            if (response.includes('success')) {
                alert('Request declined successfully');
                bootstrap.Modal.getInstance(document.getElementById('declineModal')).hide();
                $('#requestDetailsModal').modal('hide');
                loadStaffRequests();
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            alert('Error declining request: ' + error);
        }
    });
};

// View request details
window.viewDeptUnitLeadRequest = function(jdrequestid) {
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            $('#requestDetailsModal .modal-body').html(response);
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading request details:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
};

function approveDeptUnitLeadStation(jdrequestid, station) {
    if (confirm('Are you sure you want to approve this station request?')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'approve_deptunitlead_station',
                jdrequestid: jdrequestid,
                station: station
            },
            success: function(response) {
                if (response.includes('success')) {
                    alert('Station request approved successfully');
                    viewDeptUnitLeadRequest(jdrequestid); 
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error approving station request: ' + error);
            }
        });
    }
}

function showDeclineStationModal(jdrequestid, station) {
    $('#decline_jdrequestid').val(jdrequestid);
    $('#decline_station').val(station);
    $('#declineStationModal').modal('show');
}

function declineDeptUnitLeadStation() {
    const jdrequestid = $('#decline_jdrequestid').val();
    const station = $('#decline_station').val();
    const reason = $('#decline_reason').val().trim();

    if (!reason) {
        alert('Please provide a reason for declining');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'decline_deptunitlead_station',
            jdrequestid: jdrequestid,
            station: station,
            reason: reason
        },
        success: function(response) {
            if (response.includes('success')) {
                alert('Station request declined successfully');
                $('#declineStationModal').modal('hide');
                viewDeptUnitLeadRequest(jdrequestid); // Refresh the modal
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            alert('Error declining station request: ' + error);
        }
    });
}

// Keep the document ready handler for initialization
$(document).ready(function() {
    // Debug log to confirm script is loading
    console.log('DeptUnitLead JS loaded');

    const form = document.getElementById('staffRequestForm');
    const addStationBtn = document.getElementById('addStation');
    const stationContainer = document.getElementById('stationContainer');
    const jdrequestidElement = document.getElementById('jdrequestid');
    const jdrequestid = jdrequestidElement ? jdrequestidElement.textContent.trim() : null;

    // Only add event listeners if we're on the create request page
    if (form && addStationBtn && stationContainer) {
        // Add new station entry
        addStationBtn.addEventListener('click', function() {
            const newStation = document.querySelector('.station-entry').cloneNode(true);
            newStation.querySelectorAll('input, select').forEach(input => {
                input.value = '';
            });
            stationContainer.appendChild(newStation);
        });

        // Save as Draft button click handler
        const submitButton = document.querySelector('button[onclick="return submitDeptUnitLeadRequest()"]');
        if (submitButton) {
            submitButton.onclick = function(e) {
                e.preventDefault();
                saveDeptUnitLeadDraft();
            };
        }
    }

    function saveDeptUnitLeadDraft() {
        if (!form || !jdrequestid) return;

        const formData = new FormData(form);
        formData.append('action', 'save_deptunitlead_draft');
        formData.append('jdrequestid', jdrequestid);

        // Collect all station data
        const stations = [];
        const employmentTypes = [];
        const staffPerStation = [];
        
        document.querySelectorAll('.station-entry').forEach(entry => {
            stations.push(entry.querySelector('[name="stations[]"]').value);
            employmentTypes.push(entry.querySelector('[name="employmentTypes[]"]').value);
            staffPerStation.push(entry.querySelector('[name="staffPerStation[]"]').value);
        });

        formData.append('stations', JSON.stringify(stations));
        formData.append('employmentTypes', JSON.stringify(employmentTypes));
        formData.append('staffPerStation', JSON.stringify(staffPerStation));

        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.includes("successfully")) {
                    alert('Request saved as draft successfully');
                    window.location.href = 'DeptUnitLead.php';
                } else {
                    alert(response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error saving draft: ' + error);
            }
        });
    }

    // Initial load of staff requests
    loadStaffRequests();

    // Bind click handler for view buttons using event delegation
    $(document).on('click', '.btn-view-request', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        viewDeptUnitLeadRequest(jdrequestid);
    });
});

// Function to load staff requests
function loadStaffRequests() {
    console.log('Loading staff requests...'); // Debug log
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_requests'
        },
        success: function(response) {
            console.log('Requests loaded successfully'); // Debug log
            $('#staffRequestsTable').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading requests:', error);
            $('#staffRequestsTable').html('<div class="alert alert-danger">Error loading requests</div>');
        }
    });
}

// View request details function
function viewDeptUnitLeadRequest(jdrequestid) {
    console.log('Viewing request:', jdrequestid); // Debug log
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            console.log('Response received'); // Debug log
            $('#requestDetailsModal .modal-body').html(response);
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
            $('#requestDetailsModal').modal('show');
        }
    });
}
