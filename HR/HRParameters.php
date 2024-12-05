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
                try {
                    $requestId = $_POST['requestId'];
                    $details = $hr->getRequestDetails($requestId);
                    $stations = $hr->getApprovedStationRequests($requestId);

                    $output = "
                    <div class='container-fluid'>
                        <!-- Approval Timeline -->
                        

                        <!-- Request Details -->
                        <div class='row mb-4'>
                            <div class='col-md-6'>
                                <div class='card'>
                                    <div class='card-body'>
                                        <h6 class='card-title'>Request Information</h6>
                                        <p><strong>Request ID:</strong> {$details['jdrequestid']}</p>
                                        <p><strong>Department:</strong> {$details['departmentname']}</p>
                                        <p><strong>Job Title:</strong> {$details['jdtitle']}</p>
                                        <p><strong>Request Date:</strong> {$details['dandt']}</p>
                                        <p><strong>HOD Approval Date:</strong> {$details['hod_date']}</p>
                                        <p><strong>HOD Comments:</strong> {$details['hod_comments']}</p>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='card'>
                                    <div class='card-body'>
                                        <h6 class='card-title'>Job Details</h6>
                                        <p><strong>Description:</strong> {$details['jddescription']}</p>
                                        <p><strong>Educational Qualification:</strong> {$details['eduqualification']}</p>
                                        <p><strong>Professional Qualification:</strong> {$details['proqualification']}</p>
                                        <p><strong>Work Relation:</strong> {$details['workrelation']}</p>
                                        <p><strong>Position:</strong> {$details['jdposition']}</p>
                                        <p><strong>Condition:</strong> {$details['jdcondition']}</p>
                                        <p><strong>Age Bracket:</strong> {$details['agebracket']}</p>
                                        <p><strong>Person Specification:</strong> {$details['personspec']}</p>
                                        <p><strong>Functional/Technical:</strong> {$details['fuctiontech']}</p>
                                        <p><strong>Managerial:</strong> {$details['managerial']}</p>
                                        <p><strong>Behavioural:</strong> {$details['behavioural']}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Stations -->
                        <div class='row'>
                            <div class='col-12'>
                                <div class='card'>
                                    <div class='card-body'>
                                        <h6 class='card-title'>Stations</h6>
                                        <div class='table-responsive'>
                                            <table class='table table-bordered table-hover'>
                                                <thead>
                                                    <tr>
                                                        <th>Station</th>
                                                        <th>Employment Type</th>
                                                        <th>Staff Count</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>";

                    if (!empty($stations)) {
                        foreach ($stations as $station) {
                            $statusBadge = match ($station['status']) {
                                'approved' => '<span class="badge bg-success">Approved</span>',
                                'pending' => '<span class="badge bg-warning">Pending</span>',
                                'rejected' => '<span class="badge bg-danger">Rejected</span>',
                                default => '<span class="badge bg-secondary">Unknown</span>'
                            };

                            $output .= "<tr>
                                <td>{$station['station']}</td>
                                <td>{$station['employmenttype']}</td>
                                <td>{$station['staffperstation']}</td>
                                <td>{$statusBadge}</td>
                            </tr>";
                        }
                    } else {
                        $output .= "<tr><td colspan='4' class='text-center'>No stations found for this request</td></tr>";
                    }

                    $output .= "</tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>";

                    echo $output;
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error loading request details: " . $e->getMessage() . "</div>";
                }
                break;
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
