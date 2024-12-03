<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/DUL/deptunit.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$deptunit = new DeptUnit($con);
$staffid = $_SESSION['staffid'];
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

// Get DeptUnitLead info (for display purposes)
$deptUnitLeadInfo = $deptunit->getDeptUnitLeadInfo($staffid);

?>

<main id="main" class="main">
    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <a href="create_request.php" class="btn btn-primary"
                    style="background-color: #fc7f14; border: #fc7f14; float: right;"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    + Create New Request
                </a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title" style="font-weight: 800; font-size: small;">
                    STAFF REQUESTS - <?php echo htmlspecialchars($deptUnitLeadInfo['deptunitname']); ?>
                </h6>

                <!-- Tabs for switching between views -->
                <ul class="nav nav-tabs mb-3" id="requestTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab"
                            data-bs-target="#pending" type="button" role="tab">
                            Pending Approvals
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab"
                            data-bs-target="#my-requests" type="button" role="tab">
                            My Requests
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content" id="requestTabsContent">
                    <!-- Pending Approvals Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <?php
                        if ($deptUnitLeadInfo['deptunitcode']) {
                            echo $deptunit->getDeptUnitLeadRequests($deptUnitLeadInfo['deptunitcode']);
                        }
                        ?>
                    </div>

                    <!-- My Requests Tab -->
                    <div class="tab-pane fade" id="my-requests" role="tabpanel">
                        <?php
                        if (isset($_SESSION['staffid'])) {
                            echo $deptunit->getMyStaffRequests($_SESSION['staffid']);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editRequestBtn" style="display: none;">
                    Edit Request
                </button>
                <button type="button" class="btn btn-success" id="submitDraftRequestBtn" style="display: none;">
                    Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.html"); ?>
<!-- Make sure jQuery is loaded before your script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="deptunitlead.js"></script>
<link rel="stylesheet" href="css/approval-timeline.css">