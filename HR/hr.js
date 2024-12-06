function viewRequestDetails(requestId) {
    // Show loading state
    $('#requestDetailsContent').html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading...</div>');
    $('#requestDetailsModal').modal('show');
    $('#declineCommentsSection').hide();
    
    // Reset and show action buttons
    $('#actionButtons').show();
    $('#approveRequestBtn, #declineRequestBtn').show();
    
    // Store requestId in buttons
    $('#approveRequestBtn, #declineRequestBtn').data('requestid', requestId);
    
    // Fetch request details
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'get_request_details',
            requestId: requestId
        },
        success: function(response) {
            $('#requestDetailsContent').html(response);
            
            // Get status values from hidden inputs
            const hodStatus = $('#hodStatus').val();
            const hrStatus = $('#hrStatus').val();
            const hohrStatus = $('#hohrStatus').val();
            
            // Update timeline dots based on status
            updateTimelineDot('hodDot', hodStatus);
            updateTimelineDot('hrDot', hrStatus);
            updateTimelineDot('hohrDot', hohrStatus);
            
            // Show/hide buttons based on request status
            const requestStatus = $('#requestStatus').val();
            const createdByDept = $('#createdByDept').val();
            
            if (createdByDept !== 'HRD' && requestStatus === 'pending') {
                $('#actionButtons').show();
            } else {
                $('#actionButtons').hide();
            }
        },
        error: function(xhr, status, error) {
            $('#requestDetailsContent').html(
                '<div class="alert alert-danger">Error loading request details: ' + error + '</div>'
            );
        }
    });
}

function updateTimelineDot(dotId, status) {
    const dot = $('#' + dotId);
    dot.removeClass('completed current declined');
    
    switch(status) {
        case 'approved':
            dot.addClass('completed');
            break;
        case 'pending':
            dot.addClass('current');
            break;
        case 'declined':
            dot.addClass('declined');
            break;
        default:
            // Leave as is
            break;
    }
}

// Event handlers
$(document).ready(function() {
    // Load HR requests by default
    loadHRRequests();
    
    // Add tab change event listeners
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("data-bs-target");
        if (target === '#hr-requests') {
            loadHRRequests();
        } else if (target === '#other-requests') {
            loadOtherRequests();
        }
    });
    
    $('#approveRequestBtn').click(function() {
        const requestId = $(this).data('requestid');
        if (confirm('Are you sure you want to approve this request?')) {
            approveRequest(requestId);
        }
    });
    
    $('#declineRequestBtn').click(function() {
        const requestId = $(this).data('requestid');
        $('#declineCommentsSection').show();
        $('#actionButtons').hide();
        
        // Add submit decline button
        if (!$('#submitDeclineBtn').length) {
            const submitBtn = $('<button type="button" class="btn btn-danger" id="submitDeclineBtn">Submit Decline</button>');
            $('#actionButtons').after(submitBtn);
        }
    });
    
    $(document).on('click', '#submitDeclineBtn', function() {
        const requestId = $('#declineRequestBtn').data('requestid');
        const comments = $('#declineComments').val().trim();
        
        if (!comments) {
            alert('Please provide a reason for declining');
            return;
        }
        
        declineRequest(requestId, comments);
    });
});

function approveRequest(requestId) {
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'approve_request',
            requestId: requestId
        },
        success: function(response) {
            if (response === 'success') {
                alert('Request approved successfully');
                $('#requestDetailsModal').modal('hide');
                location.reload();
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

function declineRequest(requestId, comments) {
    $.ajax({
        url: 'HRParameters.php',
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
                location.reload();
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

function loadHRRequests() {
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: { action: 'load_hr_requests' },
        success: function(response) {
            $('#hrRequestsTable').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading HR requests:', error);
            $('#hrRequestsTable').html(
                '<tr><td colspan="5" class="text-center text-danger">Error loading requests: ' + error + '</td></tr>'
            );
        }
    });
}

function loadOtherRequests() {
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: { action: 'load_other_requests' },
        success: function(response) {
            $('#otherRequestsTable').html(response);
        },
        error: function(xhr, status, error) {
            $('#otherRequestsTable').html(
                '<tr><td colspan="6" class="text-center text-danger">Error loading requests: ' + error + '</td></tr>'
            );
        }
    });
}