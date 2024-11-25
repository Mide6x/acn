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
                    MY DEPARTMENT UNIT STAFF REQUESTS (<?php echo htmlspecialchars($deptUnitLeadInfo['deptunitname']); ?>)
                </h6>
                <div id="staffRequestsTable">
                    <?php
                    if ($deptUnitLeadInfo['deptunitcode']) {
                        echo $deptunit->getDeptUnitLeadRequests($deptUnitLeadInfo['deptunitcode']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Decline Station Modal -->
<div class="modal fade" id="declineStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Station Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="decline_jdrequestid">
                <input type="hidden" id="decline_station">
                <div class="mb-3">
                    <label for="decline_reason" class="form-label">Reason for Declining</label>
                    <textarea class="form-control" id="decline_reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="declineDeptUnitLeadStation()">Submit</button>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.html"); ?>
<!-- Make sure jQuery is loaded before your script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="deptunitlead.js"></script>