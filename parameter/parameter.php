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
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: {$e->getMessage()}</div>";
    }
    exit;
}
