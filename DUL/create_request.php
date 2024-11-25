<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$deptunit = new DeptUnit($con);
$staffid = $_SESSION['staffid'];
$deptUnitLeadInfo = $deptunit->getDeptUnitLeadInfo($staffid);
$availablePositions = $deptunit->getDeptUnitLeadAvailablePositions($deptUnitLeadInfo['deptunitcode']);
$jdrequestid = $deptunit->generateRequestId();
$deptunitcode = $deptUnitLeadInfo['deptunitcode'];

// Redirect if not authorized
if (!$deptUnitLeadInfo['deptunitcode'] && !$_SESSION['isAdmin']) {
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
                                    Available Positions for <?php echo $deptunitcode; ?>: <?php echo $availablePositions; ?>
                                </span>
                            </div>
                        </div>

                        <form id="staffRequestForm">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Job Title</label>
                                    <select class="form-control" id="jdtitle" name="jdtitle" style="border-radius: 8px" required>
                                        <option value="">Select Job Title</option>
                                        <?php echo $deptunit->getJobTitles(); ?>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <div class="col-sm-6">
                            <h6 class="card-title" style="font-weight: 800; font-size: small;">STAFF PER STATION DETAILS</h6>
                        </div>
                        <div id="stationRequests">
                            <div class="station-request">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <label class="form-label">Station</label>
                                        <select class="form-control" id="station" name="station" style="border-radius: 8px" required>
                                            <option value="">Select Station</option>
                                            <?php echo $deptunit->getStations(); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Employment Type</label>
                                        <select class="form-control" id="employmenttype" name="employmenttype" style="border-radius: 8px" required>
                                            <option value="">Select Type</option>
                                            <?php echo $deptunit->getStaffTypes(); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Staff Per Station</label>
                                        <input type="number" class="form-control staffperstation" id="staffperstation" name="staffperstation"
                                            style="border-radius: 8px" required min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <button type="button" class="btn btn-secondary" onclick="addStationRequest()" style="display: block; margin: 0 auto;">
                                + Add
                            </button>
                        </div>

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
<script src="../assets/js/ac.js"></script>