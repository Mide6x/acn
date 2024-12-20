<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

// Include header, sidebar, and footer
include("../includes/header.html");
include("../includes/sidebar.html");
include("../includes/footer.html");

?>
<style>
    <?php include("css/approval-timeline.css"); ?>
</style>
<main id="main" class="main">
    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <a href="create_requesthod.php" class="btn btn-primary"
                    onclick="window.location.href='create_requesthod.php'; return false;"
                    style="background-color: #fc7f14; border: #fc7f14; float: right;"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    + Create New Request
                </a>
            </div>
        </div>
        <ul class="nav nav-tabs" id="requestTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dept-requests-tab" data-bs-toggle="tab" data-bs-target="#dept-requests" type="button" role="tab" aria-controls="dept-requests" aria-selected="true">Department Requests</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hod-requests-tab" data-bs-toggle="tab" data-bs-target="#hod-requests" type="button" role="tab" aria-controls="hod-requests" aria-selected="false">My Requests</button>
            </li>
        </ul>
        <div class="tab-content" id="requestTabsContent">
            <div class="tab-pane fade show active" id="dept-requests" role="tabpanel" aria-labelledby="dept-requests-tab">


                <!--table to show allstaff request made by the user-->

                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title" style="font-weight: 800; font-size: small;">MY DEPARTMENT STAFF REQUESTS</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr style="background-color: #fc7f14; color: #fff;">
                                        <th>Request ID</th>
                                        <th>Job Title</th>
                                        <th>Total Positions</th>
                                        <th>Dept Unit</th>
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
            </div>
            <div class="tab-pane fade" id="hod-requests" role="tabpanel" aria-labelledby="hod-requests-tab">
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title" style="font-weight: 800; font-size: small;">MY OWN REQUESTS</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr style="background-color: #fc7f14; color: #fff;">
                                        <th>Request ID</th>
                                        <th>Job Title</th>
                                        <th>Total Positions</th>
                                        <th>Stations</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="hodRequestTableBody">
                                    <!-- Requests will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
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
            <div class="modal-body" id="modal-content">
                <!-- Modal content will be loaded dynamically -->
                <div id="jobDetails"></div>
                <h6>Stations</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Employment Type</th>
                            <th>Staff Per Station</th>
                        </tr>
                    </thead>
                    <tbody id="stationDetails">
                        <!-- Station details will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- Department Request Details Modal -->
<div class="modal fade" id="departmentRequestModal" tabindex="-1" aria-labelledby="departmentRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentRequestModalLabel">Department Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="departmentRequestDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="declineBtn" onclick="declineDepartmentRequest($(this).data('requestId'))">Decline</button>
                <button type="button" class="btn btn-success" id="approveBtn" onclick="approveDepartmentRequest($(this).data('requestId'))">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobDetailsModalLabel">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editRequestBtn" style="display: none;">
                    Edit Request
                </button>
                <button type="button" class="btn btn-success" id="submitDraftRequestBtn" style="display: none;">
                    Submit Request
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        loadStaffRequests(); // Load department requests
        loadHODRequests(); // Load HOD's own requests
    });

    function loadHODRequests() {
        $.ajax({
            url: 'HODParameters.php',
            type: 'POST',
            data: {
                action: 'getHODRequests'
            },
            success: function(response) {
                $('#hodRequestTableBody').html(response);
            },
            error: function() {
                alert('Failed to load HOD requests.');
            }
        });
    }

    // Inline script to ensure function is defined
    function updateRequestStatus(requestId, status) {
        let comments = '';
        if (status === 'declined') {
            comments = prompt('Please provide a reason for declining:');
            if (comments === null) return; // User cancelled
            if (comments.trim() === '') {
                alert('Please provide a reason for declining.');
                return;
            }
        }

        $.ajax({
            url: 'HODParameters.php',
            type: 'POST',
            data: {
                action: 'updateStationStatus',
                requestId: requestId,
                status: status,
                comments: comments
            },
            success: function(response) {
                alert(response);
                $('#requestDetailsModal').modal('hide');
                loadStaffRequests(); // Reload the main table
            },
            error: function() {
                alert('Failed to update status');
            }
        });
    }
</script>
<script src="hod.js"></script>
<?php
include("../includes/footer.html");
?>