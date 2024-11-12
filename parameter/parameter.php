<?php
include('../include/config.php');

$_SESSION['username'] = "olumde";

// Initialize the Revenue class
$revenue = new Revenue($con);

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if the expected data for staff request is present
if (isset($data['jdtitle'], $data['jdrequestid'], $data['novacpost'], $data['reason'], $data['eduqualification'], $data['proqualification'])) {

    // Capture the main staff request data
    $jdrequestid = $data['jdrequestid'];
    $jdtitle = $data['jdtitle'];
    $novacpost = $data['novacpost'];
    $reason = $data['reason'];
    $eduqualification = $data['eduqualification'];
    $proqualification = $data['proqualification'];

    // Optional fields, set to null if not present
    $fuctiontech = $data['fuctiontech'] ?? null;
    $managerial = $data['managerial'] ?? null;
    $behavioural = $data['behavioural'] ?? null;
    $keyresult = $data['keyresult'] ?? null;
    $empdeliveries = $data['empdeliveries'] ?? null;
    $keysuccess = $data['keysuccess'] ?? null;

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

    // Check if the main staff request was successful
    if ($staffrequestInfo > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Staff request submitted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit staff request.']);
        exit;
    }

    // Check if station details are provided
    if (isset($data['station'], $data['employmenttype'], $data['staffperstation'])) {
        $station = $data['station'];
        $employmenttype = $data['employmenttype'];
        $staffperstation = $data['staffperstation'];

        // Log station details for debugging
        error_log("Station: $station, Employment Type: $employmenttype, Staff per Station: $staffperstation");

        // Save staff request per station details
        $stationInfo = $revenue->createOrUpdateStaffRequestPerStation(
            $jdrequestid,
            $station,
            $employmenttype,
            $staffperstation
        );

        // Check if the station info was saved successfully
        if ($stationInfo > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Station-specific information saved.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save station-specific information.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing station or employment details.']);
        exit;
    }
} else {
    // Handle missing required fields
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing request data.']);
    exit;
}
