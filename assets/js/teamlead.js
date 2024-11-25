document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('staffRequestForm');
    const addStationBtn = document.getElementById('addStation');
    const stationContainer = document.getElementById('stationContainer');
    const jdrequestid = document.getElementById('jdrequestid').textContent.trim();

    // Add new station entry
    addStationBtn.addEventListener('click', function() {
        const newStation = document.querySelector('.station-entry').cloneNode(true);
        newStation.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });
        stationContainer.appendChild(newStation);
    });

    // Save as Draft button click handler
    document.querySelector('button[onclick="return submitTeamLeadRequest()"]').onclick = function(e) {
        e.preventDefault();
        saveDraft();
    };

    function saveDraft() {
        const formData = new FormData(form);
        formData.append('action', 'save_draft_request');
        formData.append('jdrequestid', jdrequestid);

        // Collect all station data
        const stations = [];
        const employmentTypes = [];
        const staffPerStation = [];
        
        document.querySelectorAll('.station-entry').forEach(entry => {
            stations.push(entry.querySelector('[name="stations[]"]').value);
            employmentTypes.push(entry.querySelector('[name="employmentTypes[]"]').value);
            staffPerStation.push(entry.querySelector('[name="staffPerStation[]"]').value);
        });

        formData.append('stations', JSON.stringify(stations));
        formData.append('employmentTypes', JSON.stringify(employmentTypes));
        formData.append('staffPerStation', JSON.stringify(staffPerStation));

        $.ajax({
            url: '../parameter/parameter.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.includes("successfully")) {
                    alert('Request saved as draft successfully');
                    window.location.href = 'TeamLead.php';
                } else {
                    alert(response);
                }
            },
            error: function(xhr, status, error) {
                alert('Error saving draft: ' + error);
            }
        });
    }
});