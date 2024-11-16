<?php
session_start();
require_once('../include/config.php');
require_once('../class/Revenue.php');

$revenue = new Revenue($con);
$createdby = $_SESSION['email'] ?? DEFAULT_CREATED_BY;
$deptunitcode = $_SESSION['deptunitcode'] ?? DEFAULT_DEPT_UNIT_CODE;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'generate_id') {
            echo $revenue->generateRequestId();
        } elseif ($_POST['action'] === 'get_job_titles') {
            echo $revenue->getJobTitles();
        } elseif ($_POST['action'] === 'get_stations') {
            echo $revenue->getStations();
        } elseif ($_POST['action'] === 'get_staff_types') {
            echo $revenue->getStaffTypes();
        } elseif ($_POST['action'] === 'save_draft' && isset($_POST['jdrequestid'], $_POST['jdtitle'], $_POST['novacpost'])) {
            try {
                if ($revenue->saveMainRequest(
                    $_POST['jdrequestid'],
                    $_POST['jdtitle'],
                    $_POST['novacpost'],
                    $deptunitcode,
                    'draft',
                    $createdby
                )) {
                    echo "Draft saved successfully";
                } else {
                    echo "Error saving draft";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } elseif (
            $_POST['action'] === 'submit_request' &&
            isset($_POST['jdrequestid'], $_POST['jdtitle'], $_POST['novacpost'], $_POST['stations'])
        ) {

            $totalStaffPerStation = 0;
            foreach ($_POST['stations'] as $request) {
                $totalStaffPerStation += intval($request['staffperstation']);
            }

            if ($totalStaffPerStation != $_POST['novacpost']) {
                echo "Error: Total staff per station must match the number of vacant positions";
                exit;
            }

            try {
                $con->beginTransaction();

                if (!$revenue->saveMainRequest(
                    $_POST['jdrequestid'],
                    $_POST['jdtitle'],
                    $_POST['novacpost'],
                    $deptunitcode,
                    'pending',
                    $createdby
                )) {
                    throw new Exception("Error saving main request");
                }

                foreach ($_POST['stations'] as $request) {
                    if (!$revenue->saveStationRequest(
                        $_POST['jdrequestid'],
                        $request['station'],
                        $request['employmenttype'],
                        $request['staffperstation'],
                        'pending',
                        $createdby
                    )) {
                        throw new Exception("Error saving station request for " . $request['station']);
                    }
                }

                $con->commit();
                echo "Request submitted successfully";
            } catch (Exception $e) {
                $con->rollBack();
                echo "Error: " . $e->getMessage();
            }
        }
    }

    // Add handler for createstaffreqperstation
    if (isset(
        $_POST['jdrequestid'],
        $_POST['jdtitle'],
        $_POST['station'],
        $_POST['employmenttype'],
        $_POST['staffperstation']
    )) {
        try {
            // Save to staffrequestperstation table
            $result = $revenue->saveStationRequest(
                $_POST['jdrequestid'],
                $_POST['station'],
                $_POST['employmenttype'],
                $_POST['staffperstation'],
                'draft',
                $createdby
            );

            if ($result) {
                echo "Approved";
            } else {
                echo "Not Okay";
            }
            exit;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }

    exit;
}

// Handle GET requests

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'get_requests') {
            $requests = $staffRequest->getRequestsByDepartment($deptunitcode);
            echo json_encode($requests);
            exit;
        }

        if ($_GET['action'] === 'get_request_details' && isset($_GET['requestId'])) {
            $details = $revenue->getStationsByRequestId($_GET['requestId']);
            echo json_encode($details);
            exit;
        }
        if ($_GET['action'] === 'load_dropdowns') {
            echo "<div class='job-titles'>" . $revenue->getJobTitles() . "</div>";
            echo "<div class='stations'>" . $revenue->getStations() . "</div>";
            echo "<div class='staff-types'>" . $revenue->getStaffTypes() . "</div>";
            exit;
        }
    }
    exit;
}
?>
}