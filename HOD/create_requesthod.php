<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/HOD/HODClass.php';

// Include header, sidebar, and footer
include("../includes/header.html");
include("../includes/sidebar.html");

$hod = new HOD($con);
$staffid = $_SESSION['staffid'];
$hodInfo = $hod->getHODInfo($staffid);
$availablePositions = $hod->getAvailablePositions($hodInfo['deptunitcode']);
$jdrequestid = $hod->generateRequestId();
$departmentcode = $hodInfo['deptcode'];

$jobTitles = $hod->getJobTitles();
echo "<!-- Debug: Job Titles -->";
echo "<!-- " . htmlspecialchars($jobTitles) . " -->";

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
                                    Available Positions for <?php echo $departmentcode; ?>: <?php echo $availablePositions; ?>
                                </span>
                            </div>
                        </div>

                        <form id="staffRequestForm">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Job Title</label>
                                    <select class="form-control" id="jdtitle" name="jdtitle" required>
                                        <option value="">Select Job Title</option>
                                        <?php echo $hod->getJobTitles(); ?>
                                    </select>
                                </div>
                            </div>
                            <div id="stationRequests">
                                <!-- Station requests will be dynamically added here -->
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12 text-center">
                                    <button type="button" class="btn btn-secondary" id="addStation">
                                        + Add Station
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="savedraftHODstaffrequest()">
                                Save as Draft
                            </button>
                        </form>

                        <div class="col-lg-12" id="loadstaffreqperstation">
                        </div>
                        <div class="row">
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary"
                                    onclick="savedraftHODstaffrequest()"
                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                    onmouseover="this.style.backgroundColor='#000000';"
                                    onmouseout="this.style.backgroundColor='#fc7f14';">Save as Draft
                                </button>
                                <button type="button" class="btn btn-primary"
                                    id="submitRequestBtn"
                                    onclick="submitRequest('<?php echo $jdrequestid; ?>')"
                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                    onmouseover="this.style.backgroundColor='#000000';"
                                    onmouseout="this.style.backgroundColor='#fc7f14';">Submit Request
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
<script src="hod.js"></script>
</body>

</html>