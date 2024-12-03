<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'HODClass.php';

$hod = new HOD($con);

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'getPendingRequests':
            try {
                $deptCode = CURRENT_USER['departmentcode'];
                $requests = $hod->getHODPendingRequests($deptCode);

                if (empty($requests)) {
                    echo "<tr><td colspan='6' class='text-center'>No pending requests found</td></tr>";
                    return;
                }

                foreach ($requests as $request) {
                    echo "<tr>
                        <td>{$request['jdrequestid']}</td>
                        <td>{$request['jdtitle']}</td>
                        <td>{$request['novacpost']}</td>
                        <td>{$request['deptunitname']}</td>
                        <td><span class='badge " . getBadgeClass($request['approval_status']) . "'>{$request['approval_status']}</span></td>
                        <td>
                            <button class='btn btn-sm btn-info' onclick='viewDetails(\"{$request['jdrequestid']}\")'>
                                View Details
                            </button>
                        </td>
                    </tr>";
                }
            } catch (Exception $e) {
                echo "<tr><td colspan='6' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
            }
            break;

        case 'getRequestDetails':
            try {
                $jdrequestid = $_POST['jdrequestid'];
                $details = $hod->getRequestDetails($jdrequestid);

                if (!empty($details)) {
                    $request = $details[0];
                    echo "<p><strong>Request ID:</strong> {$request['jdrequestid']}</p>
                          <p><strong>Job Title:</strong> {$request['jdtitle']}</p>
                          <p><strong>Status:</strong> {$request['status']}</p>";
                    // Add more details as needed
                } else {
                    echo "<p>No details found for this request.</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error: {$e->getMessage()}</p>";
            }
            break;

        case 'updateStationStatus':
            try {
                $requestId = $_POST['requestId'];
                $status = $_POST['status'];
                $comments = $_POST['comments'] ?? '';

                // Check if user has HOD rights using CURRENT_USER
                if (!defined('CURRENT_USER') || CURRENT_USER['position'] !== 'HOD') {
                    throw new Exception('Unauthorized access. Only HOD can perform this action.');
                }

                $success = $hod->updateStationStatus($requestId, $status, $comments);

                if ($success) {
                    echo "Status updated successfully. " .
                        ($status === 'approved' ? "Request forwarded to HR for review." :
                            "Request has been declined.");
                } else {
                    echo "Failed to update status";
                }
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}";
            }
            break;

        case 'createHODRequest':
            try {
                parse_str($_POST['formData'], $formData);
                $hod->createHODRequest($formData);
                echo "Request saved as draft successfully.";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'get_station_options':
            try {
                $index = $_POST['index'];
                $stations = $hod->getStations();
                $staffTypes = $hod->getStaffTypes();

                $html = '<div class="station-request">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label">Station</label>
                            <select class="form-control" name="stations[' . $index . '][station]" style="border-radius: 8px" required>
                                <option value="">Select Station</option>
                                ' . $stations . '
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Employment Type</label>
                            <select class="form-control" name="stations[' . $index . '][employmenttype]" style="border-radius: 8px" required>
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

        case 'getMyRequests':
            try {
                $staffid = $_SESSION['staffid'];
                $requests = $hod->getMyRequests($staffid);
                echo json_encode($requests);
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;

        case 'getHODRequests':
            try {
                $staffid = CURRENT_USER['staffid'];
                $requests = $hod->getHODRequests($staffid);

                if (empty($requests)) {
                    echo "<tr><td colspan='6' class='text-center'>No requests found</td></tr>";
                    return;
                }

                foreach ($requests as $request) {
                    $stations = explode(',', $request['stations']);
                    $staff_counts = explode(',', $request['staff_counts']);
                    $employment_types = explode(',', $request['employment_types']);

                    $stationDetails = [];
                    for ($i = 0; $i < count($stations); $i++) {
                        $stationDetails[] = "{$stations[$i]} ({$staff_counts[$i]} {$employment_types[$i]})";
                    }

                    echo "<tr>
                            <td>{$request['jdrequestid']}</td>
                            <td>{$request['jdtitle']}</td>
                            <td>{$request['novacpost']}</td>
                            <td>" . implode(', ', $stationDetails) . "</td>
                            <td><span class='badge " . getBadgeClass($request['status']) . "'>{$request['status']}</span></td>
                            <td>
                                <button class='btn btn-sm btn-info' onclick='viewJobDetails(\"{$request['jdtitle']}\")'>
                                    View Details
                                </button>
                            </td>
                        </tr>";
                }
            } catch (Exception $e) {
                echo "<tr><td colspan='6' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
            }
            break;

        case 'getJobDetails':
            try {
                $jdtitle = $_POST['jdtitle'];
                $details = $hod->getJobDetails($jdtitle);

                if ($details) {
                    echo "<div class='job-details'>
                            <h5>Job Title: {$details['jdtitle']}</h5>
                            <p><strong>Description:</strong> {$details['jddescription']}</p>
                            <p><strong>Educational Qualification:</strong> {$details['eduqualification']}</p>
                            <p><strong>Professional Qualification:</strong> {$details['proqualification']}</p>
                            <p><strong>Work Relations:</strong> {$details['workrelation']}</p>
                            <p><strong>Position Level:</strong> {$details['jdposition']}</p>
                            <p><strong>Age Bracket:</strong> {$details['agebracket']}</p>
                            <p><strong>Person Specification:</strong> {$details['personspec']}</p>
                            <p><strong>Technical Requirements:</strong> {$details['fuctiontech']}</p>
                            <p><strong>Managerial Requirements:</strong> {$details['managerial']}</p>
                            <p><strong>Behavioral Requirements:</strong> {$details['behavioural']}</p>
                          </div>";
                } else {
                    echo "<p>No job details found.</p>";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;
    }
}

function getBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'approved':
            return 'bg-success';
        case 'declined':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
