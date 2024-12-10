<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'CEOClass.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$ceo = new CEO($con);

// Get current hour
$hour = date('H');
$greeting = '';
if ($hour < 12) {
    $greeting = 'Good Morning';
} else if ($hour < 17) {
    $greeting = 'Good Afternoon'; 
} else {
    $greeting = 'Good Evening';
}

// Get CEO name from email
$ceoName = explode('@', CURRENT_USER['email'])[0];
$ceoName = ucwords(str_replace('.', ' ', $ceoName));
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title" style="font-weight: 800; font-size: small;">CEO DASHBOARD</h6>
                            <h6 class="text-muted"><?php echo $greeting . ', ' . $ceoName; ?></h6>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Title</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="requestsTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Request Details Modal -->
    <div class="modal fade" id="requestDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="requestDetailsContent">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="approveBtn">Approve</button>
                    <button type="button" class="btn btn-danger" id="declineBtn">Decline</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.html"); ?>
<script src="CEO.js"></script>
