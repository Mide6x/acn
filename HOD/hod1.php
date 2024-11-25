<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

// Include header, sidebar, and footer
include("../includes/header.html");
include("../includes/sidebar.html");
include("../includes/footer.html");

?>
<main id="main" class="main">

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <a href="hod2.php" class="btn btn-primary"
                    onclick="window.location.href='hod2.php'; return false;"
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
                <h6 class="card-title" style="font-weight: 800; font-size: small;">MY SUBUNITS STAFF REQUESTS</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr style="background-color: #fc7f14; color: #fff;">
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
include("../includes/footer.html");
?>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="request-info mb-4">
                    <h6 class="fw-bold">Request Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Request ID:</strong> <span id="modal-requestid"></span></p>
                            <p><strong>Job Title:</strong> <span id="modal-jobtitle"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Positions:</strong> <span id="modal-positions"></span></p>
                            <p><strong>Status:</strong> <span id="modal-status"></span></p>
                        </div>
                    </div>
                </div>
                <div class="station-info">
                    <h6 class="fw-bold">Station Requests</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Employment Type</th>
                                    <th>Staff Count</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="modal-stations">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>