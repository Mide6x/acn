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
                echo json_encode(['success' => $result]);
                break;

            case 'add_station':
                $result = $revenue->saveStationRequest(
                    $_POST['jdrequestid'],
                    $_POST['station'],
                    $_POST['employmenttype'],
                    $_POST['staffperstation'],
                    $createdby
                );
                echo json_encode(['success' => $result]);
                break;

            case 'submit_request':
                $result = $revenue->submitRequest($_POST['jdrequestid']);
                echo json_encode(['success' => $result]);
                break;

            case 'hr_action':
                $result = $revenue->updateStationRequestStatus(
                    $_POST['jdrequestid'],
                    $_POST['station'],
                    $_POST['status'],
                    $_POST['reason'] ?? null
                );
                echo json_encode(['success' => $result]);
                break;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        switch ($_GET['action']) {
            case 'generate_id':
                echo json_encode(['requestId' => $revenue->generateRequestId()]);
                break;

            case 'get_summary':
                echo json_encode($revenue->getRequestSummary($_GET['jdrequestid']));
                break;

            case 'get_requests':
                $requests = $revenue->getRequestsByDepartment($deptunitcode);
                echo json_encode(['requests' => $requests]);
                break;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
