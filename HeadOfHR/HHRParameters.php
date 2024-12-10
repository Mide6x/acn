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
                if (!isset($_POST['requestId'])) {
                    throw new Exception("Request ID is required");
                }
                $details = $hhr->getRequestDetails($_POST['requestId']);
                echo json_encode($details);
                break;

            case 'approve_request':
                if (!isset($_POST['requestId'])) {
                    throw new Exception("Request ID is required");
                }
                $result = $hhr->updateRequestStatus($_POST['requestId'], 'approved');
                echo $result ? 'success' : 'error';
                break;

            case 'decline_request':
                if (!isset($_POST['requestId']) || !isset($_POST['comments'])) {
                    throw new Exception("Request ID and comments are required");
                }
                $result = $hhr->updateRequestStatus($_POST['requestId'], 'declined', $_POST['comments']);
                echo $result ? 'success' : 'error';
                break;
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="5" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
    }
}
