<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$staffid = $_SESSION['staffid'];
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

// Get DeptUnitLead info (for display purposes)
$deptUnitLeadInfo = $revenue->getDeptUnitLeadInfo($staffid);

?>

<main id="main" class="main">
    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <a href="create_request.php" class="btn btn-primary"
                    onclick="window.location.href='create_request.php'; return false;"
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
                    MY DEPARTMENT UNIT STAFF REQUESTS (<?php echo htmlspecialchars($deptUnitLeadInfo['deptunitname']); ?>)
                </h6>
                <div id="staffRequestsTable">
                    <?php
                    if ($deptUnitLeadInfo['deptunitcode']) {
                        echo $revenue->getDeptUnitLeadRequests($deptUnitLeadInfo['deptunitcode']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>
<script src="../assets/js/deptunitlead.js"></script>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Decline Reason Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="declineForm" onsubmit="event.preventDefault(); declineDeptUnitLeadRequest();">
                    <input type="hidden" id="decline_jdrequestid">
                    <div class="mb-3">
                        <label for="decline_reason" class="form-label">Reason for Declining</label>
                        <textarea class="form-control" id="decline_reason" rows="3" required
                            placeholder="Please provide a reason for declining this request"></textarea>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.html"); ?>