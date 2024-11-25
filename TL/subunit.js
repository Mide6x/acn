function submitSubunitRequest() {
    const form = document.getElementById('subunitRequestForm');
    const stations = [];
    
    // Collect all station entries
    document.querySelectorAll('.subunit-station-entry').forEach(entry => {
        stations.push({
            station: entry.querySelector('.subunit-station').value,
            employmenttype: entry.querySelector('.subunit-employment-type').value,
            staffperstation: entry.querySelector('.subunit-staff-count').value
        });
    });

    const formData = new FormData();
    formData.append('action', 'createSubunitRequest');
    formData.append('jdtitle', document.getElementById('subunit-job-title').value);
    formData.append('stations', JSON.stringify(stations));

    fetch('subunitparameters.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            alert('Request created successfully!');
            window.location.href = 'TeamLead.php';
        } else {
            alert(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the request');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Add station button functionality
    document.getElementById('addSubunitStation')?.addEventListener('click', function() {
        const container = document.getElementById('subunit-station-container');
        const template = container.querySelector('.subunit-station-entry').cloneNode(true);
        
        // Clear the values
        template.querySelectorAll('select, input').forEach(element => {
            element.value = '';
        });
        
        container.appendChild(template);
    });
});

function viewSubunitRequest(requestId) {
    // You can implement the view functionality here
    // For example, redirect to a view page:
    window.location.href = `view_request.php?id=${requestId}`;
}
