<?php
require_once('../include/config.php');
require_once('../class/rev.php');

function runTest($name, $callback)
{
    try {
        $result = $callback();
        echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Test: $name</strong><br>";
        echo "Result: " . ($result ? "✅ Passed" : "❌ Failed");
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Test: $name</strong><br>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "Result: ❌ Failed";
        echo "</div>";
    }
}

$revenue = new Revenue($con);
$deptunitcode = 'ICT';
$createdby = 'adewole.o@acn.aero';

// First, ensure we have valid reference data
runTest("Setup Reference Data", function () use ($con) {
    // Insert department if not exists
    $stmt = $con->prepare("INSERT IGNORE INTO departmentunit (deptunitcode, deptunitname) VALUES (?, ?)");
    $stmt->execute(['ICT', 'Information Technology']);

    // Insert job title if not exists
    $stmt = $con->prepare("INSERT IGNORE INTO jobtitletbl (jdtitle, jddescription) VALUES (?, ?)");
    $stmt->execute(['Senior Developer', 'Senior Software Developer Position']);

    // Insert station if not exists
    $stmt = $con->prepare("INSERT IGNORE INTO stationtbl (stationcode, stationname) VALUES (?, ?)");
    $stmt->execute(['LOS', 'Lagos']);

    // Insert staff type if not exists
    $stmt = $con->prepare("INSERT IGNORE INTO stafftype (stafftype, stprefix) VALUES (?, ?)");
    $stmt->execute(['Permanent', 'PER']);

    return true;
});

// Test available positions
runTest("Get Available Positions", function () use ($revenue, $deptunitcode) {
    $positions = $revenue->getAvailablePositions($deptunitcode);
    return $positions > 0;
});

// Test staff request creation
$requestId = 'REQ' . date('Ymd') . sprintf('%03d', rand(1, 999));
runTest("Create Staff Request", function () use ($revenue, $requestId, $deptunitcode, $createdby) {
    return $revenue->createStaffRequest(
        $requestId,
        'Senior Developer', // This must match a jdtitle in jobtitletbl
        2,
        $deptunitcode,
        'draft',
        $createdby
    );
});

// Test station request creation
runTest("Create Station Request", function () use ($revenue, $requestId, $createdby) {
    return $revenue->createStaffRequestPerStation(
        $requestId,
        'LOS',          // This must match a stationcode in stationtbl
        'Permanent',    // This must match a stafftype in stafftype table
        1,
        'draft',
        $createdby
    );
});

// Test staff count validation
runTest("Validate Staff Count", function () use ($revenue) {
    $stationRequests = [
        ['staffperstation' => 1],
        ['staffperstation' => 1]
    ];
    return $revenue->validateStaffCount(2, $stationRequests);
});
