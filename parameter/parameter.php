<?php
include('../include/config.php');

// Initialize the Revenue class
$revenue = new Revenue($con);

// Capture incoming POST data
if (isset($_POST['jdtitle'], $_POST['jdrequestid'], $_POST['novacpost'], $_POST['reason'], $_POST['eduqualification'], $_POST['proqualification'])) {

    // Capture the main staff request data
    $jdrequestid = $_POST['jdrequestid'];
    $jdtitle = $_POST['jdtitle'];
    $novacpost = $_POST['novacpost'];
    $reason = $_POST['reason'];
    $eduqualification = $_POST['eduqualification'];
    $proqualification = $_POST['proqualification'];

    // Optional fields, set to null if not present
    $fuctiontech = $_POST['fuctiontech'] ?? null;
    $managerial = $_POST['managerial'] ?? null;
    $behavioural = $_POST['behavioural'] ?? null;
    $keyresult = $_POST['keyresult'] ?? null;
    $empdeliveries = $_POST['empdeliveries'] ?? null;
    $keysuccess = $_POST['keysuccess'] ?? null;

    // Save the main staff request
    $staffrequestInfo = $revenue->createOrUpdateStaffRequest(
        $jdrequestid,
        $jdtitle,
        $novacpost,
        $reason,
        $eduqualification,
        $proqualification,
        $fuctiontech,
        $managerial,
        $behavioural,
        $keyresult,
        $empdeliveries,
        $keysuccess
    );

    // Direct echo for the main staff request result
    if ($staffrequestInfo > 0) {
        echo 'Staff request submitted successfully.';
    } else {
        echo 'Failed to submit staff request.';
        exit;
    }

    // Check if station details are provided
    if (isset($_POST['station'], $_POST['employmenttype'], $_POST['staffperstation'])) {
        $station = $_POST['station'];
        $employmenttype = $_POST['employmenttype'];
        $staffperstation = $_POST['staffperstation'];

        // Save staff request per station details
        $stationInfo = $revenue->createOrUpdateStaffRequestPerStation(
            $jdrequestid,
            $station,
            $employmenttype,
            $staffperstation
        );

        // Direct echo for station info save result
        if ($stationInfo > 0) {
            echo 'Station-specific information saved.';
        } else {
            echo 'Failed to save station-specific information.';
        }
    } else {
        echo 'Missing station or employment details.';
        exit;
    }

    // Handle POST request for department unit and fetch positions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if deptunitcode is selected
        if (isset($_POST['jddepartmentunit'])) {
            $deptunitcode = $_POST['jddepartmentunit'];

            // Fetch positions based on the selected department
            $positions = $revenue->getPositionsByDepartment($deptunitcode);

            // Direct echo positions
            if ($positions) {
                foreach ($positions as $position) {
                    echo $position['position_name'] . '<br>';
                }
            } else {
                echo 'No positions found.';
            }
        }
    }
} else {
    echo 'Invalid or missing request data.';
    exit;
}
