<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/DUL/deptunit.php';

include("../includes/header.html");
include("../includes/sidebar.html");

// Check if user is logged in and is a DeptUnitLead
if (!isset($_SESSION['staffid']) || $_SESSION['position'] !== 'DeptUnitLead') {
    header('Location: ../login.php');
    exit();
}

$deptunit = new DeptUnit($con);
$requestId = $_GET['id'] ?? '';

try {
    $requestData = $deptunit->getEditRequestData($requestId, $_SESSION['staffid']);
    $requestDetails = $requestData['details'];
    $stations = $requestData['stations'];
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: DeptUnitLead.php');
    exit();
}
?>

<main id="main" class="main">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Staff Request</h5>

                <form id="editRequestForm" method="POST">
                    <input type="hidden" name="jdrequestid" value="<?php echo htmlspecialchars($requestId); ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="jdtitle"
                                value="<?php echo htmlspecialchars($requestDetails['jdtitle']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number of Vacant Positions</label>
                            <input type="number" class="form-control" name="novacpost"
                                value="<?php echo htmlspecialchars($requestDetails['novacpost']); ?>" required>
                        </div>
                    </div>

                    <!-- Station Details -->
                    <div class="mb-3">
                        <h6>Station Details</h6>
                        <div id="stationContainer">
                            <?php foreach ($stations as $index => $station): ?>
                                <div class="row mb-2 station-row">
                                    <div class="col-sm-4">
                                        <label class="form-label">Station</label>
                                        <select class="form-control" name="stations[<?php echo $index; ?>][station]" style="border-radius: 8px" required>
                                            <option value="">Select Station</option>
                                            <?php echo $deptunit->getStationsWithSelected($station['station']); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Employment Type</label>
                                        <select class="form-control" name="stations[<?php echo $index; ?>][employmenttype]" style="border-radius: 8px" required>
                                            <option value="">Select Type</option>
                                            <?php echo $deptunit->getStaffTypesWithSelected($station['employmenttype']); ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="form-label">Staff Per Station</label>
                                        <input type="number" class="form-control staffperstation"
                                            name="stations[<?php echo $index; ?>][staffperstation]"
                                            value="<?php echo htmlspecialchars($station['staffperstation']); ?>"
                                            style="border-radius: 8px" required min="1">
                                    </div>
                                    <div class="col-sm-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm remove-station">×</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addStationRequestDeptUnitLead()">
                            <i class="bi bi-plus"></i> Add Station
                        </button>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='DeptUnitLead.php'">Cancel</button>
                        <button type="submit" class="btn btn-primary" onclick="saveEditRequestDeptUnitLead()">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.html"); ?>
<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="deptunitlead.js"></script>