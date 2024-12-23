window.approveDeptUnitLeadRequest = function(jdrequestid) {
    if (confirm('Are you sure you want to approve this request?')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'deptunitlead_approve',
                jdrequestid: jdrequestid
            },
            success: function(response) {
                if (response.includes('success')) {
                    alert('Request approved successfully');
                    $('#requestDetailsModal').modal('hide');
                    loadStaffRequests();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error approving request: ' + error);
            }
        });
    }
};

function approveDeptUnitLeadStation(jdrequestid, station) {
    if (confirm('Are you sure you want to approve this station request?')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'approve_deptunitlead_station',
                jdrequestid: jdrequestid,
                station: station
            },
            success: function(response) {
                if (response.includes('success')) {
                    alert('Station request approved successfully');
                    viewDeptUnitLeadRequest(jdrequestid); 
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error approving station request: ' + error);
            }
        });
    }
}

function showDeclineStationModal(jdrequestid, station) {
    $('#decline_jdrequestid').val(jdrequestid);
    $('#decline_station').val(station);
    $('#declineStationModal').modal('show');
}

function declineDeptUnitLeadStation() {
    const jdrequestid = $('#decline_jdrequestid').val();
    const station = $('#decline_station').val();
    const reason = $('#decline_reason').val().trim();

    console.log('Submitting decline:', { jdrequestid, station, reason }); // Debug log

    if (!reason) {
        alert('Please provide a reason for declining');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'decline_deptunitlead_station',
            jdrequestid,
            station,
            reason
        },
        success: function(response) {
            console.log('Response:', response);
            if (response.includes('success')) {
                alert('Request declined successfully');
                $('#declineModal').modal('hide');
                viewDeptUnitLeadRequest(jdrequestid);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error declining request: ' + error);
        }
    });
}

// Document ready handler
$(document).ready(function() {
    console.log('DeptUnitLead JS loaded');

    // Bind click handler for view buttons
    $(document).on('click', '.btn-view-request', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        viewDeptUnitLeadRequest(jdrequestid);
    });

    // Bind click handler for approve buttons using event delegation
    $(document).on('click', '.btn-approve-station', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        const station = $(this).data('station');
        approveDeptUnitLeadStation(jdrequestid, station);
    });

    // Single click handler for decline buttons
    $(document).on('click', '.btn-decline-station', function(e) {
        e.preventDefault();
        const jdrequestid = $(this).data('requestid');
        const station = $(this).data('station');
        const stationRow = $(this).closest('tr');
        
        // Remove any existing decline inputs first
        $('.decline-reason-input').remove();
        
        // Create and insert the decline input row after the station row
        const declineHtml = `
            <tr class="decline-reason-input">
                <td colspan="5">
                    <div class="input-group">
                        <input type="text" class="form-control" 
                               id="decline-reason-${station}" 
                               placeholder="Enter reason for declining">
                        <button class="btn btn-primary" 
                                onclick="submitDecline('${jdrequestid}', '${station}')">
                            Submit
                        </button>
                        <button class="btn btn-secondary" 
                                onclick="cancelDecline()">
                            Cancel
                        </button>
                    </div>
                </td>
            </tr>`;
        
        stationRow.after(declineHtml);
        $(`#decline-reason-${station}`).focus();
    });

    // Listen for modal opening
    $('#declineModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const requestId = button.data('requestid');
        const station = button.data('station');
        
        console.log('Modal opening with values:', { requestId, station });
        
        // Set values in hidden fields
        $(this).find('#decline_jdrequestid').val(requestId);
        $(this).find('#decline_station').val(station);
        $(this).find('#decline_reason').val('');
        
        // Debug check
        console.log('Values set in modal:', {
            jdrequestid: $('#decline_jdrequestid').val(),
            station: $('#decline_station').val()
        });
    });

    // Handle edit button click in the modal
    $(document).on('click', '#editRequestBtn', function() {
        const requestId = $(this).data('requestid');
        if (requestId) {
            editRequest(requestId);
        } else {
            console.error('No request ID found on edit button');
        }
    });

    // Add station button click handler
    $('#addStation').click(function() {
        addStationRequestDeptUnitLead();
    });

    // Remove station button handler
    $(document).on('click', '.remove-station', function() {
        if ($('.station-row').length > 1) {
            $(this).closest('.station-row').remove();
        } else {
            alert('At least one station is required.');
        }
    });
});

