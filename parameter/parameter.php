<?php
$_SESSION['username'] = 'adewole.o@acn.aero';
$_SESSION['staffid'] = 'O2024011';
$_SESSION['stnames'] = 'Adewole Olumide';
$_SESSION['deptunitcode'] = 'ICT';

include('../include/config.php');

// Initialize the Revenue class
$revenue = new Revenue($con);

// Check if the session user ID exists for proper tracking of createdby
$createdby = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no user is logged in

// Capture and sanitize incoming POST data
if (isset(
    $_POST['jdtitle'],
    $_POST['jdrequestid'],
    $_POST['novacpost'],
    $_POST['status'],
    $_POST['station'],
    $_POST['employmenttype'],
    $_POST['staffperstation']
)) {
    // Sanitize incoming data
    $jdrequestid = htmlspecialchars($_POST['jdrequestid']);
    $jdtitle = htmlspecialchars($_POST['jdtitle']);
    $novacpost = (int)$_POST['novacpost'];
    $status = htmlspecialchars($_POST['status']);
    $station = htmlspecialchars($_POST['station']);
    $employmenttype = htmlspecialchars($_POST['employmenttype']);
    $staffperstation = (int)$_POST['staffperstation'];
    $reason = isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : null;

    // Save the main staff request data
    $staffrequestInfo = $revenue->createOrUpdateStaffRequest(
        $jdrequestid,
        $jdtitle,
        $novacpost,
        $status,
        $createdby
    );

    if ($staffrequestInfo > 0) {
        echo 'Staff request submitted successfully.';
    } else {
        echo 'Failed to submit staff request.';
        exit;
    }

    // Save station-specific information
    $stationInfo = $revenue->createOrUpdateStaffRequestPerStation(
        $jdrequestid,
        $station,
        $employmenttype,
        $staffperstation,
        $status,
        $reason,
        $createdby
    );

    if ($stationInfo > 0) {
        echo 'Station-specific information saved.';
    } else {
        echo 'Failed to save station-specific information.';
        exit;
    }
} else {
    // Check if any required data is missing
    echo 'Invalid or missing request data. Please ensure all fields are filled out correctly.';
    exit;
}
