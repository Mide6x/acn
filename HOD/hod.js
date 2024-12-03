function loadStaffRequests() {
    console.log('Loading staff requests...');
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getPendingRequests'
        },
        success: function(response) {
            $('#staffRequestTableBody').html(response);
        },
        error: function(xhr, status, error) {
            $('#staffRequestTableBody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load requests</td></tr>');
            console.error('Ajax error:', error);
        }
    });
}

function viewDetails(requestId) {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getRequestDetails',
            requestId: requestId
        },
        success: function(response) {
            $('#modal-content').html(response);
            $('#requestDetailsModal').modal('show');
        },
        error: function() {
            alert('Failed to load request details.');
        }
    });
}

function updateRequestStatus(requestId, status) {
    let comments = '';
    if (status === 'declined') {
        comments = prompt('Please provide a reason for declining:');
        if (comments === null) return; // User cancelled
        if (comments.trim() === '') {
            alert('Please provide a reason for declining.');
            return;
        }
    }

    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'updateStationStatus',
            requestId: requestId,
            status: status,
            comments: comments
        },
        success: function(response) {
            alert(response);
            $('#requestDetailsModal').modal('hide');
            loadStaffRequests(); // Reload the main table
        },
        error: function() {
            alert('Failed to update status');
        }
    });
}

// Initialize when document is ready
$(document).ready(function() {
    loadStaffRequests();
    $('#addStation').click(function() {
        addStationRequestHOD();
    });
    loadMyRequests();
});

function addStationRequestHOD() {
    const index = $('.station-request').length;
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'get_station_options',
            index: index
        },
        success: function(response) {
            if (response.trim() !== '') {
                $('#stationRequests').append(response);
            } else {
                alert('Failed to load station options.');
            }
        },
        error: function() {
            alert('Error adding new station');
        }
    });
}

function submitHODRequest() {
    const formData = $('#hodRequestForm').serialize();
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'createHODRequest',
            formData: formData
        },
        success: function(response) {
            alert(response);
            window.location.href = 'HODView.php'; // Redirect to HOD view page
        },
        error: function() {
            alert('Failed to submit request');
        }
    });
}

function savedraftHODstaffrequest() {
    const formData = $('#staffRequestForm').serialize();
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'createHODRequest',
            formData: formData
        },
        success: function(response) {
            alert(response);
            window.location.href = 'HODView.php'; // Redirect to view page
        },
        error: function() {
            alert('Failed to submit request');
        }
    });
}

function loadMyRequests() {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: { action: 'getMyRequests' },
        success: function(response) {
            const requests = JSON.parse(response);
            const tbody = $('#requestsTable tbody');
            tbody.empty();
            requests.forEach(request => {
                const row = `<tr>
                    <td>${request.jdrequestid}</td>
                    <td>${request.jdtitle}</td>
                    <td>${request.status}</td>
                    <td>${request.dandt}</td>
                    <td><button class="btn btn-info" onclick="viewRequestDetails('${request.jdrequestid}')">View Details</button></td>
                </tr>`;
                tbody.append(row);
            });
        },
        error: function() {
            alert('Failed to load requests');
        }
    });
}

function viewRequestDetails(jdrequestid) {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: { action: 'getRequestDetails', jdrequestid: jdrequestid },
        success: function(response) {
            $('#jobDetails').html(response);
            $('#detailsModal').modal('show');
        },
        error: function() {
            alert('Failed to load request details');
        }
    });
}
