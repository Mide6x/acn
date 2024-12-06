<?php
require_once('../include/config.php');
require_once('HRClass.php');

$hr = new HR($con);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'get_pending_requests':
                try {
                    $requests = $hr->getPendingRequests();
                    $output = '';

                    foreach ($requests as $request) {
                        if ($request['approved_positions_count'] > 0) {  // Only show requests with approved stations
                            $output .= "<tr>";
                            $output .= "<td>{$request['jdrequestid']}</td>";
                            $output .= "<td>{$request['deptname']}</td>";  // Show department name instead of code
                            $output .= "<td>{$request['jdtitle']}</td>";
                            $output .= "<td>{$request['approved_positions_count']}</td>";
                            $output .= "<td>
                                <button onclick=\"viewRequestDetails('{$request['jdrequestid']}')\" 
                                        class='btn btn-sm btn-info'>
                                    <i class='bi bi-eye'></i> View
                                </button>
                            </td>";
                            $output .= "</tr>";
                        }
                    }

                    echo $output ?: "<tr><td colspan='5' class='text-center'>No pending requests found</td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                }
                break;

            case 'approve_request':
                try {
                    $result = $hr->updateRequestStatus(
                        $_POST['requestId'],
                        'approved',
                        $_POST['comments'] ?? null
                    );
                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'decline_request':
                try {
                    $result = $hr->updateRequestStatus(
                        $_POST['requestId'],
                        'declined',
                        $_POST['comments']
                    );
                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'get_request_details':
                if (!isset($_POST['requestId'])) {
                    throw new Exception("Request ID is required");
                }

                $details = $hr->getRequestDetails($_POST['requestId']);

                // Generate HTML for the modal
                $html = '<div class="request-details">';

                // Basic Details
                $html .= '<div class="basic-details mb-4">
                    <h6 class="fw-bold">Basic Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Request ID:</strong> ' . htmlspecialchars($details['requestDetails']['requestId']) . '</p>
                            <p><strong>Job Title:</strong> ' . htmlspecialchars($details['requestDetails']['jobTitle']) . '</p>
                            <p><strong>Department:</strong> ' . htmlspecialchars($details['requestDetails']['department']) . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> ' . htmlspecialchars($details['requestDetails']['status']) . '</p>
                            <p><strong>Request Date:</strong> ' . htmlspecialchars($details['requestDetails']['requestDate']) . '</p>
                            <p><strong>Created By:</strong> ' . htmlspecialchars($details['requestDetails']['createdBy']) . '</p>
                        </div>
                    </div>
                </div>';

                // Station Details
                $html .= '<div class="station-details mb-4">
                    <h6 class="fw-bold">Station Requirements</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Employment Type</th>
                                <th>Number of Staff</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($details['stations'] as $station) {
                    $html .= '<tr>
                        <td>' . htmlspecialchars($station['station']) . '</td>
                        <td>' . htmlspecialchars($station['employmentType']) . '</td>
                        <td>' . htmlspecialchars($station['count']) . '</td>
                    </tr>';
                }

                $html .= '</tbody></table></div>';

                // Add hidden inputs for timeline status
                foreach ($details['approvals'] as $approval) {
                    $html .= '<input type="hidden" id="' . strtolower($approval['level']) . 'Status" value="' . htmlspecialchars($approval['status']) . '">';
                }

                // Add a hidden input field for the request status, with the value being the status of the request
                $html .= '<input type="hidden" id="requestStatus" value="' . htmlspecialchars($details['requestDetails']['status']) . '">';

                // Add a hidden input field for the department code, with the value being the first three characters of the department unit code
                if (isset($details['requestDetails']['deptunitcode'])) {
                    $html .= '<input type="hidden" id="createdByDept" value="' . substr($details['requestDetails']['deptunitcode'], 0, 3) . '">';
                } else {
                    $html .= '<input type="hidden" id="createdByDept" value="">';
                }

                $html .= '</div>';

                echo $html;
                break;

            case 'create_hr_request':
                try {
                    $requestData = [
                        'jdrequestid' => $_POST['jdrequestid'],
                        'jdtitle' => $_POST['jdtitle'],
                        'total_positions' => $_POST['total_positions'],
                        'createdby' => 'john.d@acn.aero', // HR email
                        'stations' => json_decode($_POST['stations'], true)
                    ];

                    $result = $hr->createHRRequest($requestData);
                    echo json_encode(['success' => $result]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                break;

            case 'get_station_options':
                try {
                    $index = $_POST['index'];
                    $stations = $hr->getStations();
                    $staffTypes = $hr->getStaffTypes();

                    $html = '<div class="station-request">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="form-label">Station</label>
                                    <select class="form-control" name="stations[' . $index . '][station]" 
                                            style="border-radius: 8px" required>
                                        <option value="">Select Station</option>
                                        ' . $stations . '
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Employment Type</label>
                                    <select class="form-control" name="stations[' . $index . '][employmenttype]" 
                                            style="border-radius: 8px" required>
                                        <option value="">Select Type</option>
                                        ' . $staffTypes . '
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Staff Per Station</label>
                                    <input type="number" class="form-control staffperstation" 
                                           name="stations[' . $index . '][staffperstation]"
                                           style="border-radius: 8px" required min="1">
                                </div>
                                <div class="col-sm-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-station">×</button>
                                </div>
                            </div>
                        </div>';

                    echo $html;
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage();
                }
                break;

            case 'save_draft_request':
                try {
                    $requestData = [
                        'jdrequestid' => $_POST['jdrequestid'],
                        'jdtitle' => $_POST['jdtitle'],
                        'total_positions' => $_POST['total_positions'],
                        'createdby' => $_SESSION['email'] ?? 'john.d@acn.aero',
                        'stations' => json_decode($_POST['stations'], true),
                        'status' => 'draft'
                    ];

                    $result = $hr->createHRRequest($requestData);
                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'get_hr_only_requests':
                try {
                    $requests = $hr->getPendingRequestsHRonly();
                    $output = '';

                    foreach ($requests as $request) {
                        $output .= "<tr>";
                        $output .= "<td>{$request['jdrequestid']}</td>";
                        $output .= "<td>{$request['deptname']}</td>";
                        $output .= "<td>{$request['jdtitle']}</td>";
                        $output .= "<td>{$request['status']}</td>";
                        $output .= "<td>{$request['request_date']}</td>";
                        $output .= "<td>
                            <button onclick=\"viewRequestDetails('{$request['jdrequestid']}')\" 
                                    class='btn btn-sm btn-info'>
                                <i class='bi bi-eye'></i> View
                            </button>
                        </td>";
                        $output .= "</tr>";
                    }

                    echo $output ?: "<tr><td colspan='7' class='text-center'>No HR requests found</td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td colspan='7' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                }
                break;

            case 'get_nonhr_request_details':
                if (!isset($_POST['requestId'])) {
                    throw new Exception("Request ID is required");
                }

                $details = $hr->getRequestDetails($_POST['requestId']);

                // Generate HTML for the modal
                $html = '<div class="request-details">';

                // Basic Details
                $html .= '<div class="basic-details mb-4">
                    <h6 class="fw-bold">Basic Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Request ID:</strong> ' . htmlspecialchars($details['requestDetails']['requestId']) . '</p>
                            <p><strong>Job Title:</strong> ' . htmlspecialchars($details['requestDetails']['jobTitle']) . '</p>
                            <p><strong>Department:</strong> ' . htmlspecialchars($details['requestDetails']['department']) . '</p>
                            <p><strong>Department Unit:</strong> ' . htmlspecialchars($details['requestDetails']['departmentUnit']) . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> ' . htmlspecialchars($details['requestDetails']['status']) . '</p>
                            <p><strong>Request Date:</strong> ' . htmlspecialchars($details['requestDetails']['requestDate']) . '</p>
                            <p><strong>Created By:</strong> ' . htmlspecialchars($details['requestDetails']['createdBy']) . '</p>
                        </div>
                    </div>
                </div>';

                // Station Details
                $html .= '<div class="station-details mb-4">
                    <h6 class="fw-bold">Station Requirements</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Employment Type</th>
                                <th>Number of Staff</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($details['stations'] as $station) {
                    $html .= '<tr>
                        <td>' . htmlspecialchars($station['station']) . '</td>
                        <td>' . htmlspecialchars($station['employmentType']) . '</td>
                        <td>' . htmlspecialchars($station['count']) . '</td>
                    </tr>';
                }

                $html .= '</tbody></table></div>';

                // Add hidden inputs for timeline status
                foreach ($details['approvals'] as $approval) {
                    $html .= '<input type="hidden" id="' . strtolower($approval['level']) . 'Status" 
                                     value="' . htmlspecialchars($approval['status']) . '">';
                }

                $html .= '</div>';

                echo $html;
                break;

            case 'approve_nonhr_request':
                if (!isset($_POST['requestId'])) {
                    throw new Exception("Request ID is required");
                }

                $result = $hr->updateRequestStatus(
                    $_POST['requestId'],
                    'approved'
                );
                echo $result ? 'success' : 'error';
                break;

            case 'decline_nonhr_request':
                if (!isset($_POST['requestId']) || !isset($_POST['comments'])) {
                    throw new Exception("Request ID and comments are required");
                }

                $result = $hr->updateRequestStatus(
                    $_POST['requestId'],
                    'declined',
                    $_POST['comments']
                );
                echo $result ? 'success' : 'error';
                break;
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}