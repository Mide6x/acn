function initializeRequestDetails(jdrequestid) {
    fetch(`parameter/parameter.php?action=get_request_details&jdrequestid=${jdrequestid}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error loading request: ' + data.error);
                window.location.href = 'staffrequeststep1.php';
            } else {
                document.getElementById('jdrequestid').textContent = `Request ID: ${data.jdrequestid}`;
                document.getElementById('availablevacant').textContent = `Staff Request Available for ${data.deptunitcode}: ${data.availablepositions}`;
                document.getElementById('jdtitle').value = data.jdtitle;
                stationRequests = data.stations || [];
                updateStationRequestsTable();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading request details');
            window.location.href = 'staffrequeststep1.php';
        });

}

function initializeStaffRequest() {
    // Generate request ID on page load
    fetch('parameter/parameter.php?action=generate_id')
        .then(response => response.text())
        .then(data => {
            document.getElementById('jdrequestid').value = data;
        });
}

function saveDraft() {
    const formData = new FormData(document.getElementById('staffRequestForm'));
    formData.append('action', 'save_draft');

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            alert('Draft saved successfully');
        } else {
            alert('Error saving draft');
        }
    });
}

function addStation() {
    const formData = new FormData(document.getElementById('stationForm'));
    formData.append('action', 'add_station');

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            loadStationRequests();
        }
    });
}

let stationRequests = []; // Store all station requests

function addStationRequest() {
    const availableVacant = document.getElementById('availablevacant');
    const stationElement = document.getElementById('station');
    const employmentTypeElement = document.getElementById('employmenttype');
    const staffPerStationElement = document.getElementById('staffperstation');

    if (!availableVacant || !stationElement || !employmentTypeElement || !staffPerStationElement) {
        console.error('Required elements not found');
        return;
    }

    const availablePositions = parseInt(availableVacant.textContent.split(':')[1].trim());
    const currentTotal = calculateTotalStaffCount();
    
    const station = stationElement.value;
    const employmenttype = employmentTypeElement.value;
    const staffperstation = parseInt(staffPerStationElement.value) || 0;

    // Validation
    if (!station || !employmenttype || !staffperstation) {
        alert('Please fill all station request fields');
        return;
    }

    // Check if adding this station would exceed available positions
    if (currentTotal + staffperstation > availablePositions) {
        alert(`Adding ${staffperstation} positions would exceed the available positions (${availablePositions})`);
        return;
    }

    // Check if station already exists
    if (stationRequests.some(req => req.station === station)) {
        alert('This station has already been added');
        return;
    }

    stationRequests.push({ station, employmenttype, staffperstation });
    updateStationRequestsTable();

    // Reset form
    stationElement.value = '';
    employmentTypeElement.value = '';
    staffPerStationElement.value = '';
}

function updateStationRequestsTable() {
    const tableBody = document.getElementById('loadstaffreqperstation');
    let html = '<table class="table table-bordered mt-3">';
    html += '<thead><tr><th>Station</th><th>Employment Type</th><th>Staff Count</th></tr></thead><tbody>';
    
    stationRequests.forEach(request => {
        html += `<tr>
            <td>${request.station}</td>
            <td>${request.employmenttype}</td>
            <td>${request.staffperstation}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    tableBody.innerHTML = html;
}

function removeStationRequest(index) {
    const jdrequestid = document.getElementById('jdrequestid').textContent.split(': ')[1];
    const stationToRemove = stationRequests[index];

    const deleteData = new FormData();
    deleteData.append('action', 'delete_station');
    deleteData.append('jdrequestid', jdrequestid);
    deleteData.append('station', stationToRemove.station);

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: deleteData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            stationRequests.splice(index, 1);
            updateStationRequestsTable();
            
            // Update novacpost in main request
            const newTotal = calculateTotalStaffCount();
            const updateData = new FormData();
            updateData.append('action', 'update_novacpost');
            updateData.append('jdrequestid', jdrequestid);
            updateData.append('novacpost', newTotal);
            
            return fetch('parameter/parameter.php', {
                method: 'POST',
                body: updateData
            });
        }
        throw new Error('Failed to delete station request');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing station request: ' + error.message);
    });
}

function calculateTotalStaffCount() {
    return stationRequests.reduce((sum, req) => sum + parseInt(req.staffperstation), 0);
}

