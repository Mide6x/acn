<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'subunit.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$subunit = new Subunit($con);
$staffid = $_SESSION['staffid'];
$teamLeadInfo = $subunit->getTeamLeadInfo($staffid);
$availablePositions = $subunit->getSubunitAvailablePositions($teamLeadInfo['subdeptunitcode']);
$jdrequestid = $subunit->generateRequestId();
$subdeptunitcode = $teamLeadInfo['subdeptunitcode'];

// Redirect if not authorized
if (!$subdeptunitcode && !$_SESSION['isAdmin']) {
    header("Location: ../index.php");
    exit;
}
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <h6 class="card-title" style="font-weight: 800; font-size: small;">SUBUNIT STAFF REQUEST</h6>
                            </div>
                            <div class="col-sm-6 text-end">
                                <span id="subunit-request-id" style="font-size: small; font-weight: 700;">
                                    <?php echo $jdrequestid; ?>
                                </span>
                                <span id="subunit-available-positions" style="font-size: small; font-weight: 700;">
                                    Available Positions for <?php echo $subdeptunitcode; ?>: <?php echo $availablePositions; ?>
                                </span>
                            </div>
                        </div>

                        <form id="subunitRequestForm" method="POST">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Job Title</label>
                                    <select class="form-control" id="subunit-job-title" name="jdtitle" required>
                                        <option value="">Select Job Title</option>
                                        <?php echo $subunit->getSubunitJobTitles($subdeptunitcode); ?>
                                    </select>
                                </div>
                            </div>

                            <div id="subunit-station-container">
                                <div class="subunit-station-entry">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <label class="form-label">Station</label>
                                            <select class="form-control subunit-station" name="station" required>
                                                <option value="">Select Station</option>
                                                <?php echo $subunit->getStations(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Employment Type</label>
                                            <select class="form-control subunit-employment-type" name="employmenttype" required>
                                                <option value="">Select Type</option>
                                                <?php echo $subunit->getStaffTypes(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="form-label">Staff Per Station</label>
                                            <input type="number" class="form-control subunit-staff-count" name="staffperstation" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addSubunitStation" class="btn btn-secondary mb-3">+ Add Another Station</button>
                            <button type="button" class="btn btn-primary" onclick="submitSubunitRequest()"
                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px; display: block; margin: 0 auto; margin-top: 20px"
                                onmouseover="this.style.backgroundColor='#000000';"
                                onmouseout="this.style.backgroundColor='#fc7f14';">Save as Draft
                            </button>
                        </form>

                        <div class="col-lg-12" id="subunit-request-list">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../includes/footer.html"); ?>
<script>
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request created successfully!');
                    window.location.href = 'TeamLead.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the request');
            });
    }

    document.getElementById('addSubunitStation').addEventListener('click', function() {
        const container = document.getElementById('subunit-station-container');
        const template = container.querySelector('.subunit-station-entry').cloneNode(true);

        // Clear the values
        template.querySelectorAll('select, input').forEach(element => {
            element.value = '';
        });

        container.appendChild(template);
    });
</script>