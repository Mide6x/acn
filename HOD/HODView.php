<?php
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
                <a href="create_requesthod.php" class="btn btn-primary"
                    onclick="window.location.href='create_requesthod.php'; return false;"
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
    </section>

</main><!-- End #main -->

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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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