function createstaffreqperstation() {
    const jdrequestid = document.getElementById('jdrequestid').textContent.split(': ')[1];
    const jdtitle = document.getElementById('jdtitle').value;
    
    if (!jdtitle || stationRequests.length === 0) {
        alert('Please fill job title and add at least one station request');
        return false;
    }

    const currentTotal = calculateTotalStaffCount();
    const availablePositions = parseInt(document.getElementById('availablevacant').textContent.split(':')[1].trim());

    if (currentTotal > availablePositions) {
        alert(`Total staff count (${currentTotal}) cannot exceed available positions (${availablePositions})`);
        return false;
    }

    // Create initial staff request
    const mainRequestData = new FormData();
    mainRequestData.append('action', 'create_staff_request');
    mainRequestData.append('jdrequestid', jdrequestid);
    mainRequestData.append('jdtitle', jdtitle);
    mainRequestData.append('novacpost', currentTotal);

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: mainRequestData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            // After creating main request, add station requests
            return Promise.all(stationRequests.map(request => {
                const stationData = new FormData();
                stationData.append('action', 'add_station');
                stationData.append('jdrequestid', jdrequestid);
                stationData.append('station', request.station);
                stationData.append('employmenttype', request.employmenttype);
                stationData.append('staffperstation', request.staffperstation);

                return fetch('parameter/parameter.php', {
                    method: 'POST',
                    body: stationData
                }).then(response => response.text());
            }));
        }
        throw new Error('Failed to create staff request');
    })
    .then(() => {
        alert('Staff request saved successfully');
        window.location.href = 'staffrequeststep1.php';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving request: ' + error.message);
    });
}
// Add this function to load the station requests table
function loadstaffreqperstation() {
    const jdrequestid = document.getElementById('jdrequestid').textContent.split(': ')[1];
    const container = document.getElementById('loadstaffreqperstation');

    fetch(`parameter/parameter.php?action=get_stations&jdrequestid=${jdrequestid}`)
    .then(response => response.text())
    .then(data => {
        container.innerHTML = data;
    })
    .catch(error => console.error('Error:', error));
}

// Add this to initialize the table when page loads
document.addEventListener('DOMContentLoaded', loadStaffRequests);

function loadStaffRequests() {
    console.log('Loading staff requests...');
    const tableBody = document.getElementById('staffRequestTableBody');
    if (!tableBody) {
        console.error('Staff request table body not found');
        return;
    }
    
    fetch('parameter/parameter.php?action=get_requests')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            console.log('Received data:', data);
            tableBody.innerHTML = data || '<tr><td colspan="5" class="text-center">No requests found</td></tr>';
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
        });
}

function editRequest(jdrequestid) {
    // Redirect to staffrequeststep2.php with the request ID
    window.location.href = `staffrequeststep2.php?jdrequestid=${jdrequestid}`;
}

function toggleStationDetails(requestId) {
    const stationsRow = document.getElementById(`stations-${requestId}`);
    stationsRow.style.display = stationsRow.style.display === 'none' ? 'table-row' : 'none';
}
function submitstaffrequest() {
    const jdrequestid = document.getElementById('jdrequestid').textContent.split(': ')[1];
    const jdtitle = document.getElementById('jdtitle').value;
    
    if (!jdtitle || stationRequests.length === 0) {
        alert('Please fill job title and add at least one station request');
        return false;
    }

    const currentTotal = stationRequests.reduce((sum, req) => sum + parseInt(req.staffperstation), 0);

    // First create/update main staff request
    const mainRequestData = new FormData();
    mainRequestData.append('action', 'save_draft');
    mainRequestData.append('jdrequestid', jdrequestid);
    mainRequestData.append('jdtitle', jdtitle);
    mainRequestData.append('novacpost', currentTotal);

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: mainRequestData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            // Add all station requests
            return Promise.all(stationRequests.map(request => {
                const stationData = new FormData();
                stationData.append('action', 'add_station');
                stationData.append('jdrequestid', jdrequestid);
                stationData.append('station', request.station);
                stationData.append('employmenttype', request.employmenttype);
                stationData.append('staffperstation', request.staffperstation);

                return fetch('parameter/parameter.php', {
                    method: 'POST',
                    body: stationData
                }).then(response => response.text());
            }));
        }
        throw new Error('Failed to save main request');
    })
    .then(() => {
        alert('Staff request submitted successfully');
        stationRequests = [];
        updateStationRequestsTable();
        loadStaffRequests();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting request: ' + error.message);
    });

    return false;
}

function submitRequest(jdrequestid) {
    if (confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
        const formData = new FormData();
        formData.append('action', 'submit_request');
        formData.append('jdrequestid', jdrequestid);

        fetch('parameter/parameter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Request submitted successfully');
                loadStaffRequests();
            } else {
                alert('Error submitting request: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting request: ' + error.message);
        });
    }
}

function initializeNewRequestDetails() {
    fetch('parameter/parameter.php?action=get_new_request_details')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error loading new request details: ' + data.error);
                window.location.href = 'staffrequeststep1.php';
            } else {
                document.getElementById('jdrequestid').textContent = `Request ID: ${data.jdrequestid}`;
                document.getElementById('availablevacant').textContent = `Staff Request Available for ${data.deptunitcode}: ${data.availablepositions}`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading new request details');
            window.location.href = 'staffrequeststep1.php';
        });
}

