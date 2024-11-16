// Staff Request Handling
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
    const totalStaff = parseInt(document.getElementById('totalStaff').value) || 0;
    const currentTotal = stationRequests.reduce((sum, req) => sum + parseInt(req.staffperstation), 0);
    
    if (currentTotal >= totalStaff) {
        alert('Cannot add more stations. Total staff count would exceed the requested amount.');
        return;
    }

    const station = document.getElementById('station').value;
    const employmenttype = document.getElementById('employmenttype').value;
    const staffperstation = document.getElementById('staffperstation').value;

    if (!station || !employmenttype || !staffperstation) {
        alert('Please fill all station request fields');
        return;
    }

    stationRequests.push({
        station,
        employmenttype,
        staffperstation: parseInt(staffperstation)
    });

    // Clear form fields
    document.getElementById('station').value = '';
    document.getElementById('employmenttype').value = '';
    document.getElementById('staffperstation').value = '';

    updateStationRequestsTable();
}

function updateStationRequestsTable() {
    const container = document.getElementById('loadstaffreqperstation');
    const totalStaff = parseInt(document.getElementById('totalStaff').value) || 0;
    const currentTotal = stationRequests.reduce((sum, req) => sum + req.staffperstation, 0);

    const html = `
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Employment Type</th>
                    <th>Staff Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${stationRequests.map((req, index) => `
                    <tr>
                        <td>${req.station}</td>
                        <td>${req.employmenttype}</td>
                        <td>${req.staffperstation}</td>
                        <td>
                            <button onclick="removeStationRequest(${index})" class="btn btn-sm btn-danger">Remove</button>
                        </td>
                    </tr>
                `).join('')}
                <tr>
                    <td colspan="2">Total Staff</td>
                    <td colspan="2">${currentTotal} / ${totalStaff}</td>
                </tr>
            </tbody>
        </table>
    `;
    
    container.innerHTML = html;
}

function removeStationRequest(index) {
    stationRequests.splice(index, 1);
    updateStationRequestsTable();
}

function createstaffreqperstation() {
    const jdrequestid = document.getElementById('jdrequestid').textContent.split(': ')[1];
    const jdtitle = document.getElementById('jdtitle').value;
    const totalStaff = parseInt(document.getElementById('totalStaff').value);
    
    if (!jdtitle || stationRequests.length === 0) {
        alert('Please fill job title and add at least one station request');
        return false;
    }

    const currentTotal = stationRequests.reduce((sum, req) => sum + req.staffperstation, 0);
    if (currentTotal !== totalStaff) {
        alert(`Total staff per station (${currentTotal}) must equal total staff requested (${totalStaff})`);
        return false;
    }

    // Save main request
    const mainRequestData = new FormData();
    mainRequestData.append('action', 'save_draft');
    mainRequestData.append('jdrequestid', jdrequestid);
    mainRequestData.append('jdtitle', jdtitle);
    mainRequestData.append('novacpost', totalStaff);

    fetch('parameter/parameter.php', {
        method: 'POST',
        body: mainRequestData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            // Save all station requests
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
        alert('Staff request saved successfully');
        stationRequests = [];
        updateStationRequestsTable();
        loadStaffRequests();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving request: ' + error.message);
    });

    return false;
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
    const tableBody = document.getElementById('staffRequestTableBody');
    const deptunitcode = document.getElementById('availablevacant').textContent.split(':')[0].trim().split(' ').pop();
    
    fetch(`parameter/parameter.php?action=get_requests&deptunitcode=${deptunitcode}`)
        .then(response => response.text())
        .then(data => {
            tableBody.innerHTML = data;
        });
}

function editRequest(jdrequestid) {
    fetch(`parameter/parameter.php?action=get_request_details&jdrequestid=${jdrequestid}`)
        .then(response => response.json())
        .then(data => {
            if (data.request) {
                // Populate form with existing data
                document.getElementById('jdrequestid').textContent = `Request ID: ${data.request.jdrequestid}`;
                document.getElementById('jdtitle').value = data.request.jdtitle;
                document.getElementById('totalStaff').value = data.request.novacpost;
                
                // Load station requests into global array
                stationRequests = data.request.stations.map(station => ({
                    station: station.station,
                    employmenttype: station.employmenttype,
                    staffperstation: parseInt(station.staffperstation)
                }));
                
                updateStationRequestsTable();
                
                // Scroll to form
                document.getElementById('staffRequestForm').scrollIntoView();
            }
        });
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

    const totalStaff = stationRequests.reduce((sum, req) => sum + req.staffperstation, 0);

    // First create/update main staff request
    const mainRequestData = new FormData();
    mainRequestData.append('action', 'save_draft');
    mainRequestData.append('jdrequestid', jdrequestid);
    mainRequestData.append('jdtitle', jdtitle);
    mainRequestData.append('novacpost', totalStaff);

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

