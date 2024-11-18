<?php
session_start();
require_once('../include/config.php');

$revenue = new Revenue($con);
$createdby = $_SESSION['email'] ?? DEFAULT_CREATED_BY;
$deptunitcode = $_SESSION['deptunitcode'] ?? DEFAULT_DEPT_UNIT_CODE;

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
        }
    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        switch ($_GET['action']) {
            case 'generate_id':
                echo $revenue->generateRequestId();
                break;

            case 'get_requests':
                $requests = $revenue->getRequestsByDepartment($deptunitcode);
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
                    }
                    $output .= "<button onclick=\"toggleStationDetails('{$request['jdrequestid']}')\" class='btn btn-sm btn-info'>View Stations</button>";
                    $output .= "</td>";
                    $output .= "</tr>";

                    // Add hidden row for station details
                    $output .= "<tr id='stations-{$request['jdrequestid']}' style='display:none'>";
                    $output .= "<td colspan='5'>";
                    $output .= "<table class='table table-sm'>";
                    $output .= "<thead><tr><th>Station</th><th>Employment Type</th><th>Staff Count</th><th>Status</th></tr></thead>";
                    $output .= "<tbody>";

                    foreach ($request['stations'] as $station) {
                        $output .= "<tr>";
                        $output .= "<td>{$station['station']}</td>";
                        $output .= "<td>{$station['employmenttype']}</td>";
                        $output .= "<td>{$station['staffperstation']}</td>";
                        $output .= "<td>{$station['status']}</td>";
                        $output .= "</tr>";
                    }

                    $output .= "</tbody></table>";
                    $output .= "</td></tr>";
                }

                echo $output ?: "<tr><td colspan='5' class='text-center'>No requests found</td></tr>";
                break;

            case 'get_request_details':
                $request = $revenue->getRequestDetails($_GET['jdrequestid']);
                $output = "<div class='request-details'>";
                $output .= "<h4>Request Details</h4>";
                $output .= "<p><strong>Job Title:</strong> {$request['jdtitle']}</p>";
                $output .= "<p><strong>Total Staff:</strong> {$request['novacpost']}</p>";
                $output .= "<p><strong>Status:</strong> {$request['status']}</p>";
                $output .= "</div>";
                echo $output;
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
        }
    } catch (Exception $e) {
        echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
    }
}
