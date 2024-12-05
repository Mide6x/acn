function viewRequestDetails(requestId) {
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'get_request_details',
            requestId: requestId
        },
        success: function(response) {
            console.log('Response:', response);
            $('#requestDetailsModal .modal-body').html(response);
            
            // Store the requestId for approve/decline actions
            $('#approveRequestBtn').data('requestid', requestId);
            $('#declineRequestBtn').data('requestid', requestId);
            
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
        const requestId = $(this).data('requestid');
        console.log('Decline request:', requestId);
    });
});
