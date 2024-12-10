<?php
require_once('../include/config.php');
require_once('HHRClass.php');

$hhr = new HHR($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'get_pending_requests':
                echo $hhr->getPendingRequests();
                break;

            case 'get_request_details':
                try {
                    $requestId = $_POST['requestId'];

                    // Get request details
                    $requestDetails = $hhr->getRequestDetails($requestId);

                    // Get station details
                    $stations = $hhr->getStationDetails($requestId);

                    // Generate HTML output
                    $output = "
                    <input type='hidden' id='requestStatus' value='{$requestDetails['status']}'>
                    <div class='row mb-3'>
                        <div class='col-md-6'>
                            <h6>Request Details</h6>
                            <p><strong>Request ID:</strong> {$requestDetails['jdrequestid']}</p>
                            <p><strong>Job Title:</strong> {$requestDetails['jdtitle']}</p>
                            <p><strong>Total Positions:</strong> {$requestDetails['novacpost']}</p>
                            <p><strong>Status:</strong> {$requestDetails['status']}</p>
                            <p><strong>Requested By:</strong> {$requestDetails['requestor']}</p>
                            <p><strong>Request Date:</strong> " . date('Y-m-d', strtotime($requestDetails['dandt'])) . "</p>
                        </div>
                        <div class='col-md-6'>
                            <h6>Job Details</h6>
                            <p><strong>Description:</strong> {$requestDetails['jddescription']}</p>
                            <p><strong>Educational Qualification:</strong> {$requestDetails['eduqualification']}</p>
                            <p><strong>Professional Qualification:</strong> {$requestDetails['proqualification']}</p>
                            <p><strong>Work Relation:</strong> {$requestDetails['workrelation']}</p>
                            <p><strong>Job Condition:</strong> {$requestDetails['jdcondition']}</p>
                        </div>
                    </div>

                    <div class='row mb-3'>
                        <div class='col-md-12'>
                            <h6>Additional Requirements</h6>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <p><strong>Age Bracket:</strong> {$requestDetails['agebracket']}</p>
                                    <p><strong>Person Specification:</strong> {$requestDetails['personspec']}</p>
                                    <p><strong>Technical Skills:</strong> {$requestDetails['fuctiontech']}</p>
                                </div>
                                <div class='col-md-6'>
                                    <p><strong>Managerial Skills:</strong> {$requestDetails['managerial']}</p>
                                    <p><strong>Behavioral Skills:</strong> {$requestDetails['behavioural']}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-12'>
                            <h6>Station Details</h6>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Employment Type</th>
                                        <th>Staff Count</th>
                                    </tr>
                                </thead>
                                <tbody>";

                    foreach ($stations as $station) {
                        $output .= "<tr>
                            <td>{$station['station']}</td>
                            <td>{$station['employmenttype']}</td>
                            <td>{$station['staffperstation']}</td>
                        </tr>";
                    }

                    $output .= "</tbody></table></div></div>";

                    echo $output;
                } catch (Exception $e) {
                    echo '<tr><td colspan="5" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                }
                break;

            case 'approve_request':
                if (!isset($_POST['requestId'])) {
                    echo 'error: Request ID is required';
                    break;
                }
                $result = $hhr->updateRequestStatus($_POST['requestId'], 'approved');
                echo $result ? 'success' : 'error';
                break;

            case 'decline_request':
                if (!isset($_POST['requestId']) || !isset($_POST['comments'])) {
                    echo 'error: Request ID and comments are required';
                    break;
                }
                $result = $hhr->updateRequestStatus($_POST['requestId'], 'declined', $_POST['comments']);
                echo $result ? 'success' : 'error';
                break;
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="5" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
    }
}
