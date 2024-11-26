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
    const station = document.getElementById('decline_station').value;
    const reason = document.getElementById('decline_reason').value.trim();

    console.log('Declining request:', jdrequestid, station, reason); // Debug log

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
            console.log('Decline response:', response); // Debug log
            if (response.includes('success')) {
                alert('Request declined successfully');
                $('#declineModal').modal('hide');
                // Refresh the request details
                viewDeptUnitLeadRequest(jdrequestid);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error declining request:', error);
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

    console.log('Submitting decline:', { jdrequestid, station, reason }); // Debug log

    if (!reason) {
        alert('Please provide a reason for declining');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'decline_deptunitlead_station',
            jdrequestid,
            station,
            reason
        },
        success: function(response) {
            console.log('Response:', response);
            if (response.includes('success')) {
                alert('Request declined successfully');
                $('#declineModal').modal('hide');
                viewDeptUnitLeadRequest(jdrequestid);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error declining request: ' + error);
        }
    });
}

// Document ready handler
$(document).ready(function() {
    console.log('DeptUnitLead JS loaded');

    // Bind click handler for view buttons
    $(document).on('click', '.btn-view-request', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        viewDeptUnitLeadRequest(jdrequestid);
    });

    // Bind click handler for approve buttons using event delegation
    $(document).on('click', '.btn-approve-station', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        const station = $(this).data('station');
        approveDeptUnitLeadStation(jdrequestid, station);
    });

    // Single click handler for decline buttons
    $(document).on('click', '.btn-decline-station', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        const station = $(this).data('station');
        
        console.log('Decline button clicked:', { jdrequestid, station });
        
        // Set the values in modal before showing it
        $('#decline_jdrequestid').val(jdrequestid);
        $('#decline_station').val(station);
        $('#decline_reason').val('');
        
        // Show the modal
        $('#declineModal').modal('show');
    });

    // Listen for modal opening
    $('#declineModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const requestId = button.data('requestid');
        const station = button.data('station');
        
        console.log('Modal opening with values:', { requestId, station });
        
        // Set values in hidden fields
        $(this).find('#decline_jdrequestid').val(requestId);
        $(this).find('#decline_station').val(station);
        $(this).find('#decline_reason').val('');
        
        // Debug check
        console.log('Values set in modal:', {
            jdrequestid: $('#decline_jdrequestid').val(),
            station: $('#decline_station').val()
        });
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
    console.log('Viewing request:', jdrequestid);
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
            console.error('Error:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
}

// Approve station function
function approveDeptUnitLeadStation(jdrequestid, station) {
    console.log('Approving station:', jdrequestid, station);
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
                console.log('Approval response:', response);
                if (response.includes('success')) {
                    alert('Station request approved successfully');
                    // Refresh the request details
                    viewDeptUnitLeadRequest(jdrequestid);
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error approving station request: ' + error);
            }
        });
    }
}

// Update these functions
function showDeclineModal(jdrequestid, station) {
    console.log('Showing decline modal for:', jdrequestid, station); // Debug log
    
    // Store the values in hidden fields
    document.getElementById('decline_jdrequestid').value = jdrequestid;
    document.getElementById('decline_station').value = station;
    
    // Clear any previous reason
    document.getElementById('decline_reason').value = '';
    
    // Show the decline modal
    $('#declineModal').modal('show');
}
console.log('Values set in modal:', {
    jdrequestid: $('#decline_jdrequestid').val(),
    station: $('#decline_station').val()
});
