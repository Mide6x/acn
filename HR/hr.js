function viewRequestDetails(requestId, type) {
    const modalId = type === 'all-pending' ? '#allPendingModal' : '#hrOnlyModal';
    const contentId = type === 'all-pending' ? '#allPendingContent' : '#hrOnlyContent';
    
    $(contentId).html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading...</div>');
    $(modalId).modal('show');
    
    // Add this line to store the requestId
    $('#submitRequestBtn').data('requestid', requestId);
    
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
                $(buttonsContainer).find('button:not(.btn-secondary)').hide();
            } else {
                $(buttonsContainer).find('button').show();
            }
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

    // Load CEO approved requests when the tab is shown
    $('#ceo-approved-tab').on('shown.bs.tab', function() {
        $.post('HRParameters.php', { action: 'get_ceo_approved_requests' }, function(data) {
            $('#ceoApprovedRequestsTable').html(data);
        });
    });

    // Function to create a job listing
    window.createJobListing = function(requestId) {
        alert('Create job listing for request ID: ' + requestId);
        // Implement the logic to create a job listing
    };
    
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
        
        // Add submit decline button if it doesn't exist
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

function submitRequest() {
    // Prevent double submission
    const submitBtn = $('#submitRequestBtn');
    if (submitBtn.prop('disabled')) {
        return;
    }

    // Get form data
    const requestId = $('#requestId').val();
    const jdtitle = $('#jdtitle').val();

    // Validate required fields
    if (!requestId) {
        alert('Request ID is missing');
        return;
    }

    if (!jdtitle) {
        alert('Please select a job title');
        return;
    }

    // Get station data
    const stations = [];
    $('.station-request').each(function() {
        const stationSelect = $(this).find('select[name="station"]');
        const employmentSelect = $(this).find('select[name="employmenttype"]');
        const staffInput = $(this).find('input[name="staffperstation"]');

        if (stationSelect.val() && employmentSelect.val() && staffInput.val()) {
            stations.push({
                station: stationSelect.val(),
                employmenttype: employmentSelect.val(),
                staffperstation: staffInput.val()
            });
        }
    });

    // Validate stations
    if (stations.length === 0) {
        alert('Please add at least one station with complete information');
        return;
    }

    // Show confirmation dialog
    if (!confirm('Are you sure you want to submit this request?')) {
        return;
    }

    // Disable submit button to prevent double submission
    submitBtn.prop('disabled', true);

    // Make the AJAX request
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'submit_hr_request',
            requestId: requestId,
            jdtitle: jdtitle,
            stations: stations  // Send as array directly
        },
        success: function(response) {
            submitBtn.prop('disabled', false);
            if (response === 'success') {
                alert('Request submitted successfully');
                window.location.href = 'HRview.php';
            } else {
                alert('Error submitting request: ' + response);
            }
        },
        error: function(xhr, status, error) {
            submitBtn.prop('disabled', false);
            console.error('Ajax error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error submitting request. Please try again.');
        }
    });
}

// Add this at the appropriate place in your hr.js file
let stationIndex = 0;  // Keep track of station count

$(document).ready(function() {
    // Load stations and staff types
    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: { 
            action: 'get_stations_and_types'
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (!data || !data.stations || !data.staffTypes) {
                    throw new Error('Invalid response format');
                }
                
                const stationOptions = data.stations;
                const staffTypeOptions = data.staffTypes;
                
                // Add Station button click handler
                $('#addStationBtn').on('click', function() {
                    const stationHtml = `
                        <div class="station-request">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="form-label">Station</label>
                                    <select class="form-control" name="station" style="border-radius: 8px" required>
                                        <option value="">Select Station</option>
                                        ${stationOptions}
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Employment Type</label>
                                    <select class="form-control" name="employmenttype" style="border-radius: 8px" required>
                                        <option value="">Select Type</option>
                                        ${staffTypeOptions}
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Staff Per Station</label>
                                    <input type="number" class="form-control staffperstation" name="staffperstation"
                                        style="border-radius: 8px" required min="1">
                                </div>
                                <div class="col-sm-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger remove-station">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#stationRequests').append(stationHtml);
                    
                    // Add remove button handler
                    $('.remove-station').last().on('click', function() {
                        $(this).closest('.station-request').remove();
                        updateTotalPositions(); // Update total after removing
                    });
                    
                    // Update total positions after adding new station
                    updateTotalPositions();
                });
                
                // Initial station
                $('#addStationBtn').trigger('click');
                
            } catch (e) {
                console.error('Error parsing response:', e);
                console.error('Raw response:', response);
                alert('Error loading station options. Please refresh the page.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading stations and types:', error);
            console.error('Response:', xhr.responseText);
            alert('Error loading station options. Please refresh the page.');
        }
    });
    
    // Add change event handler for job title select
    $('#jdtitle').on('change', function() {
        const selectedTitle = $(this).val();
        console.log('Selected job title:', selectedTitle);
    });

    // Add submit handler to the form
    $('#staffRequestForm').on('submit', function(e) {
        e.preventDefault();
        submitRequest();
    });

    // Add change event handler for staffperstation inputs
    $(document).on('change', '.staffperstation', function() {
        updateTotalPositions();
    });
});

// Function to update total positions
function updateTotalPositions() {
    let total = 0;
    $('.staffperstation').each(function() {
        const value = parseInt($(this).val()) || 0;
        total += value;
    });
    // Update total positions display if it exists
    if ($('#total_positions').length) {
        $('#total_positions').val(total);
    }
}

// Add save draft functionality
function savedraftHRstaffrequest() {
    // Get form data
    const jdtitle = $('#jdtitle').val();
    const jdrequestid = $('#jdrequestid').text().trim();
    
    // Get station data
    const stations = [];
    $('.station-request').each(function() {
        const station = {
            station: $(this).find('select[name="station"]').val(),
            employmenttype: $(this).find('select[name="employmenttype"]').val(),
            staffperstation: $(this).find('input[name="staffperstation"]').val()
        };
        stations.push(station);
    });

    $.ajax({
        url: 'HRParameters.php',
        type: 'POST',
        data: {
            action: 'save_draft_hr_request',
            jdrequestid: jdrequestid,
            jdtitle: jdtitle,
            stations: stations
        },
        success: function(response) {
            if (response === 'success') {
                alert('Draft saved successfully');
            } else {
                alert('Error saving draft: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error saving draft. Please try again.');
        }
    });
}