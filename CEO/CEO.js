$(document).ready(function() {
    loadPendingRequests();
});

function loadPendingRequests() {
    $.ajax({
        url: 'CEOParameters.php',
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
        url: 'CEOParameters.php',
        type: 'POST',
        data: {
            action: 'get_request_details',
            requestId: requestId
        },
        success: function(response) {
            $('#requestDetailsContent').html(response);
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
            url: 'CEOParameters.php',
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
        url: 'CEOParameters.php',
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
