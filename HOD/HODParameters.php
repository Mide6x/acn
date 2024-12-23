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

            $jdrequestid = $_POST['requestId'];
            $details = $hod->getRequestDetails($jdrequestid);

            if (!empty($details)) {
                $request = $details[0];
                $output = "<p><strong>Request ID:</strong> {$request['jdrequestid']}</p>
                          <p><strong>Job Title:</strong> {$request['jdtitle']}</p>
                          <p><strong>Status:</strong> {$request['status']}</p>";

                echo $output;
            } else {
                echo "<p>No details found for this request.</p>";
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
                                <button class='btn btn-sm btn-info' onclick='viewJobDetails(\"{$request['jdtitle']}\", \"{$request['jdrequestid']}\")'>
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
                $requestId = $_POST['requestId'] ?? null;

                // Get job details
                $details = $hod->getJobDetails($jdtitle);

                // Get approval workflow if requestId is provided
                $approvals = $requestId ? $hod->getApprovalWorkflow($requestId) : [];

                // Get request status if requestId is provided
                $requestStatus = $requestId ? $hod->getRequestStatus($requestId) : 'unknown';

                if ($details) {
                    $output = "<input type='hidden' id='requestStatus' value='{$requestStatus}'>";
                    $output .= "<div class='row mb-3'>
                            <div class='col-md-6'>
                                <h6>Job Details</h6>
                                <p><strong>Job Title:</strong> {$details['jdtitle']}</p>
                                <p><strong>Description:</strong> {$details['jddescription']}</p>
                                <p><strong>Educational Qualification:</strong> {$details['eduqualification']}</p>
                                <p><strong>Professional Qualification:</strong> {$details['proqualification']}</p>
                                <p><strong>Work Relations:</strong> {$details['workrelation']}</p>
                                <p><strong>Position Level:</strong> {$details['jdposition']}</p>
                            </div>
                            <div class='col-md-6'>
                                <h6>Additional Requirements</h6>
                                <p><strong>Age Bracket:</strong> {$details['agebracket']}</p>
                                <p><strong>Person Specification:</strong> {$details['personspec']}</p>
                                <p><strong>Technical Requirements:</strong> {$details['fuctiontech']}</p>
                                <p><strong>Managerial Requirements:</strong> {$details['managerial']}</p>
                                <p><strong>Behavioral Requirements:</strong> {$details['behavioural']}</p>
                            </div>
                        </div>";

                    // Add Approval Workflow if approvals exist
                    if (!empty($approvals)) {
                        $output .= "<div class='row mb-3'>
                                <div class='col-12'>
                                    <h6>Approval Workflow</h6>
                                    <div class='approval-timeline'>
                                        <div class='timeline-wrapper'>";

                        $approvalStages = ['HR', 'HeadOfHR', 'CFO', 'CEO'];
                        foreach ($approvalStages as $index => $stage) {
                            $status = isset($approvals[$stage]) ? $approvals[$stage]['status'] : 'draft';
                            $date = isset($approvals[$stage]['date']) ? date('Y-m-d', strtotime($approvals[$stage]['date'])) : '-';
                            $comments = isset($approvals[$stage]['comments']) ? $approvals[$stage]['comments'] : '-';

                            // Determine the dot class based on status
                            $dotClass = match ($status) {
                                'approved' => 'timeline-dot completed',
                                'declined' => 'timeline-dot declined',
                                'pending' => 'timeline-dot current',
                                default => 'timeline-dot draft'
                            };

                            $output .= "<div class='timeline-item'>
                                <div class='{$dotClass}' data-bs-toggle='tooltip' 
                                     title='Status: " . ucfirst($status) . "&#013;Date: {$date}&#013;Comments: {$comments}'>
                                </div>
                                <div class='timeline-label'>{$stage}</div>
                            </div>";

                            // Add connecting line if not the last item
                            if ($index < count($approvalStages) - 1) {
                                $lineClass = $status === 'approved' ? 'timeline-line completed' : 'timeline-line';
                                $output .= "<div class='{$lineClass}'></div>";
                            }
                        }

                        $output .= "</div></div></div>";
                    }

                    // Load station details using the provided method
                    $stationDetails = $hod->getRequestDetails($requestId);
                    if (!empty($stationDetails)) {
                        $output .= "<div class='row'>
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

                        foreach ($stationDetails as $station) {
                            $output .= "<tr>
                                <td>{$station['station']}</td>
                                <td>{$station['employmenttype']}</td>
                                <td>{$station['staffperstation']}</td>
                            </tr>";
                        }
                        $output .= "</tbody></table></div></div>";
                    } else {
                        $output .= "<div class='row'><div class='col-12'><p class='text-center'>No station details found.</p></div></div>";
                    }

                    echo $output;
                } else {
                    echo "<p>No job details found.</p>";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'getStationDetails':
            try {
                $jdtitle = $_POST['jdtitle'];
                $query = "SELECT srs.station, srs.employmenttype, srs.staffperstation
                          FROM staffrequestperstation srs
                          JOIN staffrequest sr ON srs.jdrequestid = sr.jdrequestid
                          WHERE sr.jdtitle = :jdtitle";
                $stmt = $con->prepare($query);
                $stmt->execute(['jdtitle' => $jdtitle]);
                $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($stations)) {
                    $output = "<div class='row'>
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
                } else {
                    echo "<div class='row'><div class='col-12'><p class='text-center'>No station details found.</p></div></div>";
                }
            } catch (Exception $e) {
                echo "<div class='row'><div class='col-12'><p class='text-center text-danger'>Error: {$e->getMessage()}</p></div></div>";
            }
            break;

        case 'submitRequest':
            try {
                $requestId = $_POST['requestId'];

                // Verify that this request belongs to the current HOD
                $verifyQuery = "SELECT staffid FROM staffrequest WHERE jdrequestid = :requestId";
                $stmt = $con->prepare($verifyQuery);
                $stmt->execute(['requestId' => $requestId]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                //if ($request && $request['staffid'] === CURRENT_USER['staffid']) {
                    $hod->submitHODRequest($requestId);
                    echo "Request submitted successfully. HR will be notified for review.";
                //} else {
                  //  throw new Exception("Unauthorized access or invalid request.");
                //}
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'checkRequestStatus':
            try {
                $requestId = $_POST['requestId'];
                $query = "SELECT status FROM staffrequest WHERE jdrequestid = :requestId";
                $stmt = $con->prepare($query);
                $stmt->execute(['requestId' => $requestId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $result['status'];
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'submitDraftRequest':
            try {
                $requestId = $_POST['requestId'];

                // Verify that this request belongs to the current HOD
                $verifyQuery = "SELECT staffid FROM staffrequest WHERE jdrequestid = :requestId";
                $stmt = $con->prepare($verifyQuery);
                $stmt->execute(['requestId' => $requestId]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($request && $request['staffid'] === CURRENT_USER['staffid']) {
                    $hod->submitHODRequest($requestId);
                    echo "Request submitted successfully. HR will be notified for review.";
                } else {
                    throw new Exception("Unauthorized access or invalid request.");
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'checkRequestStatus':
            try {
                $requestId = $_POST['requestId'];
                $query = "SELECT status FROM staffrequest WHERE jdrequestid = :requestId";
                $stmt = $con->prepare($query);
                $stmt->execute(['requestId' => $requestId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $result['status'];
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;


        case 'getHODDepartmentRequests':
            try {
                $deptCode = CURRENT_USER['departmentcode'];
                $requests = $hod->getHODDepartmentRequests($deptCode);

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
                                <button class='btn btn-sm btn-info' onclick='viewDepartmentRequestDetails(\"{$request['jdrequestid']}\")'>
                                    View Details
                                </button>
                            </td>
                        </tr>";
                }
            } catch (Exception $e) {
                echo "<tr><td colspan='6' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
            }
            break;

        case 'getDepartmentRequestDetails':
            try {
                $requestId = $_POST['requestId'];
                $details = $hod->getDepartmentRequestDetails($requestId);

                if ($details) {
                    $output = "<div class='job-details'>
                                <h5>Request Details</h5>
                                <p><strong>Request ID:</strong> {$details['jdrequestid']}</p>
                                <p><strong>Job Title:</strong> {$details['jdtitle']}</p>
                                <p><strong>Department Unit:</strong> {$details['deptunitname']}</p>
                                <p><strong>Total Positions:</strong> {$details['novacpost']}</p>
                                <p><strong>Description:</strong> {$details['jddescription']}</p>
                                <p><strong>Educational Qualification:</strong> {$details['eduqualification']}</p>
                                <p><strong>Professional Qualification:</strong> {$details['proqualification']}</p>
                              </div>
                              <div class='station-details mt-4'>
                                <h5>Station Details</h5>
                                <table class='table table-bordered'>
                                    <thead>
                                        <tr>
                                            <th>Station</th>
                                            <th>Employment Type</th>
                                            <th>Staff Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                    $stations = explode(',', $details['stations']);
                    $types = explode(',', $details['employment_types']);
                    $counts = explode(',', $details['staff_counts']);

                    for ($i = 0; $i < count($stations); $i++) {
                        $output .= "<tr>
                                    <td>{$stations[$i]}</td>
                                    <td>{$types[$i]}</td>
                                    <td>{$counts[$i]}</td>
                                   </tr>";
                    }

                    $output .= "</tbody></table></div>";
                    echo $output;
                } else {
                    echo "<p>No details found for this request.</p>";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'approveHODDepartmentRequest':
            try {
                $requestId = $_POST['requestId'];
                $comments = $_POST['comments'] ?? '';

                if ($hod->approveHODDepartmentRequest($requestId, $comments)) {
                    echo "Request approved successfully.";
                } else {
                    echo "Failed to approve request.";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'declineHODDepartmentRequest':
            try {
                $requestId = $_POST['requestId'];
                $comments = $_POST['comments'] ?? '';

                if (empty($comments)) {
                    echo "Please provide a reason for declining.";
                    return;
                }

                if ($hod->declineHODDepartmentRequest($requestId, $comments)) {
                    echo "Request declined successfully.";
                } else {
                    echo "Failed to decline request.";
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