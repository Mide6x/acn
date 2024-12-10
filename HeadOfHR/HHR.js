$(document).ready(function() {
    loadPendingRequests();
});

function loadPendingRequests() {
    $.ajax({
        url: 'HHRParameters.php',
        type: 'POST',
        data: { action: 'get_pending_requests' },
        success: function(response) {
            $('#requestsTableBody').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $('#requestsTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>');
        }
    });
}

function viewDetails(requestId) {
    $.ajax({
        url: 'HHRParameters.php',
        type: 'POST',
        data: {
            action: 'get_request_details',
            requestId: requestId
        },
        success: function(response) {
            const details = JSON.parse(response);
            displayRequestDetails(details);
            $('#requestDetailsModal').modal('show');
            
            // Set up button handlers
            $('#approveBtn').off('click').on('click', function() {
                approveRequest(requestId);
            });
            
            $('#declineBtn').off('click').on('click', function() {
                declineRequest(requestId);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error loading request details. Please try again.');
        }
    });
}

function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this request?')) {
        $.ajax({
            url: 'HHRParameters.php',
            type: 'POST',
            data: {
                action: 'approve_request',
                requestId: requestId
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Request approved successfully');
                    $('#requestDetailsModal').modal('hide');
                    loadPendingRequests();
                } else {
                    alert('Error approving request: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error approving request. Please try again.');
            }
        });
    }
}

function declineRequest(requestId) {
    const comments = prompt('Please provide a reason for declining:');
    if (comments === null) return;
    
    if (comments.trim() === '') {
        alert('Please provide a reason for declining.');
        return;
    }

    $.ajax({
        url: 'HHRParameters.php',
        type: 'POST',
        data: {
            action: 'decline_request',
            requestId: requestId,
            comments: comments
        },
        success: function(response) {
            if (response === 'success') {
                alert('Request declined successfully');
                $('#requestDetailsModal').modal('hide');
                loadPendingRequests();
            } else {
                alert('Error declining request: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error declining request. Please try again.');
        }
    });
}

function displayRequestDetails(details) {
    // Create stations HTML
    let stationsHtml = '<div class="table-responsive"><table class="table table-bordered">';
    stationsHtml += '<thead><tr><th>Station</th><th>Staff Count</th><th>Employment Type</th><th>Status</th></tr></thead><tbody>';
    
    if (details.stations && details.stations.length > 0) {
        details.stations.forEach(station => {
            stationsHtml += `
                <tr>
                    <td>${station.station}</td>
                    <td>${station.staffperstation}</td>
                    <td>${station.employmenttype}</td>
                    <td>${station.status}</td>
                </tr>`;
        });
    } else {
        stationsHtml += '<tr><td colspan="4">No station details available</td></tr>';
    }
    stationsHtml += '</tbody></table></div>';

    const html = `
        <div class="container">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Request ID:</strong> ${details.jdrequestid || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Title:</strong> ${details.jdtitle || 'N/A'}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Department:</strong> ${details.department || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> ${details.status || 'N/A'}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Number of Vacancies:</strong> ${details.novacpost || 'N/A'}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Job Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Job Description:</strong>
                            <p>${details.jddescription || 'No description available'}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Educational Qualification:</strong>
                            <p>${details.eduqualification || 'Not specified'}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Professional Qualification:</strong>
                            <p>${details.proqualification || 'Not specified'}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Work Relationships:</strong>
                            <p>${details.workrelation || 'Not specified'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Station Details</h5>
                </div>
                <div class="card-body">
                    ${stationsHtml}
                </div>
            </div>
        </div>`;
    
    $('#requestDetailsContent').html(html);
}
