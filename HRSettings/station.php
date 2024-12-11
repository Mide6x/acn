<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$stations = $revenue->getAllStations();

// Get current hour for greeting
$hour = date('H');
$greeting = '';
if ($hour < 12) {
    $greeting = 'Good Morning';
} else if ($hour < 17) {
    $greeting = 'Good Afternoon'; 
} else {
    $greeting = 'Good Evening';
}

// Get HR name from email
$hrName = explode('@', CURRENT_USER['email'])[0];
$hrName = ucwords(str_replace('.', ' ', $hrName));
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $greeting . ', ' . $hrName; ?></h5>
                        <h6 class="card-subtitle mb-3">Stations Management</h6>
                        
                        <!-- Add Station Button -->
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStationModal">
                            <i class="bi bi-plus-circle"></i> Add Station
                        </button>

                        <!-- Stations Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Station Code</th>
                                        <th>Station Name</th>
                                        <th>Station Type</th>
                                        <th>Operation Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stations as $station): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($station['stationcode']); ?></td>
                                        <td><?php echo htmlspecialchars($station['stationname']); ?></td>
                                        <td><?php echo htmlspecialchars($station['stationtype']); ?></td>
                                        <td><?php echo htmlspecialchars($station['operationtype']); ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                       data-stationcode="<?php echo htmlspecialchars($station['stationcode']); ?>"
                                                       <?php echo $station['status'] === 'Active' ? 'checked' : ''; ?>>
                                                <label class="form-check-label"><?php echo $station['status']; ?></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-station" 
                                                    data-stationcode="<?php echo htmlspecialchars($station['stationcode']); ?>"
                                                    data-stationname="<?php echo htmlspecialchars($station['stationname']); ?>"
                                                    data-stationtype="<?php echo htmlspecialchars($station['stationtype']); ?>"
                                                    data-operationtype="<?php echo htmlspecialchars($station['operationtype']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Add Station Modal -->
<div class="modal fade" id="addStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStationForm">
                    <div class="mb-3">
                        <label for="stationName" class="form-label">Station Name</label>
                        <input type="text" class="form-control" id="stationName" required>
                    </div>
                    <div class="mb-3">
                        <label for="stationType" class="form-label">Station Type</label>
                        <select class="form-control" id="stationType" required>
                            <option value="">Select Station Type</option>
                            <option value="Domestic">Domestic</option>
                            <option value="Regional">Regional</option>
                            <option value="International">International</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="operationType" class="form-label">Operation Type</label>
                        <select class="form-control" id="operationType" required>
                            <option value="">Select Operation Type</option>
                            <option value="Fixed">Fixed</option>
                            <option value="Rotary">Rotary</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStation">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle status toggle
    $('.status-toggle').change(function() {
        const stationCode = $(this).data('stationcode');
        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'toggle_station_status',
                stationcode: stationCode
            },
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error updating status: ' + response);
                }
            },
            error: function() {
                alert('Error updating status');
            }
        });
    });

    // Handle add station
    $('#saveStation').click(function() {
        const stationName = $('#stationName').val();
        const stationType = $('#stationType').val();
        const operationType = $('#operationType').val();

        if (!stationName || !stationType || !operationType) {
            alert('Please fill in all fields');
            return;
        }

        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'create_station',
                stationname: stationName,
                stationtype: stationType,
                operationtype: operationType
            },
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error creating station: ' + response);
                }
            },
            error: function() {
                alert('Error creating station');
            }
        });
    });
});
</script>

<?php include("../includes/footer.html"); ?>