<?php
require_once '../include/config.php';
require_once 'deptunit.php';

$_SESSION['departmentcode'] = CURRENT_USER['departmentcode'];

$deptunit = new DeptUnit($con);

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($_GET['action']) {
        case 'get_deptunitlead_requests':
            // Get the deptunitcode from session
            $deptunitcode = $_SESSION['deptunitcode'];
            echo $deptunit->getDeptUnitLeadRequests($deptunitcode);
            break;

        case 'get_deptunitlead_request_details':
            if (isset($_GET['jdrequestid'])) {
                echo $deptunit->getDeptUnitLeadRequestDetails($_GET['jdrequestid']);
            }
            break;

        case 'get_new_station_request_html':
            try {
                // Get stations and employment types
                $stations = $deptunit->getStations();
                $employmentTypes = $deptunit->getStaffTypes();

                $html = '
                <div class="station-request">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label">Station</label>
                            <select class="form-control" name="station" style="border-radius: 8px" 
                                    required onchange="validateStationSelection(this)">
                                <option value="">Select Station</option>
                                ' . $stations . '
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Employment Type</label>
                            <select class="form-control" name="employmenttype" style="border-radius: 8px" required>
                                <option value="">Select Type</option>
                                ' . $employmentTypes . '
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Staff Per Station</label>
                            <input type="number" class="form-control staffperstation" 
                                   name="staffperstation" style="border-radius: 8px" 
                                   required min="1">
                        </div>
                        <div class="col-sm-12 mt-2">
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="removeStationRequest(this)">
                                Remove Station
                            </button>
                        </div>
                    </div>
                </div>';

                echo $html;
            } catch (Exception $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;
    }
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'approve_deptunitlead_station':
            try {
                $result = $deptunit->approveDeptUnitLeadStation(
                    $_POST['jdrequestid'],
                    $_POST['station']
                );
                echo $result ? 'success' : 'error';
            } catch (Exception $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        case 'decline_deptunitlead_station':
            try {
                $result = $deptunit->declineDeptUnitLeadRequest(
                    $_POST['jdrequestid'],
                    $_POST['station'],
                    $_POST['reason']
                );
                echo $result ? 'success' : 'error';
            } catch (Exception $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        case 'save_draft_deptunitlead':
            try {
                // Create structured data array
                $formData = [
                    'jdrequestid' => $_POST['jdrequestid'],
                    'jdtitle' => $_POST['jdtitle'],
                    'novacpost' => $_POST['novacpost'],
                    'deptunitcode' => $_POST['deptunitcode'],
                    'subdeptunitcode' => $_POST['subdeptunitcode'],
                    'departmentcode' => $_POST['departmentcode'],
                    'createdby' => $_POST['createdby'],
                    'status' => 'draft',
                    'staffid' => $_SESSION['staffid'],
                    'stations' => []
                ];

                // Process station data
                $index = 0;
                while (isset($_POST["station_$index"])) {
                    $formData['stations'][] = [
                        'station' => $_POST["station_$index"],
                        'employmenttype' => $_POST["employmenttype_$index"],
                        'staffperstation' => $_POST["staffperstation_$index"]
                    ];
                    $index++;
                }

                $result = $deptunit->createStaffRequest($formData);
                echo $result ? 'success' : 'error';
            } catch (Exception $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        case 'submit_deptunitlead_request':
            try {
                if (!isset($_POST['formData'])) {
                    throw new Exception('No form data provided');
                }

                $formData = $_POST['formData'];

                // Validate request ID
                if (empty($formData['jdrequestid'])) {
                    throw new Exception('Request ID is required');
                }

                error_log("Processing submission for request ID: " . $formData['jdrequestid']);

                // Begin transaction
                $con->beginTransaction();

                // Insert into staffrequest table
                $query = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby, subdeptunitcode, staffid, departmentcode) 
                         VALUES (?, ?, ?, ?, 'draft', ?, ?, ?, ?)";

                $stmt = $con->prepare($query);
                $stmt->execute([
                    $formData['jdrequestid'],
                    $formData['jdtitle'],
                    $formData['novacpost'],
                    $formData['deptunitcode'],
                    $_SESSION['staffid'],
                    $formData['subdeptunitcode'],
                    $_SESSION['staffid'],
                    $_SESSION['departmentcode']
                ]);

                // Insert station details
                foreach ($formData['stations'] as $station) {
                    $query = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, createdby) 
                             VALUES (?, ?, ?, ?, ?)";

                    $stmt = $con->prepare($query);
                    $stmt->execute([
                        $formData['jdrequestid'],
                        $station['station'],
                        $station['employmenttype'],
                        $station['staffperstation'],
                        $_SESSION['staffid']
                    ]);
                }

                $con->commit();
                echo 'success';
            } catch (Exception $e) {
                $con->rollBack();
                error_log("Error in submit_deptunitlead_request: " . $e->getMessage());
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'get_request_details':
            try {
                $requestId = $_POST['requestId'];

                // Get request details with approval status
                $query = "SELECT 
                    sr.*,
                    jt.jobdescription,
                    jt.jobresponsibilities,
                    jt.jobrequirements,
                    hod.status as hod_status,
                    hod.dandt as hod_date,
                    hod.comments as hod_comments,
                    COALESCE(
                        (SELECT approvallevel 
                         FROM approvaltbl 
                         WHERE jdrequestid = sr.jdrequestid 
                         AND status IN ('pending', 'draft')  -- Include 'draft' explicitly
                         ORDER BY id ASC 
                         LIMIT 1),
                        'Completed'
                    ) as current_level
                FROM staffrequest sr
                LEFT JOIN jobtitletbl jt ON sr.jdtitle = jt.jobtitle
                LEFT JOIN approvaltbl hod ON sr.jdrequestid = hod.jdrequestid 
                    AND hod.approvallevel = 'HOD'
                WHERE sr.jdrequestid = ?";

                // Use the DeptUnit class method to get request details
                $requestDetails = $deptunit->getRequestDetails($requestId);

                // Get station details using DeptUnit class method
                $stations = $deptunit->getStationDetails($requestId);

                // Get approval workflow using DeptUnit class method
                $approvals = $deptunit->getApprovalWorkflow($requestId);

                $output = "
                <input type='hidden' id='requestStatus' value='{$requestDetails['status']}'>
                <div class='row mb-3'>
                    <div class='col-md-6'>
                        <h6>Request Details</h6>
                        <p><strong>Request ID:</strong> {$requestDetails['jdrequestid']}</p>
                        <p><strong>Job Title:</strong> {$requestDetails['jdtitle']}</p>
                        <p><strong>Total Positions:</strong> {$requestDetails['novacpost']}</p>
                        <p><strong>Status:</strong> {$requestDetails['status']}</p>
                        <p><strong>Current Level:</strong> {$requestDetails['current_level']}</p>
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

                <div class='row mb-3'>
                    <div class='col-12'>
                        <h6>Approval Workflow</h6>
                        <div class='approval-timeline'>
                            <div class='timeline-wrapper'>";

                $approvalStages = ['HOD', 'HR', 'HeadOfHR', 'CFO', 'CEO'];
                $currentLevel = '';
                $approvalStatuses = [];

                // Get current approval level and statuses
                foreach ($approvals as $approval) {
                    $approvalStatuses[$approval['approvallevel']] = [
                        'status' => $approval['status'],
                        'date' => $approval['dandt'],
                        'comments' => $approval['comments']
                    ];
                    if ($approval['status'] == 'pending') {
                        $currentLevel = $approval['approvallevel'];
                    }
                }

                foreach ($approvalStages as $index => $stage) {
                    $status = isset($approvalStatuses[$stage]) ? $approvalStatuses[$stage]['status'] : 'draft';
                    $date = isset($approvalStatuses[$stage]['date']) ?
                        date('Y-m-d', strtotime($approvalStatuses[$stage]['date'])) : '-';
                    $comments = isset($approvalStatuses[$stage]['comments']) ?
                        $approvalStatuses[$stage]['comments'] : '-';

                    $stageClass = '';
                    if ($status == 'approved') {
                        $stageClass = 'completed';
                    } elseif ($status == 'pending') {
                        $stageClass = 'current';
                    } elseif ($status == 'declined') {
                        $stageClass = 'declined';
                    }
                    $dotClass = ($status === 'declined') ? 'timeline-dot declined' : (($status === 'draft') ? 'timeline-dot draft' : 'timeline-dot');

                    $output .= "<div class='timeline-item {$stageClass}'>
                        <div class='timeline-dot {$dotClass}' data-bs-toggle='tooltip' 
                             title='Status: " . ucfirst($status) . "&#013;Date: {$date}&#013;Comments: {$comments}'>
                        </div>
                        <div class='timeline-label'>{$stage}</div>
                      </div>";

                    if ($index < count($approvalStages) - 1) {
                        $output .= "<div class='timeline-line " . ($status == 'approved' ? 'completed' : '') . "'></div>";
                    }
                }

                $output .= "</div></div></div>";

                // Station Details
                $output .= "
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
                echo "<div class='alert alert-danger'>Error loading request details: " . $e->getMessage() . "</div>";
            }
            break;

        case 'update_request':
            try {
                $requestData = [
                    'jdrequestid' => $_POST['jdrequestid'],
                    'jdtitle' => $_POST['jdtitle'],
                    'novacpost' => $_POST['novacpost'],
                    'stations' => []
                ];

                // Format stations data
                foreach ($_POST['stations'] as $station) {
                    $requestData['stations'][] = [
                        'station' => $station['station'],
                        'employmenttype' => $station['employmenttype'],
                        'staffperstation' => $station['staffperstation']
                    ];
                }

                // Validate total staff count matches novacpost
                $totalStaff = array_sum(array_column($requestData['stations'], 'staffperstation'));
                if ($totalStaff != $requestData['novacpost']) {
                    echo "Error: Total staff per station must equal the number of vacant positions";
                    exit;
                }

                if ($deptunit->updateStaffRequest($requestData)) {
                    echo 'success';
                } else {
                    echo 'Failed to update request';
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'get_station_options':
            $index = $_POST['index'];
            $requestId = $_POST['requestId'] ?? '';

            $output = '<div class="row mb-2 station-row">
                <div class="col-sm-4">
                    <label class="form-label">Station</label>
                    <select class="form-control" name="stations[' . $index . '][station]" style="border-radius: 8px" required>
                        <option value="">Select Station</option>
                        ' . $deptunit->getStations() . '
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label">Employment Type</label>
                    <select class="form-control" name="stations[' . $index . '][employmenttype]" style="border-radius: 8px" required>
                        <option value="">Select Type</option>
                        ' . $deptunit->getStaffTypes() . '
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
            </div>';
            echo $output;
            break;

        case 'get_edit_station_rows':
            try {
                // Get jdrequestid from POST data
                $requestId = $_POST['requestId'];

                if (!$requestId) {
                    throw new Exception('Request ID is required');
                }

                $stations = $deptunit->getStationDetails($requestId);
                $output = '';

                foreach ($stations as $index => $station) {
                    $output .= '<div class="row mb-2 station-row">
                        <div class="col-sm-4">
                            <label class="form-label">Station</label>
                            <select class="form-control" name="stations[' . $index . '][station]" style="border-radius: 8px" required>
                                <option value="">Select Station</option>
                                ' . $deptunit->getStationsWithSelected($station['station']) . '
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Employment Type</label>
                            <select class="form-control" name="stations[' . $index . '][employmenttype]" style="border-radius: 8px" required>
                                <option value="">Select Type</option>
                                ' . $deptunit->getStaffTypesWithSelected($station['employmenttype']) . '
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Staff Per Station</label>
                            <input type="number" class="form-control staffperstation" 
                                   name="stations[' . $index . '][staffperstation]"
                                   value="' . htmlspecialchars($station['staffperstation']) . '"
                                   style="border-radius: 8px" required min="1">
                        </div>
                        <div class="col-sm-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-station">×</button>
                        </div>
                    </div>';
                }
                echo $output;
            } catch (Exception $e) {
                echo "Error loading stations: " . $e->getMessage();
            }
            break;

        case 'submit_draft_request':
            try {
                $requestId = $_POST['jdrequestid'];

                // Update request status to pending
                $updateRequest = "UPDATE staffrequest 
                                 SET status = 'pending'
                                 WHERE jdrequestid = ? 
                                 AND status = 'draft'
                                 AND staffid = ?";

                $stmt = $con->prepare($updateRequest);
                $stmt->execute([$requestId, $_SESSION['staffid']]);

                // Update all associated stations to pending
                $updateStations = "UPDATE staffrequestperstation 
                                  SET status = 'pending'
                                  WHERE jdrequestid = ?";

                $stmt = $con->prepare($updateStations);
                $stmt->execute([$requestId]);

                echo 'success';
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            break;

        case 'update_station_status':
            try {
                if (!isset($_POST['jdrequestid']) || !isset($_POST['station']) || !isset($_POST['status'])) {
                    throw new Exception('Missing required parameters');
                }

                $jdrequestid = $_POST['jdrequestid'];
                $station = $_POST['station'];
                $status = $_POST['status'];
                $reason = $_POST['reason'] ?? null; // Get decline reason if provided

                // Start transaction
                $con->beginTransaction();

                // Update station status and reason
                $query = "UPDATE staffrequestperstation 
                         SET status = ?, 
                             reason = ? 
                         WHERE jdrequestid = ? 
                         AND station = ?";

                $stmt = $con->prepare($query);
                $stmt->execute([$status, $reason, $jdrequestid, $station]);

                // Check if all stations are declined
                $checkQuery = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'DeptUnit Lead Declined' THEN 1 ELSE 0 END) as declined
                FROM staffrequestperstation 
                WHERE jdrequestid = ?";

                $stmt = $con->prepare($checkQuery);
                $stmt->execute([$jdrequestid]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $allStationsDeclined = ($result['total'] > 0 && $result['total'] == $result['declined']);

                // Update main request status based on station statuses
                $mainStatus = $allStationsDeclined ? 'DeptUnit Lead Declined' : 'DeptUnit Lead Approved';
                $updateRequest = "UPDATE staffrequest 
                                 SET status = ?
                                 WHERE jdrequestid = ?";

                $stmt = $con->prepare($updateRequest);
                $stmt->execute([$mainStatus, $jdrequestid]);

                // Update approval table status
                $approvalStatus = $allStationsDeclined ? 'declined' : 'approved';
                $updateApproval = "UPDATE approvaltbl 
                                  SET status = ?,
                                      dandt = NOW()
                                  WHERE jdrequestid = ? 
                                  AND approvallevel = 'DeptUnitLead'";

                $stmt = $con->prepare($updateApproval);
                $stmt->execute([$approvalStatus, $jdrequestid]);

                // If not all declined, update HOD status to pending
                if (!$allStationsDeclined) {
                    $updateHOD = "UPDATE approvaltbl 
                                 SET status = 'pending',
                                     dandt = NOW()
                                 WHERE jdrequestid = ? 
                                 AND approvallevel = 'HOD'";
                    $stmt = $con->prepare($updateHOD);
                    $stmt->execute([$jdrequestid]);
                }

                $con->commit();
                echo 'success';
            } catch (Exception $e) {
                $con->rollBack();
                error_log("Error in update_station_status: " . $e->getMessage());
                echo "Error: " . $e->getMessage();
            }
            break;
    }
}
