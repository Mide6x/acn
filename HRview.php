<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("addon/header.html");
include("addon/sidebar.html");

$revenue = new Revenue($con);
$pendingRequests = $revenue->getPendingRequests();
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Staff Requests</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Department</th>
                                        <th>Job Title</th>
                                        <th>Total Positions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingRequestsTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Decline Reason Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="declineForm">
                    <input type="hidden" id="decline_jdrequestid">
                    <input type="hidden" id="decline_station">
                    <div class="mb-3">
                        <label for="decline_reason" class="form-label">Reason for Declining</label>
                        <textarea class="form-control" id="decline_reason" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="submitDecline()">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="jobTitleDetails"></div>
                <hr>
                <h6>Station Requests</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Employment Type</th>
                                <th>Staff Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stationRequestsTable">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/hr.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', loadPendingRequests);
</script>
<?php include("addon/footer.html"); ?>