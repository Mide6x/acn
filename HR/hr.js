function viewRequestDetails(requestId) {
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'get_request_details',
            requestId: requestId
        },
        success: function(response) {
            // Store the requestId in the modal's data attribute
            $('#requestDetailsModal').data('requestid', requestId);
            
            // Set the modal content
            $('#requestDetailsModal .modal-body').html(response);
            
            // Show the modal footer (in case it was hidden)
            $('.modal-footer').show();
            
            // Remove any existing decline reason section
            $('#declineReasonSection').remove();
            
            // Initialize any tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Show the modal
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error loading request details. Please try again.');
        }
    });
}

// Document ready handler
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Approve button click handler
    $('#approveRequestBtn').click(function() {
        const requestId = $(this).data('requestid');
        
        // Confirm before proceeding
        if (confirm('Are you sure you want to approve this request?')) {
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
                        // Refresh the main table if needed
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
    });
    
    // Decline button click handler
    $('#declineRequestBtn').click(function() {
        const requestId = $('#requestDetailsModal').data('requestid');
        
        // Add reason input field before the modal footer
        const reasonHtml = `
            <div id="declineReasonSection" class="px-3 pb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Decline Reason</h6>
                        <div class="form-group">
                            <textarea class="form-control" id="declineReason" 
                                rows="3" placeholder="Please provide a reason for declining this request"></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-danger" id="submitDeclineBtn">
                                Submit Decline
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelDeclineBtn">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        
        // Remove existing decline reason section if it exists
        $('#declineReasonSection').remove();
        
        // Hide the modal footer
        $('.modal-footer').hide();
        
        // Add the new decline reason section before the modal footer
        $('.modal-footer').before(reasonHtml);
        
        // Handle submit decline
        $('#submitDeclineBtn').click(function() {
            const reason = $('#declineReason').val().trim();
            
            if (!reason) {
                alert('Please provide a reason for declining the request.');
                return;
            }
            
            if (confirm('Are you sure you want to decline this request?')) {
                $.ajax({
                    url: 'HRParameters.php',
                    type: 'POST',
                    data: {
                        action: 'decline_request',
                        requestId: requestId,
                        comments: reason
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
        });
        
        // Handle cancel
        $('#cancelDeclineBtn').click(function() {
            $('#declineReasonSection').remove();
            $('.modal-footer').show();
        });
    });
});
