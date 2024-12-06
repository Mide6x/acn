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
    
    // Add Station button click handler
    $('#addStationBtn').on('click', function() {
        addStationRequestHR();
    });
    
    // Save Draft button click handler
    $('#saveDraftBtn').on('click', function() {
        savedraftHRstaffrequest();
    });
    
    // Submit button click handler
    $('#submitRequestBtn').on('click', function() {
        submitRequest();
    });
});

function addStationRequestHR() {
    const index = $('.station-request').length;
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'get_station_options',
            index: index
        },
        success: function(response) {
            if (response.trim() !== '') {
                $('#stationRequests').append(response);
                
                // Add remove station functionality
                $('.remove-station').off('click').on('click', function() {
                    $(this).closest('.station-request').remove();
                });
                
                // Add staff count validation
                $('.staffperstation').off('change').on('change', function() {
                    validateStaffCount();
                });
            } else {
                alert('Failed to load station options.');
            }
        },
        error: function() {
            alert('Error adding new station');
        }
    });
}

// Helper function to validate total staff count
function validateStaffCount() {
    let total = 0;
    $('.staffperstation').each(function() {
        total += parseInt($(this).val()) || 0;
    });
    
    const availablePositions = parseInt($('#availablevacant').text().match(/\d+/)[0]);
    
    if (total > availablePositions) {
        alert('Total staff count cannot exceed available positions');
        return false;
    }
    return true;
}

// Add event handler for remove station buttons
$(document).on('click', '.remove-station', function() {
    $(this).closest('.station-request').remove();
});

function savedraftHRstaffrequest() {
    // Validate form
    if (!validateForm()) {
        return;
    }

    // Collect form data
    const formData = {
        action: 'save_draft_request',
        jdrequestid: $('#jdrequestid').text().trim(),
        jdtitle: $('#jdtitle').val(),
        stations: []
    };

    // Collect station data
    $('.station-request').each(function() {
        const stationData = {
            station: $(this).find('select[name*="[station]"]').val(),
            employmenttype: $(this).find('select[name*="[employmenttype]"]').val(),
            staffperstation: $(this).find('input[name*="[staffperstation]"]').val()
        };
        formData.stations.push(stationData);
    });

    // Calculate total positions
    const totalPositions = formData.stations.reduce((sum, station) => 
        sum + parseInt(station.staffperstation || 0), 0);
    formData.total_positions = totalPositions;

    // Send AJAX request
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            ...formData,
            stations: JSON.stringify(formData.stations)
        },
        success: function(response) {
            if (response === 'success') {
                alert('Request saved as draft successfully');
                window.location.href = 'HRview.php'; // Redirect to dashboard
            } else {
                alert('Error saving draft: ' + response);
            }
        },
        error: function(xhr, status, error) {
            alert('Error saving draft request');
            console.error(error);
        }
    });
}

function validateForm() {
    // Check if job title is selected
    if (!$('#jdtitle').val()) {
        alert('Please select a job title');
        return false;
    }

    // Check if at least one station is added
    if ($('.station-request').length === 0) {
        alert('Please add at least one station');
        return false;
    }

    // Validate each station
    let isValid = true;
    $('.station-request').each(function() {
        const station = $(this).find('select[name*="[station]"]').val();
        const employmentType = $(this).find('select[name*="[employmenttype]"]').val();
        const staffCount = $(this).find('input[name*="[staffperstation]"]').val();

        if (!station || !employmentType || !staffCount) {
            alert('Please fill in all station details');
            isValid = false;
            return false;
        }
    });

    // Validate total staff count
    if (!validateStaffCount()) {
        return false;
    }

    return isValid;
}