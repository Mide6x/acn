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
                $requestId = $_POST['requestId'];
                $details = $hod->getRequestDetails($requestId);

                if (!empty($details)) {
                    $request = $details[0];
                    echo "<div class='request-info mb-4'>
                            <h6 class='fw-bold'>Request Information</h6>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <p><strong>Request ID:</strong> {$request['jdrequestid']}</p>
                                    <p><strong>Job Title:</strong> {$request['jdtitle']}</p>
                                </div>
                                <div class='col-md-6'>
                                    <p><strong>Total Positions:</strong> {$request['novacpost']}</p>
                                    <p><strong>Status:</strong> {$request['status']}</p>
                                </div>
                            </div>
                          </div>
                          <div class='station-info'>
                            <h6 class='fw-bold'>Station Requests</h6>
                            <div class='table-responsive'>
                                <table class='table table-bordered'>
                                    <thead>
                                        <tr>
                                            <th>Station</th>
                                            <th>Employment Type</th>
                                            <th>Staff Count</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                    foreach ($details as $station) {
                        echo "<tr>
                                <td>{$station['station']}</td>
                                <td>{$station['employmenttype']}</td>
                                <td>{$station['staffperstation']}</td>
                                <td>{$station['status']}</td>
                              </tr>";
                    }

                    echo "</tbody></table></div></div>
                          <div class='job-details mt-4'>
                            <h6 class='fw-bold'>Job Details</h6>";

                    // Display job details from jobtitletbl
                    $jobFields = [
                        'Educational Qualification' => 'eduqualification',
                        'Professional Qualification' => 'proqualification',
                        'Work Relation' => 'workrelation',
                        'Job Condition' => 'jdcondition',
                        'Age Bracket' => 'agebracket',
                        'Person Specification' => 'personspec',
                        'Technical Skills' => 'fuctiontech',
                        'Managerial Skills' => 'managerial',
                        'Behavioral Skills' => 'behavioural'
                    ];

                    foreach ($jobFields as $label => $field) {
                        if (!empty($request[$field])) {
                            echo "<p><strong>{$label}:</strong> {$request[$field]}</p>";
                        }
                    }

                    // Add approve/decline buttons at the bottom
                    echo "</div>
                          <div class='mt-4 text-center'>
                            <button type='button' class='btn btn-success me-2' onclick='updateRequestStatus(\"{$request['jdrequestid']}\", \"approved\")'>
                                Approve Request
                            </button>
                            <button type='button' class='btn btn-danger' onclick='updateRequestStatus(\"{$request['jdrequestid']}\", \"declined\")'>
                                Decline Request
                            </button>
                          </div>";
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