// Function to load staff requests
function loadStaffRequests() {
    console.log('Loading staff requests...'); // Debug log
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_requests'
        },
        success: function(response) {
            console.log('Requests loaded successfully'); // Debug log
            $('#staffRequestsTable').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading requests:', error);
            $('#staffRequestsTable').html('<div class="alert alert-danger">Error loading requests</div>');
        }
    });
}

// View request details function
window.viewDeptUnitLeadRequest = function(jdrequestid) {
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            $('#requestDetailsModal .modal-body').html(response);
            
            // Set the request ID on the edit button
            $('#editRequestBtn')
                .data('requestid', jdrequestid)
                .show(); // Show the edit button
            
            // Show/hide edit button based on request status
            const requestStatus = $('#requestStatus').val();
            if (requestStatus === 'draft') {
                $('#editRequestBtn').show();
                $('#submitDraftRequestBtn').show();
            } else {
                $('#editRequestBtn').hide();
                $('#submitDraftRequestBtn').hide();
            }
            
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading request details:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
};

// Approve station function
function approveDeptUnitLeadStation(jdrequestid, station) {
    console.log('Approving station:', jdrequestid, station);
    if (confirm('Are you sure you want to approve this station request?')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'approve_deptunitlead_station',
                jdrequestid: jdrequestid,
                station: station
            },
            success: function(response) {
                console.log('Approval response:', response);
                if (response.includes('success')) {
                    alert('Station request approved successfully');
                    // Refresh the request details
                    viewDeptUnitLeadRequest(jdrequestid);
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error approving station request: ' + error);
            }
        });
    }
}

// Update these functions
function showDeclineModal(jdrequestid, station) {
    console.log('Showing decline modal for:', jdrequestid, station); // Debug log
    
    // Store the values in hidden fields
    document.getElementById('decline_jdrequestid').value = jdrequestid;
    document.getElementById('decline_station').value = station;
    
    // Clear any previous reason
    document.getElementById('decline_reason').value = '';
    
    // Show the decline modal
    $('#declineModal').modal('show');
}
console.log('Values set in modal:', {
    jdrequestid: $('#decline_jdrequestid').val(),
    station: $('#decline_station').val()
});

function addStationRequestDeptUnitLead() {
    const requestId = $('#jdrequestid').val();
    const index = $('.station-row').length;

    console.log('Adding station:', { requestId, index }); // Debug log

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: { 
            action: 'get_station_options',
            index: index,
            requestId: requestId
        },
        success: function(response) {
            console.log('Received response:', response); // Debug log
            
            // Create a temporary div to hold the response
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.trim();
            
            // Append the new content to the container
            $('#stationRequests').append(tempDiv.firstChild);
            
            // Log the container contents after append
            console.log('Container after append:', $('#stationRequests').html());
        },
        error: function(xhr, status, error) {
            console.error('Error adding station:', error);
            alert('Error adding new station');
        }
    });
}

// Add validation for station selection
function validateStationSelection(selectElement) {
    const selectedValue = $(selectElement).val();
    let isDuplicate = false;

    $('.station-request select[name="station"]').each(function() {
        if (this !== selectElement && $(this).val() === selectedValue && selectedValue !== '') {
            isDuplicate = true;
            return false;
        }
    });

    if (isDuplicate) {
        alert('This station has already been selected. Please choose a different station.');
        $(selectElement).val('');
    }
}

function removeStationRequest(button) {
    $(button).closest('.station-request').remove();
}

