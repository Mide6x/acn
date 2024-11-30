function submitDeptUnitLeadRequest() {
    if (!validateForm()) return false;

    // Calculate total vacant posts
    let totalVacantPosts = 0;
    $('.staffperstation').each(function() {
        totalVacantPosts += parseInt($(this).val()) || 0;
    });

    if (!confirm('Are you sure you want to submit this request? Once submitted, it cannot be edited.')) {
        return false;
    }

    const formData = {
        jdrequestid: $('#jdrequestid').val(),
        jdtitle: $('#jdtitle').val(),
        novacpost: totalVacantPosts,
        deptunitcode: $('#deptunitcode').val(),
        subdeptunitcode: $('#subdeptunitcode').val() || null,
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
}

function validateForm() {
    // Validate JD Title
    if (!$('#jdtitle').val()) {
        alert('Please enter JD Title');
        return false;
    }

    // Validate stations
    if ($('.station-request').length === 0) {
        alert('Please add at least one station');
        return false;
    }

    let totalStaff = 0;
    let isValid = true;

    $('.station-request').each(function() {
        const station = $(this).find('select[name="station"]').val();
        const empType = $(this).find('select[name="employmenttype"]').val();
        const staffCount = parseInt($(this).find('input[name="staffperstation"]').val()) || 0;

        if (!station || !empType || staffCount <= 0) {
            alert('Please fill all station details correctly');
            isValid = false;
            return false;
        }

        totalStaff += staffCount;
    });

    // Update novacpost field with total
    $('#novacpost').val(totalStaff);

    return isValid;
} 