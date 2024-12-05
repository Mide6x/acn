<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'HRClass.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$hr = new HR($con);
$pendingRequests = $hr->getPendingRequests();
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title" style="font-weight: 800; font-size: small;">HR DASHBOARD</h6>
                        <ul class="nav nav-tabs" id="requestTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-pending-tab" data-bs-toggle="tab" data-bs-target="#all-pending" type="button" role="tab" aria-controls="all-pending" aria-selected="true">All Pending Staff Requests</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="hr-only-tab" data-bs-toggle="tab" data-bs-target="#hr-only" type="button" role="tab" aria-controls="hr-only" aria-selected="false">HR Only Requests</button>
                            </li>
                            <!-- Add other tabs if needed -->
                        </ul>
                        <div class="tab-content" id="requestTabsContent">
                            <div class="tab-pane fade show active" id="all-pending" role="tabpanel" aria-labelledby="all-pending-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Department</th>
                                                <th>Job Title</th>
                                                <th>Approved Positions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendingRequestsTable">
                                            <?php
                                            if (!empty($pendingRequests)) {
                                                foreach ($pendingRequests as $request) {
                                                    echo "<tr>";
                                                    echo "<td>{$request['jdrequestid']}</td>";
                                                    echo "<td>{$request['departmentname']}</td>";
                                                    echo "<td>{$request['jdtitle']}</td>";
                                                    echo "<td>{$request['approved_positions_count']}</td>";
                                                    echo "<td>
                                                        <button onclick='viewRequestDetails(\"{$request['jdrequestid']}\")' class='btn btn-sm btn-info'>
                                                            <i class='bi bi-eye'></i> View
                                                        </button>
                                                    </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>No pending requests found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="hr-only" role="tabpanel" aria-labelledby="hr-only-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Department</th>
                                                <th>Job Title</th>
                                                <th>Approved Positions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hrOnlyRequestsTable">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Add other tab panes if needed -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="hr.js"></script>
<?php include("../includes/footer.html"); ?>

<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">Staff Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="approveRequestBtn">
                    <i class="bi bi-check-circle"></i> Approve Request
                </button>
                <button type="button" class="btn btn-danger" id="declineRequestBtn">
                    <i class="bi bi-x-circle"></i> Decline Request
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Fallback function in case the main JS file doesn't load
    if (typeof viewRequestDetails !== 'function') {
        function viewRequestDetails(requestId) {
            console.error('Main JS file not loaded properly');
            alert('Error: Could not load request details. Please refresh the page and try again.');
        }
    }
</script>