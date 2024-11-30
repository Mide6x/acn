// Move these functions outside document.ready
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

window.showDeclineModal = function(jdrequestid) {
    document.getElementById('decline_jdrequestid').value = jdrequestid;
    document.getElementById('decline_reason').value = '';
    new bootstrap.Modal(document.getElementById('declineModal')).show();
};

window.declineDeptUnitLeadRequest = function() {
    const jdrequestid = document.getElementById('decline_jdrequestid').value;
    const station = document.getElementById('decline_station').value;
    const reason = document.getElementById('decline_reason').value.trim();

    console.log('Declining request:', jdrequestid, station, reason); // Debug log

    if (!reason) {
        alert('Please provide a reason for declining');
        return;
    }

    $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'decline_deptunitlead_station',
            jdrequestid: jdrequestid,
            station: station,
            reason: reason
        },
        success: function(response) {
            console.log('Decline response:', response); // Debug log
            if (response.includes('success')) {
                alert('Request declined successfully');
                $('#declineModal').modal('hide');
                // Refresh the request details
                viewDeptUnitLeadRequest(jdrequestid);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error declining request:', error);
            alert('Error declining request: ' + error);
        }
    });
};

// View request details
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
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading request details:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
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
        
        console.log('Decline button clicked:', { jdrequestid, station });
        
        // Set the values in modal before showing it
        $('#decline_jdrequestid').val(jdrequestid);
        $('#decline_station').val(station);
        $('#decline_reason').val('');
        
        // Show the modal
        $('#declineModal').modal('show');
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
function viewDeptUnitLeadRequest(jdrequestid) {
    console.log('Viewing request:', jdrequestid);
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_deptunitlead_request_details',
            jdrequestid: jdrequestid
        },
        success: function(response) {
            $('#requestDetailsModal .modal-body').html(response);
            $('#requestDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $('#requestDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading request details</div>');
        }
    });
}

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
    $.ajax({
        url: 'deptunitparameter.php',
        type: 'GET',
        data: {
            action: 'get_new_station_request_html'
        },
        success: function(response) {
            if (!response.includes('error')) {
                $('#stationRequests').append(response);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error adding new station request');
        }
    });
}

function removeStationRequest(button) {
    $(button).closest('.station-request').remove();
}

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

function validateStationSelection(selectElement) {
    const selectedValue = $(selectElement).val();
    let isDuplicate = false;

    $('.station-request select[name="station"]').each(function() {
        if (this !== selectElement && $(this).val() === selectedValue) {
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
    updateAvailableStations();
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
     const formData = {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: totalVacantPosts,
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val() || null, // Optional
        createdby: $('#createdby').val(),
        status: 'draft',
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
     $.ajax({
        url: 'deptunitparameter.php',
        type: 'POST',
        data: {
            action: 'save_draft_deptunitlead',
            formData: formData
        },
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
     const formData = {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: $('#novacpost').val(),
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val(),
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
                
                // Show edit button only for draft requests
                const status = $('#requestStatus').val();
                if (status === 'draft') {
                    $('#editRequestBtn').show();
                    $('#editRequestBtn').attr('onclick', `editRequest('${requestId}')`);
                } else {
                    $('#editRequestBtn').hide();
                }
            }
        });
    }
    
    function editRequest(requestId) {
        window.location.href = `edit_request.php?id=${requestId}`;
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
                $('#stationContainer').append(response);
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
    
    function loadStationRows() {
        $.ajax({
            url: 'deptunitparameter.php',
            type: 'POST',
            data: {
                action: 'get_edit_station_rows',
                requestId: $('input[name="jdrequestid"]').val()
            },
            success: function(response) {
                $('#stationContainer').html(response);
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