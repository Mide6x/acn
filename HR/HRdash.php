<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$pendingRequests = $revenue->getPendingRequests();
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title" style="font-weight: 800; font-size: small;">HR DASHBOARD</h6>
                        <!-- Content goes here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include("../includes/footer.html"); ?>