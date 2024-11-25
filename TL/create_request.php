<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$staffid = $_SESSION['staffid'];
$teamLeadInfo = $revenue->getTeamLeadInfo($staffid);
$availablePositions = $revenue->getSubunitAvailablePositions($teamLeadInfo['subdeptunitcode']);
$jdrequestid = $revenue->generateRequestId();
$subdeptunitcode = $teamLeadInfo['subdeptunitcode'];

// Redirect if not authorized
if (!$teamLeadInfo['subdeptunitcode'] && !$_SESSION['isAdmin']) {
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
                                <h6 class="card-title" style="font-weight: 800; font-size: small;">STAFF REQUEST DETAILS</h6>
                            </div>
                            <div class="col-sm-6 text-end">
                                <span id="jdrequestid" style="font-size: small; font-weight: 700;">
                                    <?php echo $jdrequestid; ?>
                                </span>
                                <span id="availablevacant" style="font-size: small; font-weight: 700;">
                                    Available Positions for <?php echo $subdeptunitcode; ?>: <?php echo $availablePositions; ?>
                                </span>
                            </div>
                        </div>

                        <form id="staffRequestForm" method="POST">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Job Title</label>
                                    <select class="form-control" id="jdtitle" name="jdtitle" required>
                                        <option value="">Select Job Title</option>
                                        <?php echo $revenue->getSubunitJobTitles($teamLeadInfo['subdeptunitcode']); ?>
                                    </select>
                                </div>
                            </div>

                            <div id="stationContainer">
                                <div class="station-entry">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <label class="form-label">Station</label>
                                            <select class="form-control station-select" name="stations[]" required>
                                                <option value="">Select Station</option>
                                                <?php echo $revenue->getStations(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Employment Type</label>
                                            <select class="form-control" name="employmentTypes[]" required>
                                                <option value="">Select Type</option>
                                                <?php echo $revenue->getStaffTypes(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="form-label">Staff Per Station</label>
                                            <input type="number" class="form-control staff-per-station" name="staffPerStation[]" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addStation" class="btn btn-secondary mb-3">+ Add Another Station</button>
                        </form>

                        <div class="col-lg-12" id="loadstaffreqperstation">
                        </div>
                        <div class="row">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary"
                                    onclick="return submitTeamLeadRequest()"
                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                    onmouseover="this.style.backgroundColor='#000000';"
                                    onmouseout="this.style.backgroundColor='#fc7f14';">Save as Draft
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../includes/footer.html"); ?>
<script src="../assets/js/teamlead.js"></script>