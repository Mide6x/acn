<?php
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
            <div class="col-12">
                <a href="create_requesthr.php" class="btn btn-primary"
                    onclick="window.location.href='create_requesthr.php'; return false;"
                    style="background-color: #fc7f14; border: #fc7f14; float: right;"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    + Create New Request
                </a>
            </div>
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
                                    <table id="hr-only-requests" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Department</th>
                                                <th>Job Title</th>
                                                <th>Status</th>
                                                <th>Request Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded here -->
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

<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Staff Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Approval Timeline -->
                <div class="approval-timeline mb-4">
                    <div class="timeline-wrapper">
                        <div class="timeline-item">
                            <div class="timeline-dot completed" data-bs-toggle="tooltip" title="Request Created"></div>
                            <div class="timeline-label">Created</div>
                        </div>
                        <div class="timeline-line"></div>
                        <div class="timeline-item">
                            <div class="timeline-dot" id="hodDot" data-bs-toggle="tooltip" title="HOD Review"></div>
                            <div class="timeline-label">HOD</div>
                        </div>
                        <div class="timeline-line"></div>
                        <div class="timeline-item">
                            <div class="timeline-dot" id="hrDot" data-bs-toggle="tooltip" title="HR Review"></div>
                            <div class="timeline-label">HR</div>
                        </div>
                        <div class="timeline-line"></div>
                        <div class="timeline-item">
                            <div class="timeline-dot" id="hohrDot" data-bs-toggle="tooltip" title="Head of HR Review"></div>
                            <div class="timeline-label">Head of HR</div>
                        </div>
                    </div>
                </div>

                <!-- Request Details Content -->
                <div id="requestDetailsContent"></div>

                <!-- Comments Section for Decline -->
                <div id="declineCommentsSection" style="display: none;" class="mt-3">
                    <div class="form-group">
                        <label for="declineComments" class="form-label">Reason for Declining:</label>
                        <textarea class="form-control" id="declineComments" rows="3" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="actionButtons">
                    <button type="button" class="btn btn-success" id="approveRequestBtn">
                        <i class="bi bi-check-circle"></i> Approve Request
                    </button>
                    <button type="button" class="btn btn-danger" id="declineRequestBtn">
                        <i class="bi bi-x-circle"></i> Decline Request
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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