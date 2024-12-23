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
    loadHODRequests();
    loadDepartmentRequests();
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

function loadHODRequests() {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getHODRequests'
        },
        success: function(response) {
            $('#hodRequestTableBody').html(response);
        },
        error: function() {
            alert('Failed to load HOD requests.');
        }
    });
}

function viewJobDetails(jdtitle, requestId) {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getJobDetails',
            jdtitle: jdtitle,
            requestId: requestId
        },
        success: function(response) {
            $('#jobDetailsModal .modal-body').html(response);
            
            // Check if request is in draft status
            const requestStatus = $('#requestStatus').val();
            if (requestStatus === 'draft') {
                $('#editRequestBtn').show().attr('data-requestid', requestId);
                $('#submitDraftRequestBtn').show().attr('data-requestid', requestId);
            } else {
                $('#editRequestBtn').hide();
                $('#submitDraftRequestBtn').hide();
            }
            
            $('#jobDetailsModal').modal('show');
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

function loadStationDetails(jdtitle) {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getStationDetails',
            jdtitle: jdtitle
        },
        success: function(response) {
            console.log('Station Details Response:', response); // Debug log
            $('#stationDetails').html(response);
            $('#requestDetailsModal').modal('show'); // Ensure modal is shown after loading data
        },
        error: function() {
            alert('Failed to load station details.');
        }
    });
}

function editRequest() {
    // Implement edit functionality
    alert('Edit functionality to be implemented.');
}

function submitRequest(requestId) {
    if (confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
        $.ajax({
            url: 'HODParameters.php',
            type: 'POST',
            data: {
                action: 'submitRequest',
                requestId: requestId
            },
            success: function(response) {
                alert(response);
                $('#requestDetailsModal').modal('hide');
                loadHODRequests(); // Reload the requests table
            },
            error: function() {
                alert('Failed to submit request.');
            }
        });
    }
}

function checkRequestStatus(requestId) {
    console.log('CheckRequestStatus - RequestID:', requestId); // Debug log
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'checkRequestStatus',
            requestId: requestId
        },
        success: function(response) {
            console.log('Status Response:', response); // Debug log
            if (response === 'draft') {
                console.log('Showing buttons'); // Debug log
                $('#submitRequestBtn').show();
                $('#editRequestBtn').show();
            } else {
                console.log('Hiding buttons'); // Debug log
                $('#submitRequestBtn').hide();
                $('#editRequestBtn').hide();
            }
        }
    });
}

function loadDepartmentRequests() {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getHODDepartmentRequests'
        },
        success: function(response) {
            $('#staffRequestTableBody').html(response);
        },
        error: function() {
            alert('Failed to load department requests.');
        }
    });
}

function viewDepartmentRequestDetails(requestId) {
    $.ajax({
        url: 'HODParameters.php',
        type: 'POST',
        data: {
            action: 'getDepartmentRequestDetails',
            requestId: requestId
        },
        success: function(response) {
            $('#departmentRequestDetails').html(response);
            
            // Show approve/decline buttons
            $('#approveBtn').data('requestId', requestId).show();
            $('#declineBtn').data('requestId', requestId).show();
            
            $('#departmentRequestModal').modal('show');
        },
        error: function() {
            alert('Failed to load request details.');
        }
    });
}

function approveDepartmentRequest(requestId) {
    if (confirm('Are you sure you want to approve this request?')) {
        $.ajax({
            url: 'HODParameters.php',
            type: 'POST',
            data: {
                action: 'approveHODDepartmentRequest',
                requestId: requestId
            },
            success: function(response) {
                alert(response);
                $('#departmentRequestModal').modal('hide');
                loadDepartmentRequests();
            },
            error: function() {
                alert('Failed to approve request.');
            }
        });
    }
}

function declineDepartmentRequest(requestId) {
    const comments = prompt('Please provide a reason for declining:');
    if (comments) {
        $.ajax({
            url: 'HODParameters.php',
            type: 'POST',
            data: {
                action: 'declineHODDepartmentRequest',
                requestId: requestId,
                comments: comments
            },
            success: function(response) {
                alert(response);
                $('#departmentRequestModal').modal('hide');
                loadDepartmentRequests();
            },
            error: function() {
                alert('Failed to decline request.');
            }
        });
    }
}

// Add event listeners for the buttons
$(document).ready(function() {
    // Edit button click handler
    $('#editRequestBtn').click(function() {
        const requestId = $(this).attr('data-requestid');
        window.location.href = `edit_requesthod.php?jdrequestId=${requestId}`;
    });

    // Submit draft request button click handler
    $('#submitDraftRequestBtn').click(function() {
        const requestId = $(this).attr('data-requestid');
        if (confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
            $.ajax({
                url: 'HODParameters.php',
                type: 'POST',
                data: {
                    action: 'submitDraftRequest',
                    requestId: requestId
                },
                success: function(response) {
                    if (response.includes('success')) {
                        alert('Request submitted successfully');
                        $('#jobDetailsModal').modal('hide');
                        // Refresh the requests list
                        loadRequests();
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error submitting request');
                }
            });
        }
    });
});