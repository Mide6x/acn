<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/DUL/deptunit.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$_SESSION['departmentcode'] = CURRENT_USER['departmentcode'];

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

if (empty($jdrequestid)) {
    error_log("Failed to generate request ID");
    // Handle error appropriately
}
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <input type="hidden" id="createdby" value="<?php echo $_SESSION['staffid']; ?>">
                            <input type="hidden" id="deptunitcode" value="<?php echo $deptUnitLeadInfo['deptunitcode']; ?>">
                            <input type="hidden" id="subdeptunitcode" value="<?php echo $deptUnitLeadInfo['subdeptunitcode']; ?>">
                            <input type="hidden" id="departmentcode" value="<?php echo $_SESSION['departmentcode']; ?>">
                            <input type="hidden" id="jdrequestid" name="jdrequestid" value="<?php echo htmlspecialchars($jdrequestid); ?>">
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
                                <div class="col-sm-6 form-group">
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
                                        <select class="form-control" name="station" style="border-radius: 8px" required>
                                            <option value="">Select Station</option>
                                            <?php echo $deptunit->getStations(); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Employment Type</label>
                                        <select class="form-control" name="employmenttype" style="border-radius: 8px" required>
                                            <option value="">Select Type</option>
                                            <?php echo $deptunit->getStaffTypes(); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Staff Per Station</label>
                                        <input type="number" class="form-control staffperstation" name="staffperstation"
                                            style="border-radius: 8px" required min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-secondary mt-2" id="addStation">
                                    <i class="bi bi-plus"></i> Add Station
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-12" id="loadstaffreqperstation">
                        </div>
                        <div class="row">
                            <div class="col-sm-10">
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-primary" onclick="saveAsDraftDeptUnitLead()">Save as Draft</button>
                                    <button type="button" class="btn btn-primary" onclick="submitDeptUnitLeadRequest()">Submit Request</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<?php include("../includes/footer.html"); ?>
<script src="deptunitlead.js"></script>
<script src="js/create-request.js"></script>