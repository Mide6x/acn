function viewRequestDetails(requestId, type) {
    const modalId = type === 'all-pending' ? '#allPendingModal' : '#hrOnlyModal';
    const contentId = type === 'all-pending' ? '#allPendingContent' : '#hrOnlyContent';
    
    $(contentId).html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading...</div>');
    $(modalId).modal('show');
    
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: type === 'all-pending' ? 'get_request_details' : 'get_hr_request_details',
            requestId: requestId
        },
        success: function(response) {
            $(contentId).html(response);
            
            // Get the status from the hidden input
            const status = $('#requestStatus').val();
            const buttonsContainer = type === 'all-pending' ? '#allPendingButtons' : '#hrOnlyButtons';
            
            // Show/hide buttons based on status
            if (status === 'approved' || status === 'declined') {
                // Only show close button for approved/declined requests
                $(buttonsContainer).find('button:not(.btn-secondary)').hide();
            } else {
                // Show approve/decline buttons for pending requests
                $(buttonsContainer).find('button').show();
            }
            
            // Update approve/decline button data
            $('#approveBtn, #declineBtn').data('requestid', requestId);
        },
        error: function(xhr, status, error) {
            $(contentId).html('<div class="alert alert-danger">Error loading request details: ' + error + '</div>');
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
    
    $('#approveBtn').click(function() {
        const requestId = $(this).data('requestid');
        if (confirm('Are you sure you want to approve this request?')) {
            approveRequest(requestId);
        }
    });
    
    $('#declineBtn').click(function() {
        const requestId = $(this).data('requestid');
        $('#declineCommentsSection').show();
        $('#allPendingButtons').hide();
        
        // Add submit decline button
        if (!$('#submitDeclineBtn').length) {
            const submitBtn = $('<button type="button" class="btn btn-danger" id="submitDeclineBtn">Submit Decline</button>');
            $('#declineCommentsSection').after(submitBtn);
        }
    });
    
    $(document).on('click', '#submitDeclineBtn', function() {
        const requestId = $('#declineBtn').data('requestid');
        const comments = $('#declineComments').val().trim();
        
        if (!comments) {
            alert('Please provide a reason for declining');
            return;
        }
        
        declineRequest(requestId, comments);
    });
    
    // Edit button handler
    $(document).on('click', '#editRequestBtn', function() {
        const requestId = $(this).data('requestid');
        window.location.href = 'edit_requesthr.php?id=' + requestId;
    });
    
    // Submit button handler - Use a single event handler
    $(document).on('click', '#submitRequestBtn', function() {
        submitRequest();
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

// Single submission function
function submitRequest() {
    const requestId = $('#requestId').val(); // Retrieve the requestId from the hidden input

    if (!requestId) {
        alert('Request ID is missing.');
        return;
    }

    if (confirm('Are you sure you want to submit this request? You won\'t be able to edit it after submission.')) {
        $.ajax({
            url: 'HRParameters.php',
            type: 'POST',
            data: {
                action: 'submit_hr_request',
                requestId: requestId
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Request submitted successfully');
                    window.location.href = 'HRview.php'; // Redirect to the view page after successful submission
                } else {
                    alert('Error submitting request: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error submitting request. Please try again.');
            }
        });
    }
}

// Functions for HR Only requests
function editRequest(requestId) {
    window.location.href = 'edit_requesthr.php?id=' + requestId;
}

function submitRequest() {
    const requestId = $('#requestId').val(); // Retrieve the requestId from the hidden input

    if (!requestId) {
        alert('Request ID is missing.');
        return;
    }

    if (confirm('Are you sure you want to submit this request? You won\'t be able to edit it after submission.')) {
        $.ajax({
            url: 'HRParameters.php',
            type: 'POST',
            data: {
                action: 'submit_hr_request',
                requestId: requestId
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Request submitted successfully');
                    $('#hrOnlyModal').modal('hide');
                    loadHRRequests();
                } else {
                    alert('Error submitting request: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error submitting request. Please try again.');
            }
        });
    }
}

// Add this at the appropriate place in your hr.js file
let stationIndex = 0;  // Keep track of station count

$(document).ready(function() {
    // Add Station button click handler
    $('#addStationBtn').on('click', function() {
        stationIndex++;  // Increment counter for unique field names
        
        $.ajax({
            url: 'HRParameters.php',
            type: 'POST',
            data: {
                action: 'get_station_options',
                index: stationIndex
            },
            success: function(response) {
                $('#stationRequests').append(response);
                
                // Add remove button handler for the new station
                $('.remove-station').last().on('click', function() {
                    $(this).closest('.station-request').remove();
                    updateTotalPositions();
                });
                
                // Add change handler for the new staffperstation input
                $('.staffperstation').last().on('change', function() {
                    updateTotalPositions();
                });
            },
            error: function(xhr, status, error) {
                alert('Error adding station: ' + error);
            }
        });
    });
    
    // Helper function to update total positions
    function updateTotalPositions() {
        let total = 0;
        $('.staffperstation').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        $('#total_positions').val(total);
    }
    
    // Initial station
    $('#addStationBtn').trigger('click');
});