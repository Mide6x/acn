<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$staffid = getCurrentUser('staffid');
$teamLeadInfo = $revenue->getTeamLeadInfo($staffid);
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
                    MY SUBUNIT STAFF REQUESTS (<?php echo htmlspecialchars($teamLeadInfo['subdeptunit']); ?>)
                </h6>
                <div id="staffRequestsTable">
                    <?php
                    if ($teamLeadInfo['subdeptunitcode']) {
                        echo $revenue->getSubunitRequests($teamLeadInfo['subdeptunitcode']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include("../includes/footer.html"); ?>