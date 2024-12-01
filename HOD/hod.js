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
            $('#requestDetailsModal .modal-body').html(response);
            $('#requestDetailsModal').modal('show');
        }
    });
}

function updateStatus(requestId, status) {
    let comments = '';
    if (status === 'declined') {
        comments = prompt('Please provide a reason for declining:');
        if (comments === null) return; // User cancelled
    }

    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'updateStatus',
            requestId: requestId,
            status: status,
            comments: comments
        },
        success: function(response) {
            loadStaffRequests(); // Reload the table
            alert('Status updated successfully');
        }
    });
}

// Initialize when document is ready
$(document).ready(function() {
    loadStaffRequests();
});