function updateAvailableStations() {
    const selectedStations = [];
    $('.station-request select[name="station"]').each(function() {
        const value = $(this).val();
        if (value) selectedStations.push(value);
    });

    // Update all station selects
    $('.station-request select[name="station"]').each(function() {
        const currentValue = $(this).val();
        const originalOptions = $('#station').clone();
        
        // Remove selected stations except current selection
        originalOptions.find('option').each(function() {
            if (selectedStations.includes($(this).val()) && 
                $(this).val() !== currentValue && 
                $(this).val() !== '') {
                $(this).remove();
            }
        });

        // Keep current selection
        $(this).html(originalOptions.html());
        $(this).val(currentValue);
    });
}

// Modify collectFormData to gather all station requests
function collectFormData() {
    const stations = [];
    $('.station-request').each(function() {
        stations.push({
            station: $(this).find('select[name="station"]').val(),
            employmenttype: $(this).find('select[name="employmenttype"]').val(),
            staffperstation: $(this).find('input[name="staffperstation"]').val()
        });
    });

    return {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: $('#novacpost').val(),
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val(),
        stations: stations
    };
}


//save draft
function saveAsDraftDeptUnitLead() {
    if (!validateForm()) return false;

    // Calculate total vacant posts
    let totalVacantPosts = 0;
    $('.staffperstation').each(function() {
        totalVacantPosts += parseInt($(this).val()) || 0;
    });

    let formData = {
        'action': 'save_draft_deptunitlead',
        'jdrequestid': $('#jdrequestid').text().trim(),
        'jdtitle': $('#jdtitle').val(),
        'novacpost': totalVacantPosts,
        'deptunitcode': $('#deptunitcode').val(),
        'subdeptunitcode': $('#subdeptunitcode').val() || null,
        'departmentcode': $('#departmentcode').val(),
        'createdby': $('#createdby').val(),
        'status': 'draft'
    };

    // Add station data
    $('.station-request').each(function(index) {
        formData[`station_${index}`] = $(this).find('select[name="station"]').val();
        formData[`employmenttype_${index}`] = $(this).find('select[name="employmenttype"]').val();
        formData[`staffperstation_${index}`] = $(this).find('input[name="staffperstation"]').val();
    });

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                alert('Request saved as draft successfully');
                window.location.href = 'DeptUnitLead.php';
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error saving draft');
        }
    });
    return false;
}

