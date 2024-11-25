<?php
require_once('../include/config.php');

$revenue = new Revenue($con);
$createdby = getCurrentUser('email');
$deptunitcode = getCurrentUser('deptunitcode');
$response = ['success' => false, 'message' => ''];
// Add error logging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Action: " . ($_POST['action'] ?? $_GET['action'] ?? 'no action'));
error_log("Department Code: " . $deptunitcode);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'save_draft':
                $result = $revenue->saveDraftRequest(
                    $_POST['jdrequestid'],
                    $_POST['jdtitle'],
                    $_POST['novacpost'],
                    $deptunitcode,
                    $createdby
                );
                echo $result ? 'success' : 'failure';
                break;

            case 'add_station':
                $result = $revenue->saveStationRequest(
                    $_POST['jdrequestid'],
                    $_POST['station'],
                    $_POST['employmenttype'],
                    $_POST['staffperstation'],
                    $createdby
                );
                echo $result ? 'success' : 'failure';
                break;

            case 'submit_request':
                try {
                    $result = $revenue->submitRequest($_POST['jdrequestid']);
                    echo $result ? 'success' : 'failure';
                } catch (Exception $e) {
                    echo 'error: ' . $e->getMessage();
                }
                break;

            case 'update_station_status':
                try {
                    $jdrequestid = $_POST['jdrequestid'];
                    $station = $_POST['station'];
                    $status = $_POST['status'];
                    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

                    $revenue->updateStationStatus($jdrequestid, $station, $status, $reason);
                    echo 'success';
                } catch (Exception $e) {
                    http_response_code(500);
                    echo $e->getMessage();
                }
                break;

            case 'update_request':
                try {
                    $result = $revenue->updateStaffRequest(
                        $_POST['jdrequestid'],
                        $_POST['jdtitle'],
                        $_POST['novacpost']
                    );
                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'delete_stations':
                try {
                    $result = $revenue->deleteStationRequest($_POST['jdrequestid'], $_POST['station']);
                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'update_novacpost':
                try {
                    $jdrequestid = $_POST['jdrequestid'];
                    $novacpost = $_POST['novacpost'];

                    $sql = "UPDATE staffrequest 
                            SET novacpost = :novacpost 
                            WHERE jdrequestid = :jdrequestid";

                    $stmt = $con->prepare($sql);
                    $result = $stmt->execute([
                        ':novacpost' => $novacpost,
                        ':jdrequestid' => $jdrequestid
                    ]);

                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'create_staff_request':
                try {
                    $jdrequestid = $_POST['jdrequestid'];
                    $jdtitle = $_POST['jdtitle'];
                    $novacpost = $_POST['novacpost'];

                    $result = $revenue->createStaffRequest(
                        $jdrequestid,
                        $jdtitle,
                        $novacpost,
                        $deptunitcode,
                        'draft',
                        $createdby
                    );

                    echo $result ? 'success' : 'error';
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;


            case 'submitFinalRequest':
                try {
                    $jdrequestid = $_POST['jdrequestid'];

                    // Update staffrequest status
                    $query = "UPDATE staffrequest SET status = 'pending' WHERE jdrequestid = ?";
                    $stmt = $con->prepare($query);
                    $stmt->execute([$jdrequestid]);

                    // Update staffrequestperstation status
                    $query = "UPDATE staffrequestperstation SET status = 'pending' WHERE jdrequestid = ?";
                    $stmt = $con->prepare($query);
                    $stmt->execute([$jdrequestid]);

                    // Update first approval level to pending
                    $query = "UPDATE approvaltbl 
                             SET status = 'pending' 
                             WHERE jdrequestid = ? 
                             AND approvallevel = 'DeptUnitLead'";
                    $stmt = $con->prepare($query);
                    $stmt->execute([$jdrequestid]);

                    echo "Request submitted successfully!";
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'save_draft_request':
                try {
                    $data = [
                        'jdrequestid' => $_POST['jdrequestid'],
                        'jdtitle' => $_POST['jdtitle'],
                        'stations' => $_POST['stations[]'],
                        'employmentTypes' => $_POST['employmentTypes[]'],
                        'staffPerStation' => $_POST['staffPerStation[]'],
                        'createdby' => $createdby,
                        'deptunitcode' => $deptunitcode,
                        'subdeptunitcode' => $_SESSION['subdeptunitcode'],
                        'staffid' => $_SESSION['staffid']
                    ];

                    $result = $revenue->saveTeamLeadDraftRequest($data);
                    echo $result ? "Draft saved successfully" : "Error saving draft";
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;



            case 'save_station':
                try {
                    $result = $revenue->saveStationRequest(
                        $_POST['jdrequestid'],
                        $_POST['station'],
                        $_POST['employmenttype'],
                        $_POST['staffperstation'],
                        $_SESSION['staffid']
                    );
                    echo $result ? "success" : "error";
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
                break;
        }
    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        switch ($_GET['action']) {
            case 'generate_id':
                echo $revenue->generateRequestId();
                break;

            case 'get_requests':
                try {
                    $requests = $revenue->getRequestsByDepartment($deptunitcode);
                    error_log("Requests found: " . json_encode($requests));
                    $output = '';

                    foreach ($requests as $request) {
                        $output .= "<tr>";
                        $output .= "<td>{$request['jdrequestid']}</td>";
                        $output .= "<td>{$request['jdtitle']}</td>";
                        $output .= "<td>{$request['novacpost']}</td>";
                        $output .= "<td>{$request['status']}</td>";
                        $output .= "<td>";
                        if ($request['status'] === 'draft') {
                            $output .= "<button onclick=\"editRequest('{$request['jdrequestid']}')\" class='btn btn-sm btn-warning'>Edit</button> ";
                            $output .= "<button onclick=\"submitRequest('{$request['jdrequestid']}')\" class='btn btn-sm btn-primary'>Submit</button> ";
                        } else {
                            $output .= "<button onclick=\"toggleStationDetails('{$request['jdrequestid']}')\" class='btn btn-sm btn-info'>View Details</button>";
                        }
                        $output .= "</td>";
                        $output .= "</tr>";
                    }
                    echo $output;
                } catch (Exception $e) {
                    error_log("Error in get_requests: " . $e->getMessage());
                    echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                }
                break;

            case 'get_request_details':
                try {
                    $jdrequestid = $_GET['jdrequestid'];
                    $requestData = $revenue->getRequestDetails($jdrequestid);
                    $requestData['deptunitcode'] = $deptunitcode;
                    $requestData['availablepositions'] = $revenue->getAvailablePositions($deptunitcode);
                    echo json_encode($requestData);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;



            case 'get_pending_requests':
                try {
                    $requests = $revenue->getPendingRequests();
                    $output = '';

                    if (!empty($requests)) {
                        foreach ($requests as $request) {
                            $output .= "<tr>";
                            $output .= "<td>{$request['jdrequestid']}</td>";
                            $output .= "<td>{$request['deptunitcode']}</td>";
                            $output .= "<td>{$request['jdtitle']}</td>";
                            $output .= "<td>{$request['novacpost']}</td>";
                            $output .= "<td>";
                            $output .= "<button onclick=\"toggleStationDetails('{$request['jdrequestid']}', '{$request['jdtitle']}')\" 
                                        class='btn btn-sm btn-info'>View Details</button>";
                            $output .= "</td>";
                            $output .= "</tr>";
                        }
                    } else {
                        $output = "<tr><td colspan='5' class='text-center'>No pending requests found</td></tr>";
                    }

                    echo $output;
                } catch (Exception $e) {
                    echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                }
                break;

            case 'get_jobtitle_details':
                try {
                    $details = $revenue->getJobTitleDetails($_GET['jdtitle']);
                    header('Content-Type: application/json');
                    echo json_encode($details ?: ['error' => 'No details found']);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                exit;

            case 'get_station_requests':
                try {
                    $stations = $revenue->getStationRequests($_GET['jdrequestid']);
                    $output = '';

                    if (!empty($stations)) {
                        foreach ($stations as $station) {
                            $output .= "<tr>";
                            $output .= "<td>{$station['station']}</td>";
                            $output .= "<td>{$station['employmenttype']}</td>";
                            $output .= "<td>{$station['staffperstation']}</td>";
                            $output .= "<td>" . ucfirst($station['status']) . "</td>";
                            $output .= "<td class='text-center'>";

                            // Always show action buttons for pending requests
                            if ($station['status'] === 'pending' || $station['status'] === '') {
                                $output .= "<div class='btn-group btn-group-sm'>";
                                $output .= "<button onclick=\"approveStation('{$_GET['jdrequestid']}', '{$station['station']}')\" 
                                            class='btn btn-success me-1'><i class='bi bi-check-circle'></i> Approve</button>";
                                $output .= "<button onclick=\"showDeclineModal('{$_GET['jdrequestid']}', '{$station['station']}')\" 
                                            class='btn btn-danger'><i class='bi bi-x-circle'></i> Decline</button>";
                                $output .= "</div>";
                            } else {
                                $output .= "<span class='badge " . ($station['status'] === 'approved' ? 'bg-success' : 'bg-danger') . "'>";
                                $output .= ucfirst($station['status']);
                                $output .= "</span>";
                                if ($station['reason']) {
                                    $output .= "<br><small class='text-muted'>Reason: {$station['reason']}</small>";
                                }
                            }
                            $output .= "</td>";
                            $output .= "</tr>";
                        }
                    } else {
                        $output = "<tr><td colspan='5' class='text-center'>No station requests found</td></tr>";
                    }

                    echo $output;
                } catch (Exception $e) {
                    echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
                }
                break;

            case 'get_new_request_details':
                try {
                    $newRequestId = $revenue->generateRequestId();
                    $availablePositions = $revenue->getAvailablePositions($deptunitcode);
                    echo json_encode([
                        'jdrequestid' => $newRequestId,
                        'deptunitcode' => $deptunitcode,
                        'availablepositions' => $availablePositions
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;

            case 'get_request_full_details':
                try {
                    $jdrequestid = $_GET['jdrequestid'];

                    // Get main request details
                    $requestQuery = "SELECT r.*, d.deptunitname 
                                    FROM staffrequest r
                                    JOIN departmentunit d ON r.deptunitcode = d.deptunitcode
                                    WHERE r.jdrequestid = ?";
                    $stmt = $con->prepare($requestQuery);
                    $stmt->execute([$jdrequestid]);
                    $request = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Get station details
                    $stationQuery = "SELECT s.*, st.stationname 
                                    FROM staffrequestperstation s
                                    JOIN stationtbl st ON s.station = st.stationcode
                                    WHERE s.jdrequestid = ?";
                    $stmt = $con->prepare($stationQuery);
                    $stmt->execute([$jdrequestid]);
                    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Output HTML directly
                    echo "<div class='request-info mb-4'>";
                    echo "<h6 class='fw-bold'>Request Information</h6>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-6'>";
                    echo "<p><strong>Request ID:</strong> {$request['jdrequestid']}</p>";
                    echo "<p><strong>Job Title:</strong> {$request['jdtitle']}</p>";
                    echo "</div>";
                    echo "<div class='col-md-6'>";
                    echo "<p><strong>Total Positions:</strong> {$request['novacpost']}</p>";
                    echo "<p><strong>Status:</strong> {$request['status']}</p>";
                    echo "</div></div></div>";

                    echo "<div class='station-info'>";
                    echo "<h6 class='fw-bold'>Station Requests</h6>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead><tr>";
                    echo "<th>Station</th>";
                    echo "<th>Employment Type</th>";
                    echo "<th>Staff Count</th>";
                    echo "<th>Status</th>";
                    echo "</tr></thead><tbody>";

                    foreach ($stations as $station) {
                        echo "<tr>";
                        echo "<td>{$station['stationname']}</td>";
                        echo "<td>{$station['employmenttype']}</td>";
                        echo "<td>{$station['staffperstation']}</td>";
                        echo "<td>{$station['status']}</td>";
                        echo "</tr>";
                    }

                    echo "</tbody></table></div></div>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error loading request details: {$e->getMessage()}</div>";
                }
                break;
        }
    } catch (Exception $e) {
        error_log("Error processing GET request: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
