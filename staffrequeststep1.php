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

$requestId = $revenue->generateRequestId();
$availablepositions = $revenue->getAvailablePositions($deptunitcode);
$staffRequests = $revenue->getRequestsByDepartment($deptunitcode);


?>
<main id="main" class="main">

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <a href="staffrequeststep2.php" class="btn btn-primary"
                    style="background-color: #fc7f14; border: #fc7f14; float: right;"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    + Create New Request
                </a>
            </div>
        </div>

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
    </section>

</main><!-- End #main -->

<script src="assets/js/ac.js"></script>
<script>
    if (document.getElementById('staffRequestTableBody')) {
        document.addEventListener('DOMContentLoaded', loadStaffRequests);
    }
</script>
<?php
include("addon/footer.html");
?>