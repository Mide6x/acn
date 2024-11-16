<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acn/include/config.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/acn/class/Revenue.php';

// Include header, sidebar, and footer
include("addon\header");
include("addon\sidebar");

$revenue = new Revenue($con);

// Get dropdown data
$jobTitles = $revenue->getJobTitles();
$stations = $revenue->getStations();
$staffTypes = $revenue->getStaffTypes();
$requestId = $revenue->generateRequestId();
$availablepositions = $revenue->getAvailablePositions($_SESSION['deptunitcode']);

?>
<main id="main" class="main">

    <section class="section">
        <div class="row">
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
                                        <span id="availablevacant" style="font-size: small; font-weight: 700;">Staff Request Available for <?php echo $_SESSION['deptunitcode']; ?>: <?php echo $availablepositions; ?></span>
                                    </div>
                                </div>

                                <form>
                                    <div class="row mb-3">
                                        <label for="jdtitle" class="col-sm-2 col-form-label">Job Title</label>
                                        <div class="col-sm-10">
                                            <select id="jdtitle" class="form-select" name="jdtitle"
                                                style="border-radius: 8px;">
                                                <option selected disabled value>Select</option>
                                                <?php echo $jobTitles; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <h6 style="margin-bottom: 20px; margin-top: 40px; font-size: 13px; font-weight: 700;">Staff Per Station</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="employmenttype" class="form-label"
                                                style="font-weight: bolder;  margin-bottom: 20px;">Employment Type</label>
                                            <select id="employmenttype" name="employmenttype" class="form-select"
                                                style="border-radius: 8px">
                                                <option selected disabled value>Select</option>
                                                <?php echo $staffTypes; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="station" class="form-label"
                                                style="font-weight: bolder; margin-bottom: 20px;">Station</label>
                                            <select id="station" name="station" class="form-select"
                                                style="border-radius: 8px">
                                                <option selected disabled value>Select</option>
                                                <?php echo $stations; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="staffperstation" class="form-label"
                                                style="font-weight: bolder; margin-bottom: 20px;">No. Of Staff
                                                Required</label>
                                            <input type="text" class="form-control" id="staffperstation"
                                                name="staffperstation" style="border-radius: 8px">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-10">
                                            <button type="button" class="btn btn-primary"
                                                onclick="return createstaffreqperstation()"
                                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                                onmouseover="this.style.backgroundColor='#000000';"
                                                onmouseout="this.style.backgroundColor='#fc7f14';">Add
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
                                </form>
                                <!--table to show allstaff request made by the user-->
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h6 class="card-title" style="font-weight: 800; font-size: small;">MY STAFF REQUESTS</h6>
                                        <div id="staffRequestsTable"></div>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Staff Requests</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Request ID</th>
                                                        <th>Job Title</th>
                                                        <th>Total Positions</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="staffRequestTableBody">
                                                </tbody>
                                            </table>
                                        </div>
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
include("addon\footer");
?>
<script src="assets\js\ac.js"></script>