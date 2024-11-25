<?php
require_once '../include/config.php';
require_once 'deptunit.php';

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
                $result = $deptunit->declineDeptUnitLeadStation(
                    $_POST['jdrequestid'],
                    $_POST['station'],
                    $_POST['reason']
                );
                echo $result ? 'success' : 'error';
            } catch (Exception $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;
    }
}
