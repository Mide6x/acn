<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/TL/subunit.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$subunit = new Subunit($con);
$staffid = getCurrentUser('staffid');
$teamLeadInfo = $subunit->getTeamLeadInfo($staffid);
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
                    MY SUBUNIT STAFF REQUESTS (<?php echo htmlspecialchars($teamLeadInfo['subdeptunit']); ?>)
                </h6>
                <div id="staffRequestsTable">
                    <?php
                    if ($teamLeadInfo['subdeptunitcode']) {
                        echo $subunit->getSubunitRequests($teamLeadInfo['subdeptunitcode']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Bootstrap Modal -->
<div class="modal" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<?php include("../includes/footer.html"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="subunit.js"></script>