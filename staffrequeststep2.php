<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

// Include header, sidebar, and footer
include("addon/header.html");
include("addon/sidebar.html");

$revenue = new Revenue($con);
// Get dropdown data
$deptunitcode = $_SESSION['deptunitcode'] ?? DEFAULT_DEPT_UNIT_CODE;

// Check if editing existing request
$jdrequestid = $_GET['jdrequestid'] ?? null;
$requestData = null;
$requestId = null;

if ($jdrequestid) {
    try {
        $requestData = $revenue->getRequestDetails($jdrequestid);
        $requestId = $jdrequestid; // Use existing ID for edit mode
    } catch (Exception $e) {
        echo "<script>alert('Error loading request: " . $e->getMessage() . "'); 
              window.location.href='staffrequeststep1.php';</script>";
        exit;
    }
} else {
    // Only generate new ID if we're creating a new request
    $requestId = $revenue->generateRequestId();
}

$availablepositions = $revenue->getAvailablePositions($deptunitcode);


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
                                <span id="jdrequestid" style="font-size: small; font-weight: 700;">Request Id: <?php echo $requestId; ?></span>
                                <span id="availablevacant" style="font-size: small; font-weight: 700;">Staff Request Available for <?php echo $deptunitcode; ?>: <?php echo $availablepositions; ?></span>
                            </div>
                        </div>

                        <form id="staffRequestForm">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Job Title</label>
                                    <select class="form-control" id="jdtitle" name="jdtitle" style="border-radius: 8px" required>
                                        <option value="">Select Job Title</option>
                                        <?php echo $revenue->getJobTitles(); ?>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <script>
                            <?php if ($requestData): ?>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Set job title
                                    document.getElementById('jdtitle').value = <?php echo json_encode($requestData['jdtitle']); ?>;

                                    // Initialize station requests
                                    stationRequests = <?php echo json_encode($requestData['stations'] ?? []); ?>;
                                    updateStationRequestsTable();
                                });
                            <?php endif; ?>
                        </script>

                        <div id="stationRequests">
                            <div class="station-request">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <label class="form-label">Station</label>
                                        <select class="form-control" id="station" name="station" style="border-radius: 8px" required>
                                            <option value="">Select Station</option>
                                            <?php echo $revenue->getStations(); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Employment Type</label>
                                        <select class="form-control" id="employmenttype" name="employmenttype" style="border-radius: 8px" required>
                                            <option value="">Select Type</option>
                                            <?php echo $revenue->getStaffTypes(); ?>
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
                                + Add Another Station
                            </button>

                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary"
                                    onclick="return createstaffreqperstation()"
                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                    onmouseover="this.style.backgroundColor='#000000';"
                                    onmouseout="this.style.backgroundColor='#fc7f14';">Save Request
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-12" id="loadstaffreqperstation">
                        </div>
                        <div class="row">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary"
                                    onclick="return submitstaffrequest()"
                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                    onmouseover="this.style.backgroundColor='#000000';"
                                    onmouseout="this.style.backgroundColor='#fc7f14';">Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </div>
    </section>

</main><!-- End #main -->
<?php
include("addon/footer.html");
?>
<script src="assets\js\ac.js"></script>