function submitDeptUnitLead() {
    if (!validateForm()) return false;

    // Calculate total vacant posts
    calculateTotalVacantPosts();

    const formData = {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: $('#novacpost').val(),
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val(),
        departmentcode: $('#departmentcode').val(),
        createdby: $('#createdby').val(),
        stations: []
    };

    // Collect all station requests
    $('.station-request').each(function() {
        formData.stations.push({
            station: $(this).find('select[name="station"]').val(),
            employmenttype: $(this).find('select[name="employmenttype"]').val(),
            staffperstation: $(this).find('input[name="staffperstation"]').val()
        });
    });

    if (confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'submit_deptunitlead_request',
                formData: formData
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Request submitted successfully');
                    window.location.href = 'DeptUnitLead.php';
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
    return false;
}

function calculateTotalVacantPosts() {
    let total = 0;
    $('.staffperstation').each(function() {
        const value = parseInt($(this).val()) || 0;
        total += value;
    });
    $('#novacpost').val(total);
 
 // Add event listener to update total when staff per station changes
 (document).on('input', '.staffperstation', function() {
    calculateTotalVacantPosts();
 });
}
 // Modify submitDeptUnitLead to include the calculation
 function submitDeptUnitLead() {
    // Calculate total vacant posts before validation
    calculateTotalVacantPosts();
    
    if (!validateForm()) return false;
     const formData = {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: $('#novacpost').val(), // This will now have the calculated total
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val(),
        departmentcode: $('#departmentcode').val(),
        createdby: $('#createdby').val(),
        stations: []
    };
    }
 
    function validateForm() {
        //Calculate total vacant posts
       let totalVacantPosts = 0;
       $('.staffperstation').each(function() {
           totalVacantPosts += parseInt($(this).val()) || 0;
       });
       $('#novacpost').val(totalVacantPosts);
        // Validate JD Title
       if (!$('#jdtitle').val()) {
           alert('Please enter JD Title');
           return false;
       }
        // Validate stations
       let isValid = true;
       if ($('.station-request').length === 0) {
           alert('Please add at least one station');
           return false;
       }
        $('.station-request').each(function() {
           const station = $(this).find('select[name="station"]').val();
           const empType = $(this).find('select[name="employmenttype"]').val();
           const staffCount = $(this).find('input[name="staffperstation"]').val();
            if (!station || !empType || !staffCount) {
               alert('Please fill all station details');
               isValid = false;
               return false;
           }
       });
        return isValid;
    }


    //Tab Switching
    document.addEventListener('DOMContentLoaded', function() {
        // Get all tab buttons
        const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                    document.querySelector(t.dataset.bsTarget).classList.remove('show', 'active');
                });

                // Add active class to clicked tab
                this.classList.add('active');
                document.querySelector(this.dataset.bsTarget).classList.add('show', 'active');
            });
        });
    });
    function viewRequestDetails(requestId) {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'get_request_details',
                requestId: requestId
            },
            success: function(response) {
                $('#requestDetailsContent').html(response);
                $('#requestDetailsModal').modal('show');
                
                // Get the status and show/hide buttons accordingly
                const status = $('#requestStatus').val();
                if (status === 'draft') {
                    // Show both edit and submit buttons for draft requests
                    $('#editRequestBtn').show();
                    $('#editRequestBtn').attr('onclick', `editRequest('${requestId}')`);
                    $('#submitDraftRequestBtn').show();
                    $('#submitDraftRequestBtn').attr('onclick', `submitDraftRequest('${requestId}')`);
                } else {
                    // Hide both buttons for non-draft requests
                    $('#editRequestBtn').hide();
                    $('#submitDraftRequestBtn').hide();
                }
            }
        });
    }
    
    function editRequest(requestId) {
        if (!requestId) {
            console.error('Invalid request ID');
            return;
        }
        const encodedRequestId = encodeURIComponent(requestId);
        window.location.href = `edit_request.php?jdrequestid=${encodedRequestId}`;
    }
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true
            });
        });
    });
    

    $(document).ready(function() {
        // Load initial station rows
        loadStationRows();
    
        // Add new station
    $('#addStation').click(function() {
        const index = $('.station-row').length;
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'get_station_options',
                index: index
            },
            success: function(response) {
                $('#stationRequests').append(response);
            }
        });
    });

    // Remove station
    $(document).on('click', '.remove-station', function() {
        if ($('.station-row').length > 1) {
            $(this).closest('.station-row').remove();
        } else {
            alert('At least one station is required.');
        }
    });

    // Add station button click handler for edit page
    $('#addStation').click(function() {
        addStationRequestDeptUnitLead();
    });


    
    // Form submission
    $('#editRequestForm').submit(function(e) {
        e.preventDefault();
        
        // Validate total staff count matches novacpost
        let totalStaff = 0;
        $('.staffperstation').each(function() {
            totalStaff += parseInt($(this).val() || 0);
        });
        
        const novacpost = parseInt($('input[name="novacpost"]').val());
        
        if (totalStaff !== novacpost) {
            alert('Total staff per station must equal the number of vacant positions');
            return;
        }

        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: $(this).serialize() + '&action=update_request',
            success: function(response) {
                if (response.includes('success')) {
                    alert('Request updated successfully');
                    window.location.href = 'DeptUnitLead.php';
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function() {
                alert('Error updating request');
            }
        });
    });
});
function addStationRequestDeptUnitLead() {
    const requestId = $('input[name="jdrequestid"]').val();
    const index = $('.station-row').length;
    
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'get_station_options',
            index: index,
            requestId: requestId
        },
        success: function(response) {
            $('#stationContainer').append(response);
        }
    });
}

