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
});