function loadStationRows() {
    const requestId = $('input[name="jdrequestid"]').val();
    console.log('Request ID:', requestId); // Debug line

    if (!requestId) {
        console.error('No request ID found in form');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'get_edit_station_rows',
            jdrequestid: requestId
        },
        success: function(response) {
            $('#stationContainer').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
        }
    });
}
    
    function addNewStation() {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'get_station_options',
                index: $('.station-row').length
            },
            success: function(response) {
                $('#stationContainer').append(response);
            },
            error: function() {
                alert('Error adding new station');
            }
        });
    }

    function submitDraftRequest(requestId) {
        if (!confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
            return false;
        }
    
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'submit_draft_request',
                jdrequestid: requestId
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Request submitted successfully');
                    $('#requestDetailsModal').modal('hide');
                    loadStaffRequests(); // Refresh the requests table
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


    // Add this function to handle edit button clicks
$(document).ready(function() {
    // Handle edit button click in the modal
    $('#editRequestBtn').click(function() {
        const requestId = $(this).data('requestid');
        window.location.href = `edit_request.php?id=${requestId}`;
    });
});

// Modify the viewDeptUnitLeadRequest function to set the request ID on the edit button
window.viewDeptUnitLeadRequest = function(jdrequestid) {
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            $('#requestDetailsModal .modal-body').html(response);
            
            // Set the request ID on the edit button
            $('#editRequestBtn')
                .data('requestid', jdrequestid)
                .show(); // Show the edit button
                
            // Show/hide edit button based on request status
            const requestStatus = $('#requestStatus').val(); // You'll need to add this hidden input in your modal
            if (requestStatus === 'draft') {
                $('#editRequestBtn').show();
                $('#submitDraftRequestBtn').show();
            } else {
                $('#editRequestBtn').hide();
                $('#submitDraftRequestBtn').hide();
            }
            
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading request details:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
};


// Update the viewDeptUnitLeadRequest function
window.viewDeptUnitLeadRequest = function(jdrequestid) {
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            $('#requestDetailsModal .modal-body').html(response);
            
            // Set the onclick attribute directly with the request ID
            $('#editRequestBtn')
                .attr('onclick', `editRequest('${jdrequestid}')`)
                .show(); // Show the edit button
            
            // Show/hide edit button based on request status
            const requestStatus = $('#requestStatus').val();
            if (requestStatus === 'draft') {
                $('#editRequestBtn').show();
                $('#submitDraftRequestBtn').show();
            } else {
                $('#editRequestBtn').hide();
                $('#submitDraftRequestBtn').hide();
            }
            
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading request details:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
};

// Make sure this is defined in the global scope
window.declineStation = function(jdrequestid, station) {
    // Remove any existing decline inputs first
    $('.decline-reason-input').remove();
    
    // Get the station row
    const stationRow = $(`tr[data-station="${station}"]`);
    
    // Create and insert the decline input row after the station row
    const declineHtml = `
        <tr class="decline-reason-input">
            <td colspan="5">
                <div class="input-group">
                    <input type="text" class="form-control" 
                           id="decline-reason-${station}" 
                           placeholder="Enter reason for declining">
                    <button class="btn btn-primary" 
                            onclick="submitDecline('${jdrequestid}', '${station}')">
                        Submit
                    </button>
                    <button class="btn btn-secondary" 
                            onclick="cancelDecline()">
                        Cancel
                    </button>
                </div>
            </td>
        </tr>`;
    
    stationRow.after(declineHtml);
    $(`#decline-reason-${station}`).focus();
};

// Make these functions globally accessible as well
window.submitDecline = function(jdrequestid, station) {
    const reason = $(`#decline-reason-${station}`).val().trim();
    
    if (!reason) {
        alert("A reason is required to decline a station.");
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'update_station_status',
            jdrequestid: jdrequestid,
            station: station,
            status: 'DeptUnit Lead Declined',
            reason: reason
        },
        success: function(response) {
            if (response === 'success') {
                alert('Station declined successfully');
                // Refresh the view to show updated status
                viewDeptUnitLeadRequest(jdrequestid);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error updating station status');
        }
    });
};

window.cancelDecline = function() {
    $('.decline-reason-input').remove();
};

// Add some basic styling
$('<style>')
    .text(`
        .decline-reason-input {
            background-color: #f8f9fa;
        }
        .decline-reason-input td {
            padding: 10px;
        }
        .decline-reason-input .input-group {
            margin-bottom: 0;
        }
    `)
    .appendTo('